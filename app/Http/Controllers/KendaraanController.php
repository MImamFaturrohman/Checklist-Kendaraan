<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan',
            'jenis_kendaraan' => 'required|string|max:100',
            'bidang' => 'nullable|string|max:100',
            'set_km' => 'nullable|integer|min:0',
            'tanggal_stnk' => 'nullable|date',
            'tanggal_pajak_stnk' => 'nullable|date',
            'tanggal_kir' => 'nullable|date',
            'status_kendaraan' => 'required|string|in:Aktif,Maintenance,Non Aktif',
        ], [
            'nomor_kendaraan.required'  => 'Nomor kendaraan wajib diisi.',
            'nomor_kendaraan.max'       => 'Nomor kendaraan maksimal 20 karakter.',
            'nomor_kendaraan.unique'    => 'Nomor kendaraan ini sudah terdaftar dalam sistem.',
            'jenis_kendaraan.required'  => 'Jenis kendaraan wajib diisi.',
            'status_kendaraan.required' => 'Status kendaraan wajib dipilih.',
            'status_kendaraan.in'       => 'Status kendaraan tidak valid.',
            'set_km.integer'            => 'KM harus berupa angka.',
            'set_km.min'                => 'KM tidak boleh negatif.',
            'tanggal_stnk.date'         => 'Format tanggal STNK tidak valid.',
            'tanggal_pajak_stnk.date'   => 'Format tanggal pajak STNK tidak valid.',
            'tanggal_kir.date'          => 'Format tanggal KIR tidak valid.',
        ]);

        $kendaraan = Kendaraan::create($request->only(
            'nomor_kendaraan',
            'jenis_kendaraan',
            'bidang',
            'set_km',
            'tanggal_stnk',
            'tanggal_pajak_stnk',
            'tanggal_kir',
            'status_kendaraan',
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil ditambahkan.',
                'data' => $kendaraan,
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan,'.$kendaraan->id,
            'jenis_kendaraan' => 'required|string|max:100',
            'bidang' => 'nullable|string|max:100',
            'km_current' => 'nullable|integer|min:0',
            'tanggal_stnk' => 'nullable|date',
            'tanggal_pajak_stnk' => 'nullable|date',
            'tanggal_kir' => 'nullable|date',
            'status_kendaraan' => 'required|string|in:Aktif,Maintenance,Non Aktif',
        ], [
            'nomor_kendaraan.required'  => 'Nomor kendaraan wajib diisi.',
            'nomor_kendaraan.max'       => 'Nomor kendaraan maksimal 20 karakter.',
            'nomor_kendaraan.unique'    => 'Nomor kendaraan ini sudah terdaftar dalam sistem.',
            'jenis_kendaraan.required'  => 'Jenis kendaraan wajib diisi.',
            'status_kendaraan.required' => 'Status kendaraan wajib dipilih.',
            'status_kendaraan.in'       => 'Status kendaraan tidak valid.',
            'km_current.integer'        => 'KM harus berupa angka.',
            'km_current.min'            => 'KM tidak boleh negatif.',
            'tanggal_stnk.date'         => 'Format tanggal STNK tidak valid.',
            'tanggal_pajak_stnk.date'   => 'Format tanggal pajak STNK tidak valid.',
            'tanggal_kir.date'          => 'Format tanggal KIR tidak valid.',
        ]);

        $kendaraan->update($request->only(
            'nomor_kendaraan',
            'jenis_kendaraan',
            'bidang',
            'km_current',
            'tanggal_stnk',
            'tanggal_pajak_stnk',
            'tanggal_kir',
            'status_kendaraan',
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan diperbarui.',
                'data' => $kendaraan,
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'Data kendaraan diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan, Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $kendaraan->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'Kendaraan berhasil dihapus.');
    }

    /**
     * API: return all kendaraan for dropdown
     */
    public function apiList()
    {
        return response()->json(
            Kendaraan::orderBy('nomor_kendaraan')->get([
                'id',
                'nomor_kendaraan',
                'jenis_kendaraan',
                'bidang',
                'set_km',
                'km_current',
                'tanggal_stnk',
                'tanggal_pajak_stnk',
                'tanggal_kir',
                'status_kendaraan',
            ])
        );
    }
}
