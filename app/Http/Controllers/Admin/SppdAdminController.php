<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sppd;
use App\Support\SppdStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SppdAdminController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin'], true), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $status = $request->input('status');
        $search = $request->input('q');

        $query = Sppd::query()
            ->with(['user:id,name,username', 'tolls', 'fuels'])
            ->orderByDesc('created_at');

        if ($status && in_array($status, SppdStatus::adminFilterOptions(), true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_driver', 'like', '%'.$search.'%')
                    ->orWhere('keperluan_dinas', 'like', '%'.$search.'%')
                    ->orWhere('no_kendaraan', 'like', '%'.$search.'%');
            });
        }

        $sppds = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Sppd::count(),
            'pending' => Sppd::where('status', Sppd::STATUS_PENDING)->count(),
            'revision' => Sppd::where('status', Sppd::STATUS_REVISION)->count(),
            'pending_manager' => Sppd::where('status', Sppd::STATUS_PENDING_MANAGER)->count(),
            'approved' => Sppd::where('status', Sppd::STATUS_APPROVED)->count(),
            'rejected' => Sppd::where('status', Sppd::STATUS_REJECTED)->count(),
            'completed' => Sppd::where('status', Sppd::STATUS_COMPLETED)->count(),
        ];

        return view('admin.sppd.index', [
            'sppds' => $sppds,
            'counts' => $counts,
            'currentStatus' => $status,
            'search' => $search,
            'statusMeta' => fn (?string $s) => SppdStatus::meta($s),
        ]);
    }

    public function show(Sppd $sppd): JsonResponse
    {
        $this->authorizeAdmin();

        $sppd->load(['user:id,name,username', 'tolls', 'fuels', 'approver:id,name', 'adminVerifier:id,name']);

        return response()->json([
            'sppd' => $sppd->toDetailArray(),
        ]);
    }

    public function verifyApprove(Sppd $sppd): JsonResponse
    {
        $this->authorizeAdmin();
        abort_unless($sppd->status === Sppd::STATUS_PENDING, 422);

        $sppd->update([
            'status' => Sppd::STATUS_PENDING_MANAGER,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan diverifikasi dan diteruskan ke Manager.',
        ]);
    }

    public function verifyReject(Request $request, Sppd $sppd): JsonResponse
    {
        $this->authorizeAdmin();
        abort_unless($sppd->status === Sppd::STATUS_PENDING, 422);

        $data = $request->validate([
            'revision_note' => 'required|string|max:5000',
        ]);

        $sppd->update([
            'status' => Sppd::STATUS_REVISION,
            'revision_note' => $data['revision_note'],
            'revision_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan dikembalikan untuk revisi.',
        ]);
    }

}
