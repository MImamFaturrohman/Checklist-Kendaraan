<?php

namespace App\Http\Controllers;

use App\Support\AdminTablePagination;
use App\Support\SuperAdminNotifier;
use App\Support\TableSort;
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
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get(['nomor_kendaraan', 'jenis_kendaraan', 'bidang', 'status_kendaraan']);

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

    private const MGR_PEMINJAMAN_PENDING_SORT = [
        'nama_lengkap'   => 'nama_lengkap',
        'nomor_kendaraan'=> 'nomor_kendaraan',
        'created_at'     => 'created_at',
    ];

    private const MGR_PEMINJAMAN_HISTORY_SORT = [
        'nama_lengkap'   => 'nama_lengkap',
        'nomor_kendaraan'=> 'nomor_kendaraan',
        'status'         => 'status',
        'updated_at'     => 'updated_at',
    ];

    public function managerIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        PeminjamanRequest::expirePendingPastBorrowDate();

        $pendingQuery = PeminjamanRequest::where('status', 'pending')->with('bidang.parent');
        TableSort::apply($pendingQuery, $request, self::MGR_PEMINJAMAN_PENDING_SORT, function ($q) {
            $q->orderByDesc('created_at');
        }, 'pending');
        $pendingRequests = $pendingQuery->paginate(10, ['*'], 'pending_page')->onEachSide(0)->withQueryString();

        $historyQuery = PeminjamanRequest::whereIn('status', ['approved', 'rejected', 'expired'])->with(['approver', 'bidang.parent']);
        TableSort::apply($historyQuery, $request, self::MGR_PEMINJAMAN_HISTORY_SORT, function ($q) {
            $q->orderByDesc('updated_at');
        }, 'history');
        $historyRequests = $historyQuery->paginate(10, ['*'], 'history_page')->onEachSide(0)->withQueryString();

        $pendingSortState  = TableSort::current($request, self::MGR_PEMINJAMAN_PENDING_SORT, 'pending');
        $historySortState  = TableSort::current($request, self::MGR_PEMINJAMAN_HISTORY_SORT, 'history');

        $pendingActiveSort  = $pendingSortState['sort']  ?? null;
        $pendingActiveDir   = $pendingSortState['dir']   ?? null;
        $historyActiveSort  = $historySortState['sort']  ?? null;
        $historyActiveDir   = $historySortState['dir']   ?? null;

        return view('manager.peminjaman', compact(
            'pendingRequests', 'historyRequests',
            'pendingActiveSort', 'pendingActiveDir',
            'historyActiveSort', 'historyActiveDir'
        ));
    }

    public function approve(Request $request, PeminjamanRequest $peminjaman): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        $user = auth()->user();
        $ttdPath = public_path("signatures/ttd_manager_{$user->username}_{$user->id}.png");
        if (!file_exists($ttdPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus mengunggah Tanda Tangan (TTD) di menu Profil terlebih dahulu sebelum menyetujui peminjaman kendaraan.',
            ], 400);
        }

        PeminjamanRequest::expirePendingPastBorrowDate();
        $peminjaman->refresh();
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

        // Notify all superadmins that this peminjaman has been approved
        try {
            SuperAdminNotifier::peminjamanApproved($peminjaman);
        } catch (\Throwable) {
            // Notification failure must not block the approval response
        }

        return response()->json([
            'success' => true,
            'message' => "Request peminjaman {$peminjaman->nomor_kendaraan} atas nama {$peminjaman->nama_lengkap} telah disetujui.",
        ]);
    }

    public function reject(Request $request, PeminjamanRequest $peminjaman): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        PeminjamanRequest::expirePendingPastBorrowDate();
        $peminjaman->refresh();
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

        $filename = 'Berita_Acara_Peminjaman_'.$peminjaman->id.'.pdf';

        // If PDF already exists, stream inline for browser preview
        if ($peminjaman->pdf_path && Storage::disk('public')->exists($peminjaman->pdf_path)) {
            return $this->inlinePdfResponse(
                Storage::disk('public')->path($peminjaman->pdf_path),
                $filename
            );
        }

        // Regenerate PDF (signature may already be cleared, that is fine)
        $path = $this->buildAndStorePdf($peminjaman);
        $peminjaman->update(['pdf_path' => $path, 'tanda_tangan' => null]);

        return $this->inlinePdfResponse(
            Storage::disk('public')->path($path),
            $filename
        );
    }

    private function inlinePdfResponse(string $absolutePath, string $filename)
    {
        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        PeminjamanRequest::expirePendingPastBorrowDate();

        $query = $this->adminPeminjamanRequestsQuery($request);
        $perPage = AdminTablePagination::resolvePerPage($request->input('per_page'));
        $requests = $query->paginate($perPage)->onEachSide(0)->withQueryString();

        $stats = [
            'total'   => PeminjamanRequest::count(),
            'pending' => PeminjamanRequest::where('status', 'pending')->count(),
            'approved' => PeminjamanRequest::where('status', 'approved')->count(),
            'rejected' => PeminjamanRequest::where('status', 'rejected')->count(),
            'expired'  => PeminjamanRequest::where('status', 'expired')->count(),
        ];

        $tabCounts = [
            'bidangs' => Bidang::query()->count(),
            'pernyataans' => Pernyataan::query()->count(),
            'permohonan' => $stats['total'],
        ];

        $sortState = TableSort::current($request, self::PPM_SORT_ALLOWED);

        if ($request->expectsJson()) {
            return response()->json([
                'tbody' => view('admin.partials.peminjaman-request-rows', compact('requests'))->render(),
                'pagination_html' => AdminTablePagination::linksHtml($requests, route('admin.peminjaman')),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'sort' => $sortState['sort'] ?? null,
                'dir'  => $sortState['dir']  ?? null,
            ]);
        }

        $activeSort = $sortState['sort'] ?? null;
        $activeDir  = $sortState['dir']  ?? null;

        return view('admin.peminjaman', compact('requests', 'stats', 'tabCounts', 'activeSort', 'activeDir'));
    }

    /**
     * @return Builder<PeminjamanRequest>
     */
    private const PPM_SORT_ALLOWED = [
        'nama_lengkap'   => 'nama_lengkap',
        'nomor_kendaraan'=> 'nomor_kendaraan',
        'status'         => 'status',
        'created_at'     => 'created_at',
        'updated_at'     => 'updated_at',
    ];

    private function adminPeminjamanRequestsQuery(Request $request)
    {
        $query = PeminjamanRequest::with(['approver', 'bidang.parent']);

        TableSort::apply($query, $request, self::PPM_SORT_ALLOWED, function ($q) {
            $q->orderByDesc('created_at');
        });

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

    public function destroyBulk(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'nullable|exists:peminjaman_requests,id',
            'all' => 'nullable|boolean',
            'search' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($request->boolean('all')) {
            $query = PeminjamanRequest::query();

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

            $requestsToDelete = $query->get();
        } else {
            $ids = $request->input('ids', []);
            $requestsToDelete = PeminjamanRequest::whereIn('id', $ids)->get();
        }

        $count = $requestsToDelete->count();

        foreach ($requestsToDelete as $req) {
            if ($req->pdf_path) {
                Storage::disk('public')->delete($req->pdf_path);
            }
            $req->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' data peminjaman berhasil dihapus.',
        ]);
    }
}
