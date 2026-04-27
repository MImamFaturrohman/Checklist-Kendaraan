<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BbmReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BbmOperationalPortalController extends Controller
{
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

    public function index(): View
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

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
        $yearFrom = max($minYear, $maxYear - 4);
        $yearsRange = range($yearFrom, $maxYear);

        $monthlyRupiahByYear = [];
        foreach ($yearsRange as $year) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[] = (float) BbmReport::query()
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $m)
                    ->sum('total_harga');
            }
            $monthlyRupiahByYear[$year] = $months;
        }

        $bulanSingkat = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $literPerVehicleLabels = [];
        $from12 = now()->subMonths(11)->startOfMonth();
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->copy()->subMonths($i)->startOfMonth();
            $literPerVehicleLabels[] = $bulanSingkat[(int) $d->format('n')].' '.$d->format('Y');
        }

        $top5Nopol = BbmReport::query()
            ->where('tanggal', '>=', $from12->toDateString())
            ->selectRaw('nomor_kendaraan, COALESCE(SUM(liter), 0) as liters')
            ->groupBy('nomor_kendaraan')
            ->orderByDesc('liters')
            ->limit(5)
            ->pluck('nomor_kendaraan')
            ->all();

        $literPerVehicleSeries = [];
        foreach ($top5Nopol as $nopol) {
            $series = [];
            for ($i = 11; $i >= 0; $i--) {
                $d = now()->copy()->subMonths($i);
                $series[] = (float) BbmReport::query()
                    ->where('nomor_kendaraan', $nopol)
                    ->whereYear('tanggal', $d->year)
                    ->whereMonth('tanggal', $d->month)
                    ->sum('liter');
            }
            $literPerVehicleSeries[$nopol] = $series;
        }

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

        $reports = BbmReport::query()
            ->with(['user:id,name,username'])
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.bbm-operational-portal', [
            'stats' => [
                'total_reports_all' => $totalReportsAll,
                'month_reports' => $monthReports,
                'month_liter' => $monthLiter,
                'month_rupiah' => $monthRupiah,
                'boros' => $boros,
                'efisien' => $efisien,
                'month_label' => now()->translatedFormat('F Y'),
            ],
            'monthlyRupiahByYear' => $monthlyRupiahByYear,
            'yearsAvailable' => $yearsRange,
            'literPerVehicleLabels' => $literPerVehicleLabels,
            'literPerVehicleSeries' => $literPerVehicleSeries,
            'topDriversMonth' => $topDriversMonth,
            'reports' => $reports,
        ]);
    }

    public function showJson(BbmReport $bbmReport): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $bbmReport->load('user:id,name,username');

        $waktu = $bbmReport->getRawOriginal('waktu') ?? $bbmReport->waktu;
        $waktuStr = is_string($waktu) ? substr($waktu, 0, 5) : Carbon::parse($waktu)->format('H:i');

        return response()->json([
            'report' => [
                'id' => $bbmReport->id,
                'driver_name' => $bbmReport->user?->name,
                'driver_username' => $bbmReport->user?->username,
                'nomor_kendaraan' => $bbmReport->nomor_kendaraan,
                'jenis_kendaraan' => $bbmReport->jenis_kendaraan,
                'tanggal' => $bbmReport->tanggal->format('d/m/Y'),
                'waktu' => $waktuStr,
                'odometer_sebelum' => (string) $bbmReport->odometer_sebelum,
                'odometer_sesudah' => (string) $bbmReport->odometer_sesudah,
                'liter' => (float) $bbmReport->liter,
                'harga_per_liter' => (float) $bbmReport->harga_per_liter,
                'total_harga' => (float) $bbmReport->total_harga,
                'odometer_photo_url' => $this->bbmPublicFileUrl($bbmReport->odometer_photo_path),
                'struk_photo_url' => $this->bbmPublicFileUrl($bbmReport->struk_photo_path),
            ],
        ]);
    }
}
