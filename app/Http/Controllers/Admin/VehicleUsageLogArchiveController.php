<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminTablePagination;
use App\Support\TableSort;
use App\Models\VehicleUsageLog;
use App\Models\Kendaraan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $search  = $request->input('q');
        $month   = $request->input('month');
        $year    = $request->input('year');

        $query = $this->buildLogsQuery($search, $month, $year);
        TableSort::apply($query, $request, self::SORT_ALLOWED, fn ($q) => $q->orderByDesc('created_at')->orderByDesc('id'));

        $logs      = $query->paginate($perPage)->onEachSide(0)->withQueryString();
        $sortState = TableSort::current($request, self::SORT_ALLOWED);
        $years     = $this->availableYears();
        $nopolList = Kendaraan::orderBy('nomor_kendaraan')->get();
        $logsJson  = $logs->getCollection()->map(fn($row) => [
            'id' => $row->id,
            'nomor_kendaraan' => $row->nomor_kendaraan,
            'jam_awal' => $row->jam_awal ? substr(trim($row->jam_awal), 0, 5) : '',
            'jam_akhir' => $row->jam_akhir ? substr(trim($row->jam_akhir), 0, 5) : '',
            'level_bbm_awal' => $row->level_bbm_awal,
            'level_bbm_akhir' => $row->level_bbm_akhir,
            'km_awal_raw' => $row->km_awal,
            'km_akhir_raw' => $row->km_akhir,
            'keperluan_full' => $row->keperluan,
            'kondisi_sebelum_full' => $row->kondisi_sebelum_penggunaan,
            'kondisi_setelah_full' => $row->kondisi_setelah_penggunaan,
        ]);

        $view = view('admin.vehicle-usage-logs.index', [
            'logs'           => $logs,
            'years'          => $years,
            'nopolList'      => $nopolList,
            'logsJson'       => $logsJson,
            'paginationHtml' => AdminTablePagination::linksHtml($logs, route('api.admin.vehicle-usage-logs')),
            'filters'        => ['q' => $search, 'month' => $month, 'year' => $year],
            'activeSort'     => $sortState['sort'] ?? null,
            'activeDir'      => $sortState['dir']  ?? null,
        ]);

        if ($request->header('X-VMS-VUL-Logs-Fragment') === '1') {
            return response($view->fragment('vul-logs-live-fragment'));
        }

        return $view;
    }

    public function apiIndex(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $perPage = AdminTablePagination::resolvePerPage($request->input('per_page'), 25);
        $search  = $request->input('q');
        $month   = $request->input('month');
        $year    = $request->input('year');

        $query = $this->buildLogsQuery($search, $month, $year);
        TableSort::apply($query, $request, self::SORT_ALLOWED, fn ($q) => $q->orderByDesc('created_at')->orderByDesc('id'));

        $rows      = $query->paginate($perPage)->withQueryString();
        $sortState = TableSort::current($request, self::SORT_ALLOWED);

        $data = $rows->map(fn ($row) => [
            'id'                           => $row->id,
            'created_at'                   => $row->created_at?->translatedFormat('d F Y H:i'),
            'user_name'                    => $row->user?->name ?? '—',
            'user_username'                => $row->user?->username ?? '',
            'nomor_kendaraan'              => $row->nomor_kendaraan,
            'jenis_kendaraan'              => $row->jenis_kendaraan,
            'jam_awal'                     => $row->jam_awal ? substr(trim($row->jam_awal), 0, 5) : '',
            'jam_akhir'                    => $row->jam_akhir ? substr(trim($row->jam_akhir), 0, 5) : '',
            'level_bbm_awal'               => $row->level_bbm_awal,
            'level_bbm_akhir'              => $row->level_bbm_akhir,
            'km_awal'                      => $row->km_awal !== null ? number_format((int) $row->km_awal) : null,
            'km_akhir'                     => $row->km_akhir !== null ? number_format((int) $row->km_akhir) : null,
            'km_awal_raw'                  => $row->km_awal,
            'km_akhir_raw'                 => $row->km_akhir,
            'durasi'                       => $row->durasiDeskripsi(),
            'keperluan'                    => \Illuminate\Support\Str::limit(strip_tags($row->keperluan ?? ''), 80),
            'keperluan_full'               => $row->keperluan ?? '',
            'kondisi_sebelum_penggunaan'   => \Illuminate\Support\Str::limit(strip_tags($row->kondisi_sebelum_penggunaan ?? ''), 120),
            'kondisi_sebelum_full'         => $row->kondisi_sebelum_penggunaan ?? '',
            'kondisi_setelah_penggunaan'   => \Illuminate\Support\Str::limit(strip_tags($row->kondisi_setelah_penggunaan ?? ''), 120),
            'kondisi_setelah_full'         => $row->kondisi_setelah_penggunaan ?? '',
        ]);

        return response()->json(array_merge(
            [
                'data' => $data,
                'sort' => $sortState['sort'] ?? null,
                'dir'  => $sortState['dir']  ?? null,
            ],
            AdminTablePagination::jsonMeta($rows, route('api.admin.vehicle-usage-logs'))
        ));
    }

    /* ------------------------------------------------------------------ */
    /* Private helpers                                                       */
    /* ------------------------------------------------------------------ */

    /** @return list<int> */
    private function availableYears(): array
    {
        $maxYear = max((int) (VehicleUsageLog::query()->max(DB::raw('YEAR(created_at)')) ?? now()->year), now()->year);
        $minYear = (int) (VehicleUsageLog::query()->min(DB::raw('YEAR(created_at)')) ?? $maxYear);
        if ($minYear < 2020) {
            $minYear = 2020;
        }

        return $minYear <= $maxYear ? range($maxYear, $minYear) : [now()->year];
    }

    private function buildLogsQuery(mixed $search, mixed $month, mixed $year)
    {
        $query = VehicleUsageLog::query()->with(['user:id,name,username']);

        if (is_string($search) && $search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term, $search) {
                $q->where('nomor_kendaraan', 'like', $term)
                    ->orWhere('jenis_kendaraan', 'like', $term)
                    ->orWhere('keperluan', 'like', $term)
                    ->orWhere('level_bbm_awal', 'like', $term)
                    ->orWhere('level_bbm_akhir', 'like', $term)
                    ->orWhere('kondisi_sebelum_penggunaan', 'like', $term)
                    ->orWhere('kondisi_setelah_penggunaan', 'like', $term)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $term)->orWhere('username', 'like', $term));

                // Smart Date search
                $lowerSearch = strtolower(trim($search));

                $monthMap = [
                    'januari' => 1, 'january' => 1, 'jan' => 1,
                    'februari' => 2, 'february' => 2, 'feb' => 2,
                    'maret' => 3, 'march' => 3, 'mar' => 3,
                    'april' => 4, 'apr' => 4,
                    'mei' => 5, 'may' => 5,
                    'juni' => 6, 'june' => 6, 'jun' => 6,
                    'juli' => 7, 'july' => 7, 'jul' => 7,
                    'agustus' => 8, 'august' => 8, 'agu' => 8, 'aug' => 8,
                    'september' => 9, 'sep' => 9,
                    'oktober' => 10, 'october' => 10, 'okt' => 10, 'oct' => 10,
                    'november' => 11, 'nov' => 11,
                    'desember' => 12, 'december' => 12, 'des' => 12, 'dec' => 12,
                ];

                $foundMonth = null;
                foreach ($monthMap as $monthName => $monthNum) {
                    if (preg_match('/\b'.preg_quote($monthName, '/').'\b/', $lowerSearch)) {
                        $foundMonth = $monthNum;
                        break;
                    }
                }

                $foundYear = null;
                if (preg_match('/\b(20\d{2})\b/', $lowerSearch, $matches)) {
                    $foundYear = (int) $matches[1];
                }

                $foundDay = null;
                if (preg_match_all('/\b(\d{1,2})\b/', $lowerSearch, $numMatches)) {
                    foreach ($numMatches[1] as $numStr) {
                        $num = (int) $numStr;
                        if ($num >= 1 && $num <= 31 && $num !== $foundYear) {
                            $foundDay = $num;
                            break;
                        }
                    }
                }

                $isoDate = null;
                if (preg_match('/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/', $lowerSearch, $m)) {
                    $isoDate = $m[1].'-'.str_pad($m[2], 2, '0', STR_PAD_LEFT).'-'.str_pad($m[3], 2, '0', STR_PAD_LEFT);
                } elseif (preg_match('/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\b/', $lowerSearch, $m)) {
                    $isoDate = $m[3].'-'.str_pad($m[2], 2, '0', STR_PAD_LEFT).'-'.str_pad($m[1], 2, '0', STR_PAD_LEFT);
                }

                if ($isoDate) {
                    $q->orWhereDate('created_at', $isoDate);
                } elseif ($foundYear && $foundMonth && $foundDay) {
                    $q->orWhereDate('created_at', sprintf('%04d-%02d-%02d', $foundYear, $foundMonth, $foundDay));
                } elseif ($foundMonth && $foundDay) {
                    $q->orWhere(fn ($sub) => $sub->whereMonth('created_at', $foundMonth)->whereDay('created_at', $foundDay));
                } elseif ($foundYear && $foundMonth) {
                    $q->orWhere(fn ($sub) => $sub->whereYear('created_at', $foundYear)->whereMonth('created_at', $foundMonth));
                } elseif ($foundMonth) {
                    $q->orWhereMonth('created_at', $foundMonth);
                } elseif ($foundYear) {
                    $q->orWhereYear('created_at', $foundYear);
                }
            });
        }

        if (is_numeric($month) && $month >= 1 && $month <= 12) {
            $query->whereMonth('created_at', $month);
        }
        if (is_numeric($year)) {
            $query->whereYear('created_at', $year);
        }

        return $query;
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'nullable|exists:vehicle_usage_logs,id',
            'all' => 'nullable|boolean',
            'search' => 'nullable|string',
            'month' => 'nullable|string',
            'year' => 'nullable|string',
        ]);

        if ($request->boolean('all')) {
            $query = $this->buildLogsQuery(
                $request->input('search'),
                $request->input('month'),
                $request->input('year')
            );
            $logsToDelete = $query->get();
        } else {
            $ids = $request->input('ids', []);
            $logsToDelete = VehicleUsageLog::whereIn('id', $ids)->get();
        }

        $count = $logsToDelete->count();

        foreach ($logsToDelete as $log) {
            $log->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' data log pemakaian kendaraan berhasil dihapus.',
        ]);
    }

    public function update(Request $request, VehicleUsageLog $vehicleUsageLog): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $validated = $request->validate([
            'nomor_kendaraan' => ['required', 'exists:kendaraans,nomor_kendaraan'],
            'jam_awal' => ['required', 'date_format:H:i'],
            'jam_akhir' => ['required', 'date_format:H:i', 'after:jam_awal'],
            'keperluan' => ['required', 'string', 'max:10000'],
            'level_bbm_awal' => ['required', 'integer', 'min:0', 'max:100'],
            'level_bbm_akhir' => ['required', 'integer', 'min:0', 'max:100'],
            'km_awal' => ['required', 'integer', 'min:0'],
            'km_akhir' => ['required', 'integer', 'min:0', 'gte:km_awal'],
            'kondisi_sebelum_penggunaan' => ['required', 'string', 'max:10000'],
            'kondisi_setelah_penggunaan' => ['required', 'string', 'max:10000'],
        ], [
            'nomor_kendaraan.required' => 'Pilih nomor kendaraan.',
            'nomor_kendaraan.exists' => 'Nomor kendaraan tidak terdaftar.',
            'jam_awal.required' => 'Jam awal wajib diisi.',
            'jam_awal.date_format' => 'Format jam awal tidak valid.',
            'jam_akhir.required' => 'Jam akhir wajib diisi.',
            'jam_akhir.date_format' => 'Format jam akhir tidak valid.',
            'jam_akhir.after' => 'Jam akhir harus setelah jam awal.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'level_bbm_awal.required' => 'Level BBM awal wajib diisi.',
            'level_bbm_akhir.required' => 'Level BBM akhir wajib diisi.',
            'km_awal.required' => 'KM awal wajib diisi.',
            'km_akhir.required' => 'KM akhir wajib diisi.',
            'km_akhir.gte' => 'KM akhir harus lebih besar atau sama dengan KM awal.',
            'kondisi_sebelum_penggunaan.required' => 'Kondisi sebelum penggunaan wajib diisi.',
            'kondisi_setelah_penggunaan.required' => 'Kondisi setelah penggunaan wajib diisi.',
        ]);

        $kendaraan = Kendaraan::query()->where('nomor_kendaraan', $validated['nomor_kendaraan'])->firstOrFail();

        $vehicleUsageLog->update([
            'kendaraan_id' => $kendaraan->id,
            'nomor_kendaraan' => $kendaraan->nomor_kendaraan,
            'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
            'jam_awal' => $validated['jam_awal'],
            'jam_akhir' => $validated['jam_akhir'],
            'keperluan' => $validated['keperluan'],
            'level_bbm_awal' => (string) $validated['level_bbm_awal'],
            'level_bbm_akhir' => (string) $validated['level_bbm_akhir'],
            'km_awal' => (int) $validated['km_awal'],
            'km_akhir' => (int) $validated['km_akhir'],
            'kondisi_sebelum_penggunaan' => $validated['kondisi_sebelum_penggunaan'],
            'kondisi_setelah_penggunaan' => $validated['kondisi_setelah_penggunaan'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log penggunaan kendaraan berhasil diperbarui.',
        ]);
    }
}
