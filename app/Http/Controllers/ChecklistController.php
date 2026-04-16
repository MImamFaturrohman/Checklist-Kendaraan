<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistExterior;
use App\Models\ChecklistInterior;
use App\Models\ChecklistMesin;
use App\Models\ChecklistPerlengkapan;
use App\Models\Kendaraan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    /**
     * Store checklist and generate PDF.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'jam_serah_terima' => 'required',
            'nomor_kendaraan' => 'required|string',
            'jenis_kendaraan' => 'required|string',
            'driver_serah' => 'required|string',
            'driver_terima' => 'required|string',
            'level_bbm' => 'required|numeric|min:0|max:100',
            'km_awal' => 'required|numeric|min:0',
            'km_akhir' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            // Save signature images from base64
            $ttdSerahPath = null;
            $ttdTerimaPath = null;

            if ($request->filled('tanda_tangan_serah') && str_starts_with($request->input('tanda_tangan_serah'), 'data:image')) {
                $ttdSerahPath = $this->saveBase64Image($request->input('tanda_tangan_serah'), 'ttd_serah');
            }
            if ($request->filled('tanda_tangan_terima') && str_starts_with($request->input('tanda_tangan_terima'), 'data:image')) {
                $ttdTerimaPath = $this->saveBase64Image($request->input('tanda_tangan_terima'), 'ttd_terima');
            }

            // Save BBM dashboard photo
            $fotoBbmPath = null;
            if ($request->hasFile('foto_bbm_dashboard')) {
                $fotoBbmPath = $request->file('foto_bbm_dashboard')->store('checklists/bbm', 'public');
            }

            // Create main checklist
            $checklist = Checklist::create([
                'tanggal' => $request->tanggal,
                'shift' => $request->shift,
                'driver_serah' => $request->driver_serah,
                'driver_terima' => $request->driver_terima,
                'nomor_kendaraan' => $request->nomor_kendaraan,
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'jam_serah_terima' => $request->jam_serah_terima,
                'level_bbm' => $request->level_bbm,
                'bbm_terakhir' => $request->bbm_terakhir,
                'km_awal' => $request->km_awal,
                'km_akhir' => $request->km_akhir,
                'foto_bbm_dashboard' => $fotoBbmPath,
                'catatan_khusus' => $request->catatan_khusus,
                'tanda_tangan_serah' => $ttdSerahPath,
                'tanda_tangan_terima' => $ttdTerimaPath,
                'user_id' => auth()->id(),
            ]);

            // Save Exterior
            $exteriorItems = ['body_kendaraan', 'kaca', 'spion', 'lampu_utama', 'lampu_sein', 'ban', 'velg', 'wiper'];
            $exteriorData = ['checklist_id' => $checklist->id];
            foreach ($exteriorItems as $item) {
                $exteriorData[$item] = $request->input("exterior_{$item}");
                $exteriorData["{$item}_keterangan"] = $request->input("exterior_{$item}_catatan");
            }
            $exteriorData['catatan'] = $request->input('exterior_catatan');

            // Save exterior photos
            foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side) {
                if ($request->hasFile("exterior_foto_{$side}")) {
                    $exteriorData["foto_{$side}"] = $request->file("exterior_foto_{$side}")->store('checklists/exterior', 'public');
                }
            }
            ChecklistExterior::create($exteriorData);

            // Save Interior
            $interiorItems = ['jok', 'dashboard', 'ac', 'sabuk_pengaman', 'audio', 'kebersihan'];
            $interiorData = ['checklist_id' => $checklist->id];
            foreach ($interiorItems as $item) {
                $interiorData[$item] = $request->input("interior_{$item}");
                $interiorData["{$item}_keterangan"] = $request->input("interior_{$item}_catatan");
            }
            $interiorData['catatan'] = $request->input('interior_catatan');

            for ($i = 1; $i <= 3; $i++) {
                if ($request->hasFile("interior_foto_{$i}")) {
                    $interiorData["foto_{$i}"] = $request->file("interior_foto_{$i}")->store('checklists/interior', 'public');
                }
            }
            ChecklistInterior::create($interiorData);

            // Save Mesin
            $mesinItems = ['mesin', 'oli', 'radiator', 'rem', 'kopling', 'transmisi', 'indikator'];
            $mesinData = ['checklist_id' => $checklist->id];
            foreach ($mesinItems as $item) {
                $mesinData[$item] = $request->input("mesin_{$item}");
                $mesinData["{$item}_keterangan"] = $request->input("mesin_{$item}_catatan");
            }
            $mesinData['catatan'] = $request->input('mesin_catatan');

            for ($i = 1; $i <= 3; $i++) {
                if ($request->hasFile("mesin_foto_{$i}")) {
                    $mesinData["foto_{$i}"] = $request->file("mesin_foto_{$i}")->store('checklists/mesin', 'public');
                }
            }
            ChecklistMesin::create($mesinData);

            // Save Perlengkapan
            $perlengkapanItems = ['stnk', 'kir', 'dongkrak', 'toolkit', 'segitiga', 'apar', 'ban_cadangan'];
            $perlengkapanData = ['checklist_id' => $checklist->id];
            foreach ($perlengkapanItems as $item) {
                $val = $request->input("perlengkapan.{$item}");
                $perlengkapanData[$item] = $val ? 'ada' : 'tidak_ada';
            }
            ChecklistPerlengkapan::create($perlengkapanData);

            // Auto-save kendaraan to master if not exists
            Kendaraan::firstOrCreate(
                ['nomor_kendaraan' => $request->nomor_kendaraan],
                ['jenis_kendaraan' => $request->jenis_kendaraan]
            );

            // Generate PDF
            $checklist->load(['exterior', 'interior', 'mesin', 'perlengkapan']);
            $pdf = Pdf::loadView('checklists.pdf', ['checklist' => $checklist]);
            $pdfFileName = 'checklist_' . $checklist->id . '_' . now()->format('Ymd_His') . '.pdf';
            $pdfPath = 'checklists/pdf/' . $pdfFileName;
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $checklist->update(['pdf_path' => $pdfPath]);

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil disimpan!',
                'pdf_url' => Storage::disk('public')->url($pdfPath),
                'checklist_id' => $checklist->id,
            ]);
        });
    }

    /**
     * Lookup kendaraan by nomor.
     */
    public function lookupKendaraan(Request $request): JsonResponse
    {
        $nomor = $request->query('nomor');
        $kendaraan = Kendaraan::where('nomor_kendaraan', $nomor)->first();

        if ($kendaraan) {
            return response()->json([
                'found' => true,
                'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Get last KM for a vehicle.
     */
    public function lastKm(Request $request): JsonResponse
    {
        $nomor = $request->query('nomor');
        $lastChecklist = Checklist::where('nomor_kendaraan', $nomor)
            ->whereNotNull('km_akhir')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'km' => $lastChecklist?->km_akhir ?? 0,
        ]);
    }

    /**
     * Save base64 image to storage.
     */
    private function saveBase64Image(string $base64, string $prefix): string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);
        $fileName = $prefix . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.png';
        $path = 'checklists/signatures/' . $fileName;
        Storage::disk('public')->put($path, $image);
        return $path;
    }
}
