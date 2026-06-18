<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BbmReport;
use App\Models\Kendaraan;
use App\Support\DriverShift;
use App\Support\TableSort;
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

    private const SORT_ALLOWED = [
        'tanggal'         => 'tanggal',
        'waktu'           => 'waktu',
        'shift'           => 'shift',
        'nomor_kendaraan' => 'nomor_kendaraan',
        'liter'           => 'liter',
        'total_harga'     => 'total_harga',
        'odometer_sebelum'=> 'odometer_sebelum',
        'odometer_sesudah'=> 'odometer_sesudah',
    ];

    private function authorizePortalAccess(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'manager', 'admin'], true), 403);
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

    /**
     * @return list<array{name: string|null, username: string|null, cnt: int}>
     */
    private function topDriversForYear(int $year, ?string $nomorKendaraan): array
    {
        $query = BbmReport::query()
            ->join('users', 'users.id', '=', 'bbm_reports.user_id')
            ->whereYear('bbm_reports.tanggal', $year);

        if ($nomorKendaraan !== null && $nomorKendaraan !== '') {
            $query->where('bbm_reports.nomor_kendaraan', $nomorKendaraan);
        }

        return $query
            ->select([
                'users.name',
                'users.username',
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('cnt')
            ->limit(12)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'username' => $row->username,
                'cnt' => (int) $row->cnt,
            ])
            ->values()
            ->all();
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

        $vehicleFilter = $nopol === '' ? null : $nopol;
        $prevYear = $year - 1;
        $current = $this->monthlyTotalsForYear($year, $vehicleFilter);
        $previous = $this->monthlyTotalsForYear($prevYear, $vehicleFilter);

        return response()->json([
            'year' => $year,
            'year_previous' => $prevYear,
            'nomor_kendaraan' => $vehicleFilter,
            'month_labels' => self::MONTH_SHORT_ID,
            'rupiah_current' => $current['rupiah'],
            'rupiah_previous' => $previous['rupiah'],
            'liter_current' => $current['liter'],
            'liter_previous' => $previous['liter'],
            'top_drivers' => $this->topDriversForYear($year, $vehicleFilter),
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
     * @return array{show: bool, direction: 'up'|'down'|'flat', pct_display: string}
     */
    private function portalCompareMeta(float $current, float $previous): array
    {
        $epsilon = 0.0001;
        if ($previous <= $epsilon && $current <= $epsilon) {
            return ['show' => false, 'direction' => 'flat', 'pct_display' => ''];
        }
        if ($previous <= $epsilon) {
            return [
                'show' => true,
                'direction' => 'up',
                'pct_display' => '—',
            ];
        }

        $pct = (($current - $previous) / $previous) * 100;
        $absFmt = number_format(abs($pct), 1, ',', '.');

        if ($pct > 0.049) {
            return ['show' => true, 'direction' => 'up', 'pct_display' => $absFmt.'%'];
        }
        if ($pct < -0.049) {
            return ['show' => true, 'direction' => 'down', 'pct_display' => $absFmt.'%'];
        }

        return ['show' => true, 'direction' => 'flat', 'pct_display' => $absFmt.'%'];
    }

    /**
     * Kendaraan aktif dengan pengisian BBM paling lama (atau belum pernah isi).
     *
     * @return object{nomor_kendaraan: string, jenis_kendaraan: string, never_filled: bool, days_since: int|null, last_fill_label: string}|null
     */
    private function vehicleLongestWithoutBbm(): ?object
    {
        $activeVehicles = Kendaraan::query()
            ->where('status_kendaraan', 'Aktif')
            ->orderBy('nomor_kendaraan')
            ->get(['nomor_kendaraan', 'jenis_kendaraan']);

        if ($activeVehicles->isEmpty()) {
            $activeVehicles = BbmReport::query()
                ->selectRaw('nomor_kendaraan, MAX(jenis_kendaraan) as jenis_kendaraan')
                ->groupBy('nomor_kendaraan')
                ->orderBy('nomor_kendaraan')
                ->get();
        }

        if ($activeVehicles->isEmpty()) {
            return null;
        }

        $lastFillRows = BbmReport::query()
            ->selectRaw('nomor_kendaraan, MAX(tanggal) as last_date')
            ->groupBy('nomor_kendaraan')
            ->pluck('last_date', 'nomor_kendaraan');

        $worst = null;
        $worstScore = -1;

        foreach ($activeVehicles as $vehicle) {
            $nopol = (string) $vehicle->nomor_kendaraan;
            $lastRaw = $lastFillRows[$nopol] ?? null;

            if ($lastRaw === null) {
                $score = PHP_INT_MAX;
                $meta = $this->lastFillMeta(null);
            } else {
                $lastAt = Carbon::parse($lastRaw)->startOfDay();
                $days = (int) $lastAt->diffInDays(now()->startOfDay());
                $score = $days;
                $meta = $this->lastFillMeta($lastAt);
            }

            if ($score > $worstScore) {
                $worstScore = $score;
                $worst = (object) [
                    'nomor_kendaraan' => $nopol,
                    'jenis_kendaraan' => (string) ($vehicle->jenis_kendaraan ?? ''),
                    'never_filled' => $meta['never_filled'],
                    'days_since' => $meta['days_since'],
                    'last_fill_label' => $meta['label'],
                ];
            }
        }

        return $worst;
    }

    /**
     * @return array{never_filled: bool, days_since: int|null, label: string}
     */
    private function lastFillMeta(?Carbon $lastFillAt): array
    {
        if ($lastFillAt === null) {
            return [
                'never_filled' => true,
                'days_since' => null,
                'label' => 'Belum pernah isi BBM',
            ];
        }

        $days = (int) $lastFillAt->diffInDays(now()->startOfDay());
        $dateLabel = $lastFillAt->translatedFormat('j F Y');
        $ago = match (true) {
            $days === 0 => 'hari ini',
            $days === 1 => '1 hari lalu',
            default => $days.' hari lalu',
        };

        return [
            'never_filled' => false,
            'days_since' => $days,
            'label' => 'Terakhir isi pada '.$dateLabel.' ('.$ago.')',
        ];
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
        abort_unless(in_array($role, ['superadmin', 'manager', 'admin'], true), 403);

        $chartsOnly = in_array($role, ['manager', 'admin'], true);
        $perPage = $this->resolvePerPage($request);
        $search = $request->input('q');
        $shiftFilter = $request->input('shift');
        $jenisPengisianFilter = $request->input('jenis_pengisian');
        if (is_string($jenisPengisianFilter) && $jenisPengisianFilter !== '') {
            if (! in_array($jenisPengisianFilter, BbmReport::JENIS_PENGISIAN_VALUES, true)) {
                $jenisPengisianFilter = '';
            }
        } else {
            $jenisPengisianFilter = '';
        }
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $year = (int) now()->year;
        $prevYear = $year - 1;

        $totalReportsAll = BbmReport::query()->count();
        $yearReports = BbmReport::query()->whereYear('tanggal', $year)->count();
        $prevYearReports = BbmReport::query()->whereYear('tanggal', $prevYear)->count();
        $yearLiter = (float) BbmReport::query()->whereYear('tanggal', $year)->sum('liter');
        $yearRupiah = (float) BbmReport::query()->whereYear('tanggal', $year)->sum('total_harga');
        $prevYearLiter = (float) BbmReport::query()->whereYear('tanggal', $prevYear)->sum('liter');
        $prevYearRupiah = (float) BbmReport::query()->whereYear('tanggal', $prevYear)->sum('total_harga');

        $vehYearAgg = BbmReport::query()
            ->whereYear('tanggal', $year)
            ->selectRaw('nomor_kendaraan, jenis_kendaraan, COALESCE(SUM(liter), 0) as liters, COALESCE(SUM(total_harga), 0) as rupiah')
            ->groupBy('nomor_kendaraan', 'jenis_kendaraan')
            ->get();

        $boros = $vehYearAgg->sortByDesc('liters')->first();
        $overdueVehicle = $this->vehicleLongestWithoutBbm();

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

        $reportsQuery = BbmReport::query()->with(['user:id,name,username']);

        if (! $chartsOnly) {
            if ($search !== null && $search !== '') {
                $term = '%'.$search.'%';
                $reportsQuery->where(function ($q) use ($term) {
                    $q->where('nomor_kendaraan', 'like', $term)
                        ->orWhere('jenis_kendaraan', 'like', $term)
                        ->orWhere('jenis_pengisian', 'like', $term)
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('name', 'like', $term)
                                ->orWhere('username', 'like', $term);
                        });
                });
            }

            if ($jenisPengisianFilter !== '') {
                $reportsQuery->where('jenis_pengisian', $jenisPengisianFilter);
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

        TableSort::apply($reportsQuery, $request, self::SORT_ALLOWED, function ($q) {
            $q->orderByDesc('tanggal')->orderByDesc('waktu')->orderByDesc('id');
        });

        $reports = $reportsQuery->paginate($perPage)->onEachSide(0)->withQueryString();

        $sortState = TableSort::current($request, self::SORT_ALLOWED);

        $payload = [
            'stats' => [
                'total_reports_all' => $totalReportsAll,
                'year_reports' => $yearReports,
                'year_liter' => $yearLiter,
                'year_rupiah' => $yearRupiah,
                'boros' => $boros,
                'overdue_vehicle' => $overdueVehicle,
                'year_label' => (string) $year,
                'yoy_year_reports' => $this->portalCompareMeta((float) $yearReports, (float) $prevYearReports),
                'yoy_year_liter' => $this->portalCompareMeta($yearLiter, $prevYearLiter),
                'yoy_year_rupiah' => $this->portalCompareMeta($yearRupiah, $prevYearRupiah),
            ],
            'yearsAvailable' => $yearsRange,
            'bbmVehicleNopolList' => $bbmVehicleNopolList,
            'bbmDefaultChartYear' => $bbmDefaultChartYear,
            'reports' => $reports,
            'bbmPortalChartsOnly' => $chartsOnly,
            'bbmPortalSearch' => $search,
            'bbmPortalShift' => $shiftFilter,
            'bbmPortalJenisPengisian' => $jenisPengisianFilter,
            'bbmPortalDateFrom' => $dateFrom,
            'bbmPortalDateTo' => $dateTo,
            'activeSort' => $sortState['sort'] ?? null,
            'activeDir'  => $sortState['dir']  ?? null,
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
                'jenis_pengisian' => $bbmReport->jenis_pengisian ?: BbmReport::JENIS_PENGISIAN_OPERASIONAL,
                'tanggal' => $bbmReport->tanggal->format('d/m/Y'),
                'waktu' => $waktuStr,
                'shift_code' => $shiftCode,
                'shift_label' => DriverShift::tableLabelFromCode($shiftCode),
                'shift_badge_class' => DriverShift::badgeClassFromCode($shiftCode),
                'shift_icon_class' => DriverShift::iconClassFromCode($shiftCode),
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
