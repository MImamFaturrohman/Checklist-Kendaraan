<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index()
    {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();
        return view('admin.master-armada', compact('kendaraans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan',
            'jenis_kendaraan' => 'required|string|max:100',
        ]);

        Kendaraan::create($request->only('nomor_kendaraan', 'jenis_kendaraan'));

        return redirect()->route('admin.master-armada')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan,' . $kendaraan->id,
            'jenis_kendaraan' => 'required|string|max:100',
        ]);

        $kendaraan->update($request->only('nomor_kendaraan', 'jenis_kendaraan'));

        return redirect()->route('admin.master-armada')->with('success', 'Data kendaraan diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan)
    {
        $kendaraan->delete();
        return redirect()->route('admin.master-armada')->with('success', 'Kendaraan berhasil dihapus.');
    }

    /**
     * API: return all kendaraan for dropdown
     */
    public function apiList()
    {
        return response()->json(
            Kendaraan::orderBy('nomor_kendaraan')->get(['id', 'nomor_kendaraan', 'jenis_kendaraan'])
        );
    }
}
