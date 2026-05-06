<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Kendaraan;
use App\Models\LaporanKejadian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'penjelasan_gambar' => 'required|string|max:10000',
            'foto' => 'required|image|max:5120',
            'ttd_manager' => 'required|string',
            'ttd_pelapor' => 'required|string',
        ], [
            'foto.required' => 'Foto kejadian wajib diunggah.',
            'foto.image' => 'File harus berupa gambar (JPEG, PNG, atau WebP).',
            'foto.max' => 'Ukuran gambar maksimal 5 MB.',
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

        foreach (['ttd_manager', 'ttd_pelapor'] as $field) {
            $v = $request->input($field);
            if (! is_string($v) || ! str_starts_with($v, 'data:image/png;base64,')) {
                throw ValidationException::withMessages([
                    $field => 'Tanda tangan tidak valid.',
                ]);
            }
        }

        $fotoPath = $request->file('foto')->store('laporan-kejadian/foto', 'public');

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
            'penjelasan_gambar' => $request->penjelasan_gambar,
            'foto_path' => $fotoPath,
            'ttd_manager' => $request->ttd_manager,
            'ttd_pelapor' => $request->ttd_pelapor,
        ]);

        try {
            $pdfPath = $this->buildAndStorePdf($laporan);
            if ($pdfPath) {
                $laporan->update(['pdf_path' => $pdfPath]);
            }
        } catch (\Throwable) {
            // Tetap anggap sukses menyimpan laporan; PDF bisa diunduh ulang dari admin
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan kejadian berhasil dikirim.',
        ]);
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $laporans = LaporanKejadian::query()
            ->with(['bidang.parent'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => LaporanKejadian::count(),
            'incident' => LaporanKejadian::where('kategori', 'Incident')->count(),
            'nearmiss' => LaporanKejadian::where('kategori', 'Nearmiss')->count(),
        ];

        return view('admin.laporan-kejadian.index', compact('laporans', 'stats'));
    }

    public function downloadPdf(LaporanKejadian $laporanKejadian)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $laporanKejadian->load(['bidang.parent']);

        if ($laporanKejadian->pdf_path && Storage::disk('public')->exists($laporanKejadian->pdf_path)) {
            return response()->download(
                Storage::disk('public')->path($laporanKejadian->pdf_path),
                'Laporan_Kejadian_'.$laporanKejadian->id.'.pdf'
            );
        }

        $path = $this->buildAndStorePdf($laporanKejadian);
        if ($path) {
            $laporanKejadian->update(['pdf_path' => $path]);
        }

        $laporanKejadian->refresh();

        if (! $laporanKejadian->pdf_path || ! Storage::disk('public')->exists($laporanKejadian->pdf_path)) {
            abort(503, 'PDF tidak dapat dibuat.');
        }

        return response()->download(
            Storage::disk('public')->path($laporanKejadian->pdf_path),
            'Laporan_Kejadian_'.$laporanKejadian->id.'.pdf'
        );
    }

    private function buildAndStorePdf(LaporanKejadian $laporan): ?string
    {
        $laporan->loadMissing(['bidang.parent']);

        $fotoDataUrl = null;
        if ($laporan->foto_path && Storage::disk('public')->exists($laporan->foto_path)) {
            $binary = Storage::disk('public')->get($laporan->foto_path);
            $ext = strtolower(pathinfo($laporan->foto_path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $fotoDataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        $pdf = Pdf::loadView('laporan-kejadian.pdf', [
            'laporan' => $laporan,
            'fotoDataUrl' => $fotoDataUrl,
        ])->setPaper('a4', 'portrait');

        $fileName = 'laporan_kejadian_'.$laporan->id.'_'.now()->format('Ymd_His').'.pdf';
        $path = 'laporan-kejadian/pdf/'.$fileName;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
