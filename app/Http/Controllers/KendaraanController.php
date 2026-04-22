<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kendaraan::orderBy('nomor_kendaraan');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kendaraan', 'like', "%{$search}%")
                    ->orWhere('jenis_kendaraan', 'like', "%{$search}%");
            });
        }

        $kendaraans = $query->paginate(10)->withQueryString();
        return view('admin.master-armada', compact('kendaraans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan',
            'jenis_kendaraan' => 'required|string|max:100',
            'bidang'          => 'nullable|string|max:100',
            'set_km'          => 'nullable|integer|min:0',
        ]);

        $kendaraan = Kendaraan::create($request->only('nomor_kendaraan', 'jenis_kendaraan', 'bidang', 'set_km'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil ditambahkan.',
                'data' => $kendaraan
            ]);
        }

        return redirect()->route('admin.master-armada')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $request->validate([
            'nomor_kendaraan' => 'required|string|max:20|unique:kendaraans,nomor_kendaraan,' . $kendaraan->id,
            'jenis_kendaraan' => 'required|string|max:100',
            'bidang'          => 'nullable|string|max:100',
            'set_km'          => 'nullable|integer|min:0',
        ]);

        $kendaraan->update($request->only('nomor_kendaraan', 'jenis_kendaraan', 'bidang', 'set_km'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan diperbarui.',
                'data' => $kendaraan
            ]);
        }

        return redirect()->route('admin.master-armada')->with('success', 'Data kendaraan diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan, Request $request)
    {
        $kendaraan->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.master-armada')->with('success', 'Kendaraan berhasil dihapus.');
    }

    /**
     * API: return all kendaraan for dropdown
     */
    public function apiList()
    {
        return response()->json(
            Kendaraan::orderBy('nomor_kendaraan')->get(['id', 'nomor_kendaraan', 'jenis_kendaraan', 'bidang', 'set_km'])
        );
    }
}
