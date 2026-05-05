<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleUsageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VehicleUsageLogArchiveController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    public function index(Request $request): View|Response
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $perPage = (int) $request->query('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

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

        $logs = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $totalAll = VehicleUsageLog::query()->count();

        $view = view('admin.vehicle-usage-logs.index', [
            'logs' => $logs,
            'filters' => [
                'q' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'totalAll' => $totalAll,
        ]);

        if ($request->header('X-VMS-VUL-Logs-Fragment') === '1') {
            return response($view->fragment('vul-logs-live-fragment'));
        }

        return $view;
    }
}
