<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BbmReport;
use App\Support\DriverShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BbmOperationalPortalController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private const MONTH_SHORT_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private function authorizePortalAccess(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'manager'], true), 403);
    }

    /**
     * @return array{liter: list<float>, rupiah: list<float>}
     */
    private function monthlyTotalsForYear(int $year, ?string $nomorKendaraan): array
    {
        $liter = [];
        $rupiah = [];
        $hasVehicle = $nomorKendaraan !== null && $nomorKendaraan !== '';

        for ($m = 1; $m <= 12; $m++) {
            $base = BbmReport::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $m);
            if ($hasVehicle) {
                $base->where('nomor_kendaraan', $nomorKendaraan);
            }
            $liter[] = (float) (clone $base)->sum('liter');
            $rupiah[] = (float) (clone $base)->sum('total_harga');
        }

        return ['liter' => $liter, 'rupiah' => $rupiah];
    }

    public function chartSeries(Request $request): JsonResponse
    {
        $this->authorizePortalAccess();

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'nomor_kendaraan' => ['nullable', 'string', 'max:32'],
        ]);

        $year = (int) $validated['year'];
        $nopol = isset($validated['nomor_kendaraan']) ? trim((string) $validated['nomor_kendaraan']) : '';

        if ($nopol !== '' && ! BbmReport::query()->where('nomor_kendaraan', $nopol)->exists()) {
            abort(422, 'Nomor kendaraan tidak ditemukan pada data BBM.');
        }

        $prevYear = $year - 1;
        $current = $this->monthlyTotalsForYear($year, $nopol === '' ? null : $nopol);
        $previous = $this->monthlyTotalsForYear($prevYear, $nopol === '' ? null : $nopol);

        return response()->json([
            'year' => $year,
            'year_previous' => $prevYear,
            'nomor_kendaraan' => $nopol === '' ? null : $nopol,
            'month_labels' => self::MONTH_SHORT_ID,
            'rupiah_current' => $current['rupiah'],
            'rupiah_previous' => $previous['rupiah'],
            'liter_current' => $current['liter'],
            'liter_previous' => $previous['liter'],
        ]);
    }

    public function activityLog(Request $request): JsonResponse
    {
        $this->authorizePortalAccess();

        $limit = min(50, max(5, (int) $request->query('limit', 20)));

        $rows = BbmReport::query()
            ->with(['user:id,name,username'])
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $isSuper = auth()->user()?->role === 'superadmin';

        $items = $rows->map(function (BbmReport $r) use ($isSuper) {
            $waktu = $r->getRawOriginal('waktu') ?? $r->waktu;
            $waktuStr = is_string($waktu) ? substr($waktu, 0, 5) : Carbon::parse($waktu)->format('H:i');
            $compact = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $r->nomor_kendaraan));
            $badge = $compact === '' ? '—' : substr($compact, 0, 3);

            return [
                'id' => $r->id,
                'badge' => $badge,
                'nomor_kendaraan' => $r->nomor_kendaraan,
                'driver_name' => $r->user?->name ?? '—',
                'tanggal_label' => $r->tanggal->translatedFormat('j F Y'),
                'waktu_label' => $waktuStr,
                'liter' => (float) $r->liter,
                'total_harga' => (float) $r->total_harga,
                'detail_json_url' => $isSuper ? route('admin.portal-bbm-operasional.json', $r) : null,
            ];
        })->values()->all();

        return response()->json(['items' => $items]);
    }

    private function resolvePerPage(Request $request): int
    {
        $n = (int) $request->query('per_page', 25);

        return in_array($n, self::PER_PAGE_OPTIONS, true) ? $n : 25;
    }

    /**
     * Public URL for files on the public disk. Uses a root-relative path so the
     * browser resolves it against the current host (avoids broken images when
     * APP_URL does not match how the user opens the site).
     */
    private function bbmPublicFileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return '/storage/'.$path;
    }

    public function index(Request $request): View|Response
    {
        $role = auth()->user()?->role;
        abort_unless(in_array($role, ['superadmin', 'manager'], true), 403);

        $chartsOnly = $role === 'manager';
        $perPage = $this->resolvePerPage($request);
        $search = $request->input('q');
        $shiftFilter = $request->input('shift');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $totalReportsAll = BbmReport::query()->count();
        $monthReports = BbmReport::query()->whereBetween('tanggal', [$monthStart, $monthEnd])->count();
        $monthLiter = (float) BbmReport::query()->whereBetween('tanggal', [$monthStart, $monthEnd])->sum('liter');
        $monthRupiah = (float) BbmReport::query()->whereBetween('tanggal', [$monthStart, $monthEnd])->sum('total_harga');

        $vehMonthAgg = BbmReport::query()
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->selectRaw('nomor_kendaraan, jenis_kendaraan, COALESCE(SUM(liter), 0) as liters, COALESCE(SUM(total_harga), 0) as rupiah')
            ->groupBy('nomor_kendaraan', 'jenis_kendaraan')
            ->get();

        $boros = $vehMonthAgg->sortByDesc('liters')->first();
        $efisien = $vehMonthAgg->filter(fn ($v) => (float) $v->liters > 0)->sortBy('liters')->first();

        $maxYear = (int) (BbmReport::query()->max(DB::raw('YEAR(tanggal)')) ?? now()->year);
        $minYear = (int) (BbmReport::query()->min(DB::raw('YEAR(tanggal)')) ?? $maxYear);
        $yearsRange = $minYear <= $maxYear ? range($minYear, $maxYear) : [now()->year];
        $bbmDefaultChartYear = min(max(now()->year, $minYear), $maxYear);

        $bbmVehicleNopolList = BbmReport::query()
            ->distinct()
            ->orderBy('nomor_kendaraan')
            ->pluck('nomor_kendaraan')
            ->filter()
            ->values()
            ->all();

        $topDriversMonth = BbmReport::query()
            ->join('users', 'users.id', '=', 'bbm_reports.user_id')
            ->whereBetween('bbm_reports.tanggal', [$monthStart, $monthEnd])
            ->select([
                'users.name',
                'users.username',
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('cnt')
            ->limit(12)
            ->get();

        $reportsQuery = BbmReport::query()->with(['user:id,name,username']);

        if (! $chartsOnly) {
            if ($search !== null && $search !== '') {
                $term = '%'.$search.'%';
                $reportsQuery->where(function ($q) use ($term) {
                    $q->where('nomor_kendaraan', 'like', $term)
                        ->orWhere('jenis_kendaraan', 'like', $term)
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('name', 'like', $term)
                                ->orWhere('username', 'like', $term);
                        });
                });
            }

            if ($shiftFilter === 'luar') {
                $reportsQuery->where(function ($q) {
                    $q->where('shift', 'luar')->orWhereNull('shift');
                });
            } elseif (in_array($shiftFilter, ['pagi', 'siang'], true)) {
                $reportsQuery->where('shift', $shiftFilter);
            }

            if ($dateFrom) {
                try {
                    $reportsQuery->whereDate('tanggal', '>=', Carbon::parse($dateFrom)->toDateString());
                } catch (\Throwable) {
                    // Abaikan tanggal tidak valid
                }
            }
            if ($dateTo) {
                try {
                    $reportsQuery->whereDate('tanggal', '<=', Carbon::parse($dateTo)->toDateString());
                } catch (\Throwable) {
                }
            }
        } else {
            $reportsQuery->whereRaw('0 = 1');
        }

        $reports = $reportsQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->onEachSide(0)
            ->withQueryString();

        $payload = [
            'stats' => [
                'total_reports_all' => $totalReportsAll,
                'month_reports' => $monthReports,
                'month_liter' => $monthLiter,
                'month_rupiah' => $monthRupiah,
                'boros' => $boros,
                'efisien' => $efisien,
                'month_label' => now()->translatedFormat('F Y'),
            ],
            'yearsAvailable' => $yearsRange,
            'bbmVehicleNopolList' => $bbmVehicleNopolList,
            'bbmDefaultChartYear' => $bbmDefaultChartYear,
            'topDriversMonth' => $topDriversMonth,
            'reports' => $reports,
            'bbmPortalChartsOnly' => $chartsOnly,
            'bbmPortalSearch' => $search,
            'bbmPortalShift' => $shiftFilter,
            'bbmPortalDateFrom' => $dateFrom,
            'bbmPortalDateTo' => $dateTo,
        ];

        $view = view('admin.bbm-operational-portal', $payload);

        if ($request->header('X-VMS-BBM-Portal-Fragment') === '1') {
            return response($view->fragment('bbm-portal-table-body'));
        }

        return $view;
    }

    public function showJson(BbmReport $bbmReport): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $bbmReport->load('user:id,name,username');

        $waktu = $bbmReport->getRawOriginal('waktu') ?? $bbmReport->waktu;
        $waktuStr = is_string($waktu) ? substr($waktu, 0, 5) : Carbon::parse($waktu)->format('H:i');

        $totalKm = max(0, (int) $bbmReport->odometer_sesudah - (int) $bbmReport->odometer_sebelum);
        $shiftCode = $bbmReport->shift ?: 'luar';

        return response()->json([
            'report' => [
                'id' => $bbmReport->id,
                'driver_name' => $bbmReport->user?->name,
                'driver_username' => $bbmReport->user?->username,
                'nomor_kendaraan' => $bbmReport->nomor_kendaraan,
                'jenis_kendaraan' => $bbmReport->jenis_kendaraan,
                'tanggal' => $bbmReport->tanggal->format('d/m/Y'),
                'waktu' => $waktuStr,
                'shift_code' => $shiftCode,
                'shift_label' => DriverShift::labelFromCode($shiftCode),
                'shift_badge_class' => DriverShift::badgeClassFromCode($shiftCode),
                'odometer_sebelum' => (string) (int) $bbmReport->odometer_sebelum,
                'odometer_sesudah' => (string) (int) $bbmReport->odometer_sesudah,
                'total_km' => (string) $totalKm,
                'liter' => (float) $bbmReport->liter,
                'harga_per_liter' => (float) $bbmReport->harga_per_liter,
                'total_harga' => (float) $bbmReport->total_harga,
                'odometer_photo_url' => $this->bbmPublicFileUrl($bbmReport->odometer_photo_path),
                'struk_photo_url' => $this->bbmPublicFileUrl($bbmReport->struk_photo_path),
            ],
        ]);
    }
}
