<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminTablePagination;
use App\Support\TableSort;
use App\Models\VehicleUsageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VehicleUsageLogArchiveController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private const SORT_ALLOWED = [
        'created_at'      => 'created_at',
        'nomor_kendaraan' => 'nomor_kendaraan',
        'km_awal'         => 'km_awal',
        'km_akhir'        => 'km_akhir',
        'keperluan'       => 'keperluan',
    ];

    public function index(Request $request): View|Response
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $perPage = AdminTablePagination::resolvePerPage($request->query('per_page'), 25);

        $search = $request->input('q');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = VehicleUsageLog::query()->with(['user:id,name,username']);

        if (is_string($search) && $search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('nomor_kendaraan', 'like', $term)
                    ->orWhere('jenis_kendaraan', 'like', $term)
                    ->orWhere('keperluan', 'like', $term)
                    ->orWhere('level_bbm_awal', 'like', $term)
                    ->orWhere('level_bbm_akhir', 'like', $term)
                    ->orWhere('kondisi_sebelum_penggunaan', 'like', $term)
                    ->orWhere('kondisi_setelah_penggunaan', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', $term)
                            ->orWhere('username', 'like', $term);
                    });
            });
        }

        if ($dateFrom) {
            try {
                $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->toDateString());
            } catch (\Throwable) {
            }
        }
        if ($dateTo) {
            try {
                $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->toDateString());
            } catch (\Throwable) {
            }
        }

        TableSort::apply($query, $request, self::SORT_ALLOWED, function ($q) {
            $q->orderByDesc('created_at')->orderByDesc('id');
        });

        $logs = $query->paginate($perPage)->onEachSide(0)->withQueryString();

        $totalAll = VehicleUsageLog::query()->count();
        $sortState = TableSort::current($request, self::SORT_ALLOWED);

        $view = view('admin.vehicle-usage-logs.index', [
            'logs' => $logs,
            'filters' => [
                'q' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'totalAll' => $totalAll,
            'activeSort' => $sortState['sort'] ?? null,
            'activeDir'  => $sortState['dir']  ?? null,
        ]);

        if ($request->header('X-VMS-VUL-Logs-Fragment') === '1') {
            return response($view->fragment('vul-logs-live-fragment'));
        }

        return $view;
    }
}
