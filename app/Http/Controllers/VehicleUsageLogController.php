<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\VehicleUsageLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleUsageLogController extends Controller
{
    private function authorizeDriver(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['driver', 'pic_kendaraan'], true), 403);
    }

    public function create(): View
    {
        $this->authorizeDriver();

        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();

        return view('vehicle-usage-logs.create', [
            'kendaraans' => $kendaraans,
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeDriver();

        $wantsJson = $request->expectsJson();

        $validated = $request->validate([
            'nomor_kendaraan' => ['required', 'string', 'exists:kendaraans,nomor_kendaraan'],
            'jam_awal' => ['required', 'date_format:H:i'],
            'jam_akhir' => ['required', 'date_format:H:i', 'after:jam_awal'],
            'keperluan' => ['required', 'string', 'max:10000'],
            'level_bbm_awal' => ['required', 'integer', 'min:0', 'max:100'],
            'level_bbm_akhir' => ['required', 'integer', 'min:0', 'max:100'],
            'km_awal' => ['required', 'integer', 'min:0'],
            'km_akhir' => ['required', 'integer', 'min:0', 'gte:km_awal'],
            'kondisi_sebelum_penggunaan' => ['required', 'string', 'max:10000'],
            'kondisi_setelah_penggunaan' => ['required', 'string', 'max:10000'],
        ], [
            'nomor_kendaraan.required' => 'Pilih nomor kendaraan.',
            'nomor_kendaraan.exists' => 'Nomor kendaraan tidak terdaftar.',
            'jam_awal.required' => 'Jam awal wajib diisi.',
            'jam_awal.date_format' => 'Format jam awal tidak valid.',
            'jam_akhir.required' => 'Jam akhir wajib diisi.',
            'jam_akhir.date_format' => 'Format jam akhir tidak valid.',
            'jam_akhir.after' => 'Jam akhir harus setelah jam awal.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'level_bbm_awal.required' => 'Level BBM awal wajib diisi.',
            'level_bbm_akhir.required' => 'Level BBM akhir wajib diisi.',
            'km_awal.required' => 'KM awal wajib diisi.',
            'km_akhir.required' => 'KM akhir wajib diisi.',
            'km_akhir.gte' => 'KM akhir harus lebih besar atau sama dengan KM awal.',
            'kondisi_sebelum_penggunaan.required' => 'Kondisi sebelum penggunaan wajib diisi.',
            'kondisi_setelah_penggunaan.required' => 'Kondisi setelah penggunaan wajib diisi.',
        ]);

        $kendaraan = Kendaraan::query()->where('nomor_kendaraan', $validated['nomor_kendaraan'])->firstOrFail();

        VehicleUsageLog::create([
            'user_id' => auth()->id(),
            'kendaraan_id' => $kendaraan->id,
            'nomor_kendaraan' => $kendaraan->nomor_kendaraan,
            'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
            'jam_awal' => $validated['jam_awal'],
            'jam_akhir' => $validated['jam_akhir'],
            'keperluan' => $validated['keperluan'],
            'level_bbm_awal' => (string) $validated['level_bbm_awal'],
            'level_bbm_akhir' => (string) $validated['level_bbm_akhir'],
            'km_awal' => (int) $validated['km_awal'],
            'km_akhir' => (int) $validated['km_akhir'],
            'kondisi_sebelum_penggunaan' => $validated['kondisi_sebelum_penggunaan'],
            'kondisi_setelah_penggunaan' => $validated['kondisi_setelah_penggunaan'],
        ]);

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Log penggunaan kendaraan berhasil dikirim.',
            ]);
        }

        return redirect()->route('vehicle-usage-logs.create')->with('vul_ok', 'Log penggunaan kendaraan berhasil dikirim.');
    }
}
