<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sppd;
use App\Support\SppdStatus;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SppdAdminController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private const SORT_ALLOWED = [
        'nama_driver'     => 'nama_driver',
        'keperluan_dinas' => 'keperluan_dinas',
        'no_kendaraan'    => 'no_kendaraan',
        'status'          => 'status',
        'tanggal_dinas'   => 'tanggal_dinas',
        'created_at'      => 'created_at',
    ];

    private function authorizeAdmin(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin'], true), 403);
    }

    private function authorizeVerifier(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    private function resolveAdminSppdPerPage(Request $request): int
    {
        $n = (int) $request->query('per_page', 15);

        return in_array($n, self::PER_PAGE_OPTIONS, true) ? $n : 15;
    }

    public function index(Request $request): View|Response
    {
        $this->authorizeAdmin();

        $perPage = $this->resolveAdminSppdPerPage($request);

        $status = $request->input('status');
        $search = $request->input('q');

        $query = Sppd::query()->with(['user:id,name,username', 'tolls', 'fuels']);

        TableSort::apply($query, $request, self::SORT_ALLOWED, function ($q) {
            $q->orderByDesc('created_at');
        });

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

        $sppds = $query->paginate($perPage)->onEachSide(0)->withQueryString();

        $counts = [
            'all' => Sppd::count(),
            'pending' => Sppd::where('status', Sppd::STATUS_PENDING)->count(),
            'revision' => Sppd::where('status', Sppd::STATUS_REVISION)->count(),
            'pending_manager' => Sppd::where('status', Sppd::STATUS_PENDING_MANAGER)->count(),
            'approved' => Sppd::where('status', Sppd::STATUS_APPROVED)->count(),
            'rejected' => Sppd::where('status', Sppd::STATUS_REJECTED)->count(),
            'completed' => Sppd::where('status', Sppd::STATUS_COMPLETED)->count(),
        ];

        $sortState = TableSort::current($request, self::SORT_ALLOWED);

        $payload = [
            'sppds' => $sppds,
            'counts' => $counts,
            'currentStatus' => $status,
            'search' => $search,
            'statusMeta' => fn (?string $s) => SppdStatus::meta($s),
            'canVerifySppd' => auth()->user()?->role === 'admin',
            'activeSort' => $sortState['sort'] ?? null,
            'activeDir'  => $sortState['dir']  ?? null,
        ];

        $view = view('admin.sppd.index', $payload);

        if ($request->header('X-VMS-SPPD-Fragment') === '1') {
            return response($view->fragment('sppd-admin-body'));
        }

        return $view;
    }

    public function show(Sppd $sppd): JsonResponse
    {
        $this->authorizeAdmin();

        $sppd->load(['user:id,name,username', 'tolls', 'fuels', 'approver:id,name', 'adminVerifier:id,name']);

        return response()->json([
            'sppd' => $sppd->toDetailArray(),
        ]);
    }

    public function downloadPdf(Sppd $sppd): BinaryFileResponse
    {
        $this->authorizeAdmin();
        abort_unless($sppd->pdf_path && Storage::disk('public')->exists($sppd->pdf_path), 404);

        $path = Storage::disk('public')->path($sppd->pdf_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Rekap_SPPD_'.$sppd->id.'.pdf"',
        ]);
    }

    public function verifyApprove(Sppd $sppd): JsonResponse
    {
        $this->authorizeVerifier();
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
        $this->authorizeVerifier();
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
