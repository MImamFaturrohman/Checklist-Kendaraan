<?php

namespace App\Http\Controllers;

use App\Mail\LaporanKejadianApprovalMail;
use App\Models\Bidang;
use App\Models\Kendaraan;
use App\Support\AdminTablePagination;
use App\Models\LaporanKejadian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaporanKejadianController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50',
            'jabatan' => 'required|string|max:150',
            'bidang_id' => 'required|exists:bidangs,id',
            'waktu_kejadian' => 'required|date',
            'kategori' => 'required|string|in:Incident,Nearmiss',
            'lokasi_kejadian' => 'required|string|max:500',
            'nomor_kendaraan' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|string|max:100',
            'peristiwa' => 'required|string|max:10000',
            'sebelum_kejadian' => 'required|string|max:10000',
            'uraian_kejadian' => 'required|string|max:10000',
            'foto' => 'required|array|min:1|max:3',
            'foto.*' => 'required|image|max:5120',
            'penjelasan_gambar' => 'required|array|min:1|max:3',
            'penjelasan_gambar.*' => 'required|string|max:10000',
            'ttd_pelapor' => 'required|string',
        ], [
            'foto.required' => 'Minimal satu foto kejadian wajib diunggah.',
            'foto.min' => 'Minimal satu foto kejadian wajib diunggah.',
            'foto.max' => 'Maksimal 3 foto lampiran.',
            'foto.*.image' => 'Setiap lampiran harus berupa gambar (JPEG, PNG, atau WebP).',
            'foto.*.max' => 'Ukuran tiap gambar maksimal 5 MB.',
            'penjelasan_gambar.*.required' => 'Penjelasan untuk tiap gambar wajib diisi.',
        ]);

        $bidang = Bidang::query()->find($request->bidang_id);
        if (! $bidang || ! $bidang->isLeaf()) {
            throw ValidationException::withMessages([
                'bidang_id' => 'Pilih sub bidang / bagian (bukan induk).',
            ]);
        }

        $k = Kendaraan::query()->where('nomor_kendaraan', $request->nomor_kendaraan)->first();
        if (! $k || $k->jenis_kendaraan !== $request->jenis_kendaraan) {
            throw ValidationException::withMessages([
                'nomor_kendaraan' => 'Nomor kendaraan tidak valid atau tidak sesuai jenis kendaraan.',
            ]);
        }

        $fotoFiles = $request->file('foto', []);
        $penjelasanList = $request->input('penjelasan_gambar', []);
        if (! is_array($penjelasanList)) {
            $penjelasanList = [];
        }
        if (count($fotoFiles) !== count($penjelasanList)) {
            throw ValidationException::withMessages([
                'foto' => 'Jumlah foto dan penjelasan harus sama.',
            ]);
        }

        $ttdPelapor = $request->input('ttd_pelapor');
        if (! is_string($ttdPelapor) || ! str_starts_with($ttdPelapor, 'data:image/png;base64,')) {
            throw ValidationException::withMessages([
                'ttd_pelapor' => 'Tanda tangan tidak valid.',
            ]);
        }

        $lampiran = [];
        foreach ($fotoFiles as $i => $upload) {
            $path = $upload->store('laporan-kejadian/foto', 'public');
            $lampiran[] = [
                'path' => $path,
                'penjelasan' => (string) ($penjelasanList[$i] ?? ''),
            ];
        }

        $firstPenjelasan = $lampiran[0]['penjelasan'] ?? '';
        $fotoPath = $lampiran[0]['path'] ?? null;

        $needsManagerApproval = $bidang->hasManagerContact();
        $token = $needsManagerApproval ? Str::random(64) : null;

        $laporan = LaporanKejadian::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'bidang_id' => $request->bidang_id,
            'waktu_kejadian' => $request->waktu_kejadian,
            'kategori' => $request->kategori,
            'lokasi_kejadian' => $request->lokasi_kejadian,
            'nomor_kendaraan' => $request->nomor_kendaraan,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'peristiwa' => $request->peristiwa,
            'sebelum_kejadian' => $request->sebelum_kejadian,
            'uraian_kejadian' => $request->uraian_kejadian,
            'penjelasan_gambar' => $firstPenjelasan,
            'lampiran_gambar' => $lampiran,
            'foto_path' => $fotoPath,
            'ttd_pelapor' => $ttdPelapor,
            'ttd_manager' => null,
            'manager_approval_token' => $token,
        ]);

        if ($needsManagerApproval) {
            $approvalUrl = route('laporan-kejadian.approval.show', ['token' => $token]);
            try {
                Mail::to($bidang->manager_email)
                    ->send(new LaporanKejadianApprovalMail($laporan->load('bidang.parent'), $approvalUrl, $bidang->manager_nama));
            } catch (\Throwable $e) {
                Log::error('LaporanKejadian approval email gagal: '.$e->getMessage(), ['laporan_id' => $laporan->id]);
            }

            return response()->json([
                'success' => true,
                'pending_manager_approval' => true,
                'message' => 'Laporan kejadian berhasil dikirim. Tautan persetujuan telah dikirimkan ke email manager bidang Anda.',
            ]);
        }

        try {
            $pdfPath = $this->buildAndStorePdf($laporan);
            if ($pdfPath) {
                $laporan->update([
                    'pdf_path'    => $pdfPath,
                    'ttd_pelapor' => null,
                    'ttd_manager' => null,
                ]);
            }
        } catch (\Throwable) {
            // Tetap anggap sukses; PDF bisa diunduh ulang dari admin
        }

        return response()->json([
            'success' => true,
            'pending_manager_approval' => false,
            'message' => 'Laporan kejadian berhasil dikirim.',
        ]);
    }

    public function showApproval(string $token)
    {
        $laporan = LaporanKejadian::query()
            ->where('manager_approval_token', $token)
            ->with(['bidang.parent'])
            ->firstOrFail();

        if ($laporan->ttd_manager) {
            return view('laporan-kejadian.manager-approval', [
                'laporan' => $laporan,
                'alreadySigned' => true,
            ]);
        }

        $fotoSlides = [];
        foreach ($laporan->lampiranItems() as $item) {
            $p = $item['path'];
            $url = ($p !== '' && Storage::disk('public')->exists($p))
                ? Storage::disk('public')->url($p)
                : null;
            $fotoSlides[] = ['url' => $url, 'penjelasan' => $item['penjelasan']];
        }

        return view('laporan-kejadian.manager-approval', [
            'laporan' => $laporan,
            'alreadySigned' => false,
            'fotoSlides' => $fotoSlides,
        ]);
    }

    public function submitApproval(Request $request, string $token): JsonResponse
    {
        $laporan = LaporanKejadian::query()
            ->where('manager_approval_token', $token)
            ->firstOrFail();

        if ($laporan->ttd_manager) {
            return response()->json(['success' => false, 'message' => 'Laporan ini sudah disetujui sebelumnya.'], 422);
        }

        $request->validate([
            'ttd_manager' => 'required|string',
        ]);

        $ttd = $request->input('ttd_manager');
        if (! is_string($ttd) || ! str_starts_with($ttd, 'data:image/png;base64,')) {
            return response()->json(['success' => false, 'message' => 'Tanda tangan tidak valid.'], 422);
        }

        $laporan->update([
            'ttd_manager' => $ttd,
            'manager_approval_token' => null,
        ]);

        try {
            $laporan->load('bidang.parent');
            $pdfPath = $this->buildAndStorePdf($laporan);
            if ($pdfPath) {
                $laporan->update([
                    'pdf_path'    => $pdfPath,
                    'ttd_pelapor' => null,
                    'ttd_manager' => null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('LaporanKejadian PDF build gagal setelah approval: '.$e->getMessage(), ['laporan_id' => $laporan->id]);
        }

        return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan. Laporan telah disetujui.']);
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $perPage = AdminTablePagination::resolvePerPage($request->input('per_page'));
        $laporans = $this->adminLaporanQuery($request)
            ->paginate($perPage)
            ->onEachSide(0)
            ->withQueryString();

        $stats = [
            'total' => LaporanKejadian::count(),
            'incident' => LaporanKejadian::where('kategori', 'Incident')->count(),
            'nearmiss' => LaporanKejadian::where('kategori', 'Nearmiss')->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'tbody' => view('admin.partials.laporan-kejadian-rows', compact('laporans'))->render(),
                'pagination_html' => AdminTablePagination::linksHtml($laporans, route('admin.laporan-kejadian.index')),
                'per_page' => $laporans->perPage(),
            ]);
        }

        return view('admin.laporan-kejadian.index', compact('laporans', 'stats'));
    }

    /**
     * @return Builder<LaporanKejadian>
     */
    private function adminLaporanQuery(Request $request): Builder
    {
        $query = LaporanKejadian::query()
            ->with(['bidang.parent'])
            ->orderByDesc('created_at');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('lokasi_kejadian', 'like', "%{$search}%")
                    ->orWhere('nomor_kendaraan', 'like', "%{$search}%")
                    ->orWhere('jenis_kendaraan', 'like', "%{$search}%")
                    ->orWhereHas('bidang', function ($bq) use ($search) {
                        $bq->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('parent', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
                    });
            });
        }

        $kategori = $request->input('kategori');
        if (in_array($kategori, ['Incident', 'Nearmiss'], true)) {
            $query->where('kategori', $kategori);
        }

        return $query;
    }

    public function downloadPdf(LaporanKejadian $laporanKejadian)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        if ($laporanKejadian->manager_approval_token) {
            abort(403, 'Laporan masih menunggu persetujuan manager.');
        }

        $laporanKejadian->load(['bidang.parent']);

        $filename = 'Laporan_Kejadian_'.$laporanKejadian->id.'.pdf';

        if ($laporanKejadian->pdf_path && Storage::disk('public')->exists($laporanKejadian->pdf_path)) {
            return $this->inlinePdfResponse(
                Storage::disk('public')->path($laporanKejadian->pdf_path),
                $filename
            );
        }

        $path = $this->buildAndStorePdf($laporanKejadian);
        if ($path) {
            $laporanKejadian->update([
                'pdf_path'    => $path,
                'ttd_pelapor' => null,
                'ttd_manager' => null,
            ]);
        }

        $laporanKejadian->refresh();

        if (! $laporanKejadian->pdf_path || ! Storage::disk('public')->exists($laporanKejadian->pdf_path)) {
            abort(503, 'PDF tidak dapat dibuat.');
        }

        return $this->inlinePdfResponse(
            Storage::disk('public')->path($laporanKejadian->pdf_path),
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

    private function buildAndStorePdf(LaporanKejadian $laporan): ?string
    {
        $laporan->loadMissing(['bidang.parent']);

        $fotoSlides = [];
        foreach ($laporan->lampiranItems() as $item) {
            $p = $item['path'];
            $penjelasan = $item['penjelasan'];
            $dataUrl = null;
            if ($p !== '' && Storage::disk('public')->exists($p)) {
                $binary = Storage::disk('public')->get($p);
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };
                $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);
            }
            $fotoSlides[] = ['data_url' => $dataUrl, 'penjelasan' => $penjelasan];
        }

        $pdf = Pdf::loadView('laporan-kejadian.pdf', [
            'laporan' => $laporan,
            'fotoSlides' => $fotoSlides,
        ])->setPaper('a4', 'portrait');

        $fileName = 'laporan_kejadian_'.$laporan->id.'_'.now()->format('Ymd_His').'.pdf';
        $path = 'laporan-kejadian/pdf/'.$fileName;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
