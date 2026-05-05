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
        abort_unless(auth()->user()?->role === 'driver', 403);
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
        ], [
            'nomor_kendaraan.required' => 'Pilih nomor kendaraan.',
            'nomor_kendaraan.exists' => 'Nomor kendaraan tidak terdaftar.',
            'jam_awal.required' => 'Jam awal wajib diisi.',
            'jam_awal.date_format' => 'Format jam awal tidak valid.',
            'jam_akhir.required' => 'Jam akhir wajib diisi.',
            'jam_akhir.date_format' => 'Format jam akhir tidak valid.',
            'jam_akhir.after' => 'Jam akhir harus setelah jam awal.',
            'keperluan.required' => 'Keperluan wajib diisi.',
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
