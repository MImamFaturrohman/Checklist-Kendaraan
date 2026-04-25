<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kendaraan;
use App\Models\PeminjamanRequest;
use App\Models\Pernyataan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function landingPage()
    {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get(['nomor_kendaraan', 'jenis_kendaraan', 'bidang']);

        $bidangRoots = Bidang::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->get();

        $pernyataanPengantar = config('peminjaman.pernyataan_pengantar');
        $pernyataans = Pernyataan::query()->orderBy('urutan')->orderBy('id')->get();

        return view('landing', compact('kendaraans', 'bidangRoots', 'pernyataanPengantar', 'pernyataans'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|max:50',
            'jabatan' => 'required|string|max:150',
            'bidang_id' => 'required|exists:bidangs,id',
            'nomor_kendaraan' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|string|max:100',
            'tanggal_peminjaman' => 'required|date',
            'alasan' => 'required|string|max:2000',
            'tanda_tangan' => 'required|string',
        ]);

        $bidang = Bidang::query()->find($request->bidang_id);
        if (! $bidang || ! $bidang->isLeaf()) {
            throw ValidationException::withMessages([
                'bidang_id' => 'Pilih sub bidang / bagian (bukan induk).',
            ]);
        }

        PeminjamanRequest::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'bidang_id' => $request->bidang_id,
            'nomor_kendaraan' => $request->nomor_kendaraan,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'tanggal_peminjaman' => $request->tanggal_peminjaman,
            'alasan' => $request->alasan,
            'tanda_tangan' => $request->tanda_tangan,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function managerIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        $pendingRequests = PeminjamanRequest::where('status', 'pending')
            ->with('bidang.parent')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        $historyRequests = PeminjamanRequest::whereIn('status', ['approved', 'rejected'])
            ->with(['approver', 'bidang.parent'])
            ->orderByDesc('approved_at')
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        return view('manager.peminjaman', compact('pendingRequests', 'historyRequests'));
    }

    public function approve(Request $request, PeminjamanRequest $peminjaman): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        abort_unless($peminjaman->isPending(), 422);

        $peminjaman->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Generate PDF immediately and clear the signature from DB
        $peminjaman->refresh()->load(['approver', 'bidang.parent']);
        try {
            $pdfPath = $this->buildAndStorePdf($peminjaman);
            if ($pdfPath) {
                $peminjaman->update(['pdf_path' => $pdfPath, 'tanda_tangan' => null]);
            }
        } catch (\Throwable) {
            // PDF generation failure should not block approval response
        }

        return response()->json([
            'success' => true,
            'message' => "Request peminjaman {$peminjaman->nomor_kendaraan} atas nama {$peminjaman->nama_lengkap} telah disetujui.",
        ]);
    }

    public function reject(Request $request, PeminjamanRequest $peminjaman): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        abort_unless($peminjaman->isPending(), 422);

        $request->validate([
            'catatan_manager' => 'nullable|string|max:500',
        ]);

        $peminjaman->update([
            'status' => 'rejected',
            'catatan_manager' => $request->catatan_manager,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'tanda_tangan' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Request peminjaman {$peminjaman->nomor_kendaraan} atas nama {$peminjaman->nama_lengkap} telah ditolak.",
        ]);
    }

    /**
     * Build a PDF for the given peminjaman and store it. Returns the storage path.
     */
    private function buildAndStorePdf(PeminjamanRequest $peminjaman): ?string
    {
        $signatureDataUrl = $peminjaman->tanda_tangan ?: null;

        $peminjaman->loadMissing(['approver', 'bidang.parent']);

        $pernyataanPengantar = config('peminjaman.pernyataan_pengantar');
        $pernyataans = Pernyataan::query()->orderBy('urutan')->orderBy('id')->get();

        $pdf = Pdf::loadView('peminjaman.pdf', [
            'peminjaman' => $peminjaman,
            'signatureDataUrl' => $signatureDataUrl,
            'pernyataanPengantar' => $pernyataanPengantar,
            'pernyataans' => $pernyataans,
        ])->setPaper('a4', 'portrait');

        $fileName = 'peminjaman_'.$peminjaman->id.'_'.now()->format('Ymd_His').'.pdf';
        $path = 'peminjaman/pdf/'.$fileName;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Admin: download / generate PDF for an approved peminjaman.
     */
    public function downloadPdf(PeminjamanRequest $peminjaman)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        abort_unless($peminjaman->isApproved(), 422);

        $peminjaman->load(['approver', 'bidang.parent']);

        // If PDF already exists, stream it
        if ($peminjaman->pdf_path && Storage::disk('public')->exists($peminjaman->pdf_path)) {
            return response()->download(
                Storage::disk('public')->path($peminjaman->pdf_path),
                'Berita_Acara_Peminjaman_'.$peminjaman->id.'.pdf'
            );
        }

        // Regenerate PDF (signature may already be cleared, that is fine)
        $path = $this->buildAndStorePdf($peminjaman);
        $peminjaman->update(['pdf_path' => $path, 'tanda_tangan' => null]);

        return response()->download(
            Storage::disk('public')->path($path),
            'Berita_Acara_Peminjaman_'.$peminjaman->id.'.pdf'
        );
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $query = $this->adminPeminjamanRequestsQuery($request);
        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => PeminjamanRequest::count(),
            'pending' => PeminjamanRequest::where('status', 'pending')->count(),
            'approved' => PeminjamanRequest::where('status', 'approved')->count(),
            'rejected' => PeminjamanRequest::where('status', 'rejected')->count(),
        ];

        $tabCounts = [
            'bidangs' => Bidang::query()->count(),
            'pernyataans' => Pernyataan::query()->count(),
            'permohonan' => $stats['total'],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'tbody' => view('admin.partials.peminjaman-request-rows', compact('requests'))->render(),
                'pagination' => $requests->hasPages()
                    ? (string) $requests->withQueryString()->links()
                    : '',
            ]);
        }

        return view('admin.peminjaman', compact('requests', 'stats', 'tabCounts'));
    }

    /**
     * @return Builder<PeminjamanRequest>
     */
    private function adminPeminjamanRequestsQuery(Request $request)
    {
        $query = PeminjamanRequest::with(['approver', 'bidang.parent'])->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('nomor_kendaraan', 'like', "%{$search}%")
                    ->orWhere('jenis_kendaraan', 'like', "%{$search}%")
                    ->orWhereHas('bidang', function ($bq) use ($search) {
                        $bq->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('parent', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query;
    }
}
