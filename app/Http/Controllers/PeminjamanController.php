<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\PeminjamanRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function landingPage()
    {
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get(['nomor_kendaraan', 'jenis_kendaraan']);

        return view('landing', compact('kendaraans'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_lengkap'    => 'required|string|max:255',
            'nip'             => 'required|string|max:50',
            'divisi'          => 'required|string|max:100',
            'nomor_kendaraan' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|string|max:100',
            'alasan'          => 'required|string|max:1000',
        ]);

        PeminjamanRequest::create([
            'nama_lengkap'    => $request->nama_lengkap,
            'nip'             => $request->nip,
            'divisi'          => $request->divisi,
            'nomor_kendaraan' => $request->nomor_kendaraan,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'alasan'          => $request->alasan,
            'status'          => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request peminjaman berhasil dikirim! Silakan tunggu persetujuan dari manager.',
        ]);
    }

    public function managerIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        $pendingRequests = PeminjamanRequest::where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        $historyRequests = PeminjamanRequest::whereIn('status', ['approved', 'rejected'])
            ->with('approver')
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
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

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
            'status'          => 'rejected',
            'catatan_manager' => $request->catatan_manager,
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Request peminjaman {$peminjaman->nomor_kendaraan} atas nama {$peminjaman->nama_lengkap} telah ditolak.",
        ]);
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $query = PeminjamanRequest::with('approver')->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%")
                    ->orWhere('nomor_kendaraan', 'like', "%{$search}%")
                    ->orWhere('jenis_kendaraan', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'total'    => PeminjamanRequest::count(),
            'pending'  => PeminjamanRequest::where('status', 'pending')->count(),
            'approved' => PeminjamanRequest::where('status', 'approved')->count(),
            'rejected' => PeminjamanRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.peminjaman', compact('requests', 'stats'));
    }
}
