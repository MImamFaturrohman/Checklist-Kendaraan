<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistExterior;
use App\Models\ChecklistInterior;
use App\Models\ChecklistMesin;
use App\Models\ChecklistPerlengkapan;
use App\Models\Kendaraan;
use Barryvdh\DomPDF\Facade\Pdf;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\SuperAdminNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    private function isSuperAdmin(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    private function canAccessInspectionPortal(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'admin', 'manager'], true);
    }

    /**
     * Store checklist and generate PDF.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'jam_serah_terima' => 'required',
            'nomor_kendaraan' => 'required|string',
            'jenis_kendaraan' => 'required|string',
            'driver_serah' => 'required|string',
            'driver_terima' => 'required|string',
            'level_bbm' => 'required|numeric|min:0|max:100',
            'km_awal' => 'required|numeric|min:0',
            'km_akhir' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            // Save signature images from base64
            $ttdSerahPath = null;
            $ttdTerimaPath = null;

            if ($request->filled('tanda_tangan_serah') && str_starts_with($request->input('tanda_tangan_serah'), 'data:image')) {
                $ttdSerahPath = $this->saveBase64Image($request->input('tanda_tangan_serah'), 'ttd_serah');
            }
            if ($request->filled('tanda_tangan_terima') && str_starts_with($request->input('tanda_tangan_terima'), 'data:image')) {
                $ttdTerimaPath = $this->saveBase64Image($request->input('tanda_tangan_terima'), 'ttd_terima');
            }

            // Save BBM dashboard photo
            $fotoBbmPath = null;
            if ($request->hasFile('foto_bbm_dashboard')) {
                $fotoBbmPath = $request->file('foto_bbm_dashboard')->store('checklists/bbm', 'public');
            }

            // Create main checklist
            $checklist = Checklist::create([
                'tanggal' => $request->tanggal,
                'shift' => $request->shift,
                'driver_serah' => $request->driver_serah,
                'driver_terima' => $request->driver_terima,
                'nomor_kendaraan' => $request->nomor_kendaraan,
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'jam_serah_terima' => $request->jam_serah_terima,
                'level_bbm' => $request->level_bbm,
                'bbm_terakhir' => $request->bbm_terakhir,
                'km_awal' => $request->km_awal,
                'km_akhir' => $request->km_akhir,
                'foto_bbm_dashboard' => $fotoBbmPath,
                'catatan_khusus' => $request->catatan_khusus,
                'tanda_tangan_serah' => $ttdSerahPath,
                'tanda_tangan_terima' => $ttdTerimaPath,
                'user_id' => auth()->id(),
            ]);

            // Save Exterior
            $exteriorItems = ['body_kendaraan', 'kaca', 'spion', 'lampu_utama', 'lampu_sein', 'ban', 'velg', 'wiper'];
            $exteriorData = ['checklist_id' => $checklist->id];
            foreach ($exteriorItems as $item) {
                $exteriorData[$item] = $request->input("exterior_{$item}");
                $exteriorData["{$item}_keterangan"] = $request->input("exterior_{$item}_catatan");
            }

            // Save exterior photos
            foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side) {
                if ($request->hasFile("exterior_foto_{$side}")) {
                    $exteriorData["foto_{$side}"] = $request->file("exterior_foto_{$side}")->store('checklists/exterior', 'public');
                }
            }
            ChecklistExterior::create($exteriorData);

            // Save Interior
            $interiorItems = ['jok', 'dashboard', 'ac', 'sabuk_pengaman', 'audio', 'kebersihan'];
            $interiorData = ['checklist_id' => $checklist->id];
            foreach ($interiorItems as $item) {
                $interiorData[$item] = $request->input("interior_{$item}");
                $interiorData["{$item}_keterangan"] = $request->input("interior_{$item}_catatan");
            }

            for ($i = 1; $i <= 3; $i++) {
                if ($request->hasFile("interior_foto_{$i}")) {
                    $interiorData["foto_{$i}"] = $request->file("interior_foto_{$i}")->store('checklists/interior', 'public');
                }
            }
            ChecklistInterior::create($interiorData);

            // Save Mesin
            $mesinItems = ['mesin', 'oli', 'radiator', 'rem', 'kopling', 'transmisi', 'indikator'];
            $mesinData = ['checklist_id' => $checklist->id];
            foreach ($mesinItems as $item) {
                $mesinData[$item] = $request->input("mesin_{$item}");
                $mesinData["{$item}_keterangan"] = $request->input("mesin_{$item}_catatan");
            }

            for ($i = 1; $i <= 3; $i++) {
                if ($request->hasFile("mesin_foto_{$i}")) {
                    $mesinData["foto_{$i}"] = $request->file("mesin_foto_{$i}")->store('checklists/mesin', 'public');
                }
            }
            ChecklistMesin::create($mesinData);

            // Save Perlengkapan
            $perlengkapanItems = ['stnk', 'kir', 'dongkrak', 'toolkit', 'segitiga', 'apar', 'ban_cadangan'];
            $perlengkapanData = ['checklist_id' => $checklist->id];
            foreach ($perlengkapanItems as $item) {
                $val = $request->input("perlengkapan.{$item}");
                $perlengkapanData[$item] = $val ? 'ada' : 'tidak_ada';
            }
            ChecklistPerlengkapan::create($perlengkapanData);

            // Auto-save kendaraan to master if not exists
            Kendaraan::firstOrCreate(
                ['nomor_kendaraan' => $request->nomor_kendaraan],
                ['jenis_kendaraan' => $request->jenis_kendaraan]
            );

            // Generate PDF
            $checklist->load(['exterior', 'interior', 'mesin', 'perlengkapan']);
            $pdf = Pdf::loadView('checklists.pdf', ['checklist' => $checklist]);
            $pdfFileName = 'checklist_'.$checklist->id.'_'.now()->format('Ymd_His').'.pdf';
            $pdfPath = 'checklists/pdf/'.$pdfFileName;
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $checklist->update(['pdf_path' => $pdfPath]);

            $pdfUrl = $pdfUrl = Storage::url($pdfPath);

            // Auto-sync to Google Spreadsheet
            try {
                $this->syncSingleToSpreadsheet($checklist);
            } catch (\Throwable $e) {
                Log::warning('Auto-sync to Google Sheets failed', ['message' => $e->getMessage()]);
            }

            DB::afterCommit(function () use ($checklist): void {
                SuperAdminNotifier::checklistSubmitted($checklist);
            });

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil disimpan!',
                'pdf_url' => $pdfUrl,
                'checklist_id' => $checklist->id,
            ]);
        });
    }

    /**
     * Lookup kendaraan by nomor.
     */
    public function lookupKendaraan(Request $request): JsonResponse
    {
        $nomor = $request->query('nomor');
        $kendaraan = Kendaraan::where('nomor_kendaraan', $nomor)->first();

        if ($kendaraan) {
            return response()->json([
                'found' => true,
                'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Get last KM for a vehicle.
     */
    public function lastKm(Request $request): JsonResponse
    {
        $nomor = $request->query('nomor');
        $lastChecklist = Checklist::where('nomor_kendaraan', $nomor)
            ->whereNotNull('km_akhir')
            ->orderByDesc('created_at')
            ->first();

        if ($lastChecklist) {
            $km = $lastChecklist->km_akhir;
        } else {
            $kendaraan = Kendaraan::where('nomor_kendaraan', $nomor)->first();
            $km = $kendaraan?->set_km ?? 0;
        }

        return response()->json([
            'km' => $km,
        ]);
    }

    /**
     * Save base64 image to storage.
     */
    private function saveBase64Image(string $base64, string $prefix): string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);
        $fileName = $prefix.'_'.now()->format('Ymd_His').'_'.uniqid().'.png';
        $path = 'checklists/signatures/'.$fileName;
        Storage::disk('public')->put($path, $image);

        return $path;
    }

    /**
     * Apply common search/filter to checklist query.
     */
    private function applyChecklistFilters(Request $request, $query)
    {
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kendaraan', 'like', "%{$search}%")
                    ->orWhere('driver_serah', 'like', "%{$search}%")
                    ->orWhere('driver_terima', 'like', "%{$search}%")
                    ->orWhere('jenis_kendaraan', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->input('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $to);
        }
        if ($nopol = $request->input('nopol')) {
            $query->where('nomor_kendaraan', $nopol);
        }
        if ($shift = $request->input('shift')) {
            $query->where('shift', $shift);
        }

        return $query;
    }

    /**
     * Combined Portal Pemeriksaan Kendaraan page.
     */
    public function portalPemeriksaan(Request $request)
    {
        abort_unless($this->canAccessInspectionPortal(), 403);
        $canAccessDatabase = $this->isSuperAdmin();
        $pemeriksaanInsightOnlyManager = auth()->user()?->role === 'manager';

        $nopolList = Checklist::select('nomor_kendaraan')->distinct()->orderBy('nomor_kendaraan')->pluck('nomor_kendaraan');

        // Database Sheet stats (unfiltered)
        $allChecklists = Checklist::all();
        $dbStats = [
            'total' => $allChecklists->count(),
            'kendaraan_unik' => $allChecklists->unique('nomor_kendaraan')->count(),
            'driver_aktif' => $allChecklists->unique('driver_serah')->count(),
            'bulan_ini' => $allChecklists->where('tanggal', '>=', now()->startOfMonth())->count(),
        ];

        // Arsip PDF stats (unfiltered)
        $allPdf = Checklist::whereNotNull('pdf_path');
        $pdfStats = [
            'total' => (clone $allPdf)->count(),
            'bulan_ini' => (clone $allPdf)->whereDate('tanggal', '>=', now()->startOfMonth())->count(),
        ];

        // Chart data
        $chartData = $this->buildChartData();

        // Initial paginated data (superadmin only).
        if ($canAccessDatabase) {
            $dbQuery = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan'])->orderByDesc('created_at');
            $this->applyChecklistFilters($request, $dbQuery);
            $dbChecklists = $dbQuery->paginate(10, ['*'], 'db_page')->withQueryString();

            $fotoQuery = Checklist::with(['exterior', 'interior', 'mesin'])->orderByDesc('created_at');
            $this->applyChecklistFilters($request, $fotoQuery);
            $fotoChecklists = $fotoQuery->paginate(10, ['*'], 'foto_page')->withQueryString();

            $pdfQuery = Checklist::whereNotNull('pdf_path')->orderByDesc('created_at');
            $this->applyChecklistFilters($request, $pdfQuery);
            $pdfChecklists = $pdfQuery->paginate(10, ['*'], 'pdf_page')->withQueryString();
        } else {
            $dbChecklists = collect();
            $fotoChecklists = collect();
            $pdfChecklists = collect();
        }

        $dbMeta = $canAccessDatabase ? ['current_page' => $dbChecklists->currentPage(),   'last_page' => $dbChecklists->lastPage(),   'total' => $dbChecklists->total(),   'per_page' => $dbChecklists->perPage()] : null;
        $fotoMeta = $canAccessDatabase ? ['current_page' => $fotoChecklists->currentPage(), 'last_page' => $fotoChecklists->lastPage(), 'total' => $fotoChecklists->total(), 'per_page' => $fotoChecklists->perPage()] : null;
        $pdfMeta = $canAccessDatabase ? ['current_page' => $pdfChecklists->currentPage(),  'last_page' => $pdfChecklists->lastPage(),  'total' => $pdfChecklists->total(),  'per_page' => $pdfChecklists->perPage()] : null;

        return view('admin.portal-pemeriksaan', compact(
            'nopolList', 'dbStats', 'pdfStats', 'chartData',
            'dbChecklists', 'fotoChecklists', 'pdfChecklists',
            'dbMeta', 'fotoMeta', 'pdfMeta', 'canAccessDatabase',
            'pemeriksaanInsightOnlyManager'
        ));
    }

    /**
     * AJAX: Database Sheet filtered data (JSON).
     */
    public function apiPortalDatabaseSheet(Request $request): JsonResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $perPage = min((int) $request->input('per_page', 10), 100);
        $query = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan'])->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $rows = $query->paginate($perPage)->withQueryString();

        $data = $rows->map(fn ($c) => [
            'id' => $c->id,
            'tanggal' => $c->tanggal?->format('d/m/Y'),
            'shift' => $c->shift,
            'nomor_kendaraan' => $c->nomor_kendaraan,
            'jenis_kendaraan' => $c->jenis_kendaraan,
            'driver_serah' => $c->driver_serah,
            'driver_terima' => $c->driver_terima,
            'level_bbm' => $c->level_bbm,
            'km_awal' => number_format($c->km_awal),
            'km_akhir' => number_format($c->km_akhir ?? 0),
            'exterior' => $c->exterior ? [
                'body_kendaraan' => $c->exterior->body_kendaraan,
                'kaca' => $c->exterior->kaca,
                'spion' => $c->exterior->spion,
                'lampu_utama' => $c->exterior->lampu_utama,
                'lampu_sein' => $c->exterior->lampu_sein,
                'ban' => $c->exterior->ban,
                'velg' => $c->exterior->velg,
                'wiper' => $c->exterior->wiper,
            ] : null,
            'interior' => $c->interior ? [
                'jok' => $c->interior->jok,
                'dashboard' => $c->interior->dashboard,
                'ac' => $c->interior->ac,
                'sabuk_pengaman' => $c->interior->sabuk_pengaman,
                'audio' => $c->interior->audio,
                'kebersihan' => $c->interior->kebersihan,
            ] : null,
            'mesin' => $c->mesin ? [
                'mesin' => $c->mesin->mesin,
                'oli' => $c->mesin->oli,
                'radiator' => $c->mesin->radiator,
                'rem' => $c->mesin->rem,
                'kopling' => $c->mesin->kopling,
                'transmisi' => $c->mesin->transmisi,
                'indikator' => $c->mesin->indikator,
            ] : null,
        ]);

        return response()->json([
            'data' => $data,
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'total' => $rows->total(),
            'per_page' => $rows->perPage(),
        ]);
    }

    /**
     * AJAX: Log Foto Fisik filtered data (JSON).
     */
    public function apiPortalLogFoto(Request $request): JsonResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $perPage = min((int) $request->input('per_page', 10), 100);
        $baseUrl = url('/');
        $resolveUrl = function (?string $path) use ($baseUrl) {
            if (! $path) {
                return null;
            }
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            if (str_starts_with($path, '/storage/')) {
                return $baseUrl.$path;
            }
            if (str_starts_with($path, 'storage/')) {
                return $baseUrl.'/'.$path;
            }

            return $baseUrl.'/storage/'.ltrim($path, '/');
        };

        $query = Checklist::with(['exterior', 'interior', 'mesin'])->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $rows = $query->paginate($perPage)->withQueryString();

        $data = $rows->map(fn ($c) => [
            'id' => $c->id,
            'waktu' => ($c->tanggal?->format('d/m/Y') ?? '').' '.($c->jam_serah_terima ?? ''),
            'nomor_kendaraan' => $c->nomor_kendaraan,
            'foto_bbm' => $resolveUrl($c->foto_bbm_dashboard),
            'exterior' => $c->exterior ? [
                'foto_depan' => $resolveUrl($c->exterior->foto_depan),
                'foto_kanan' => $resolveUrl($c->exterior->foto_kanan),
                'foto_kiri' => $resolveUrl($c->exterior->foto_kiri),
                'foto_belakang' => $resolveUrl($c->exterior->foto_belakang),
            ] : null,
            'interior' => $c->interior ? [
                'foto_1' => $resolveUrl($c->interior->foto_1),
                'foto_2' => $resolveUrl($c->interior->foto_2),
                'foto_3' => $resolveUrl($c->interior->foto_3),
            ] : null,
            'mesin' => $c->mesin ? [
                'foto_1' => $resolveUrl($c->mesin->foto_1),
                'foto_2' => $resolveUrl($c->mesin->foto_2),
                'foto_3' => $resolveUrl($c->mesin->foto_3),
            ] : null,
        ]);

        return response()->json([
            'data' => $data,
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'total' => $rows->total(),
            'per_page' => $rows->perPage(),
        ]);
    }

    /**
     * AJAX: Arsip PDF filtered data (JSON).
     */
    public function apiPortalArsipPdf(Request $request): JsonResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $perPage = min((int) $request->input('per_page', 10), 100);
        $baseUrl = url('/');
        $resolveUrl = function (?string $path) use ($baseUrl) {
            if (! $path) {
                return null;
            }
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            if (str_starts_with($path, '/storage/')) {
                return $baseUrl.$path;
            }
            if (str_starts_with($path, 'storage/')) {
                return $baseUrl.'/'.$path;
            }

            return $baseUrl.'/storage/'.ltrim($path, '/');
        };

        $query = Checklist::whereNotNull('pdf_path')->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $rows = $query->paginate($perPage)->withQueryString();

        $data = $rows->map(fn ($c) => [
            'id' => $c->id,
            'tanggal' => $c->tanggal?->format('d/m/Y'),
            'nomor_kendaraan' => $c->nomor_kendaraan,
            'driver_serah' => $c->driver_serah,
            'driver_terima' => $c->driver_terima,
            'shift' => $c->shift,
            'pdf_url' => $resolveUrl($c->pdf_path),
        ]);

        return response()->json([
            'data' => $data,
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'total' => $rows->total(),
            'per_page' => $rows->perPage(),
        ]);
    }

    /**
     * AJAX: Chart data for the portal.
     */
    public function apiPortalCharts(Request $request): JsonResponse
    {
        abort_unless($this->canAccessInspectionPortal(), 403);

        return response()->json($this->buildChartData());
    }

    /**
     * Build chart data arrays for the portal page.
     */
    private function buildChartData(): array
    {
        // Ceklist per kendaraan (top 10)
        $perKendaraan = Checklist::select('nomor_kendaraan', DB::raw('count(*) as total'))
            ->groupBy('nomor_kendaraan')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Ceklist per shift
        $perShift = Checklist::select('shift', DB::raw('count(*) as total'))
            ->groupBy('shift')
            ->orderByDesc('total')
            ->get();

        // Ceklist per bulan (12 bulan terakhir)
        $perBulan = Checklist::select(
            DB::raw("DATE_FORMAT(tanggal, '%Y-%m') as bulan"),
            DB::raw('count(*) as total')
        )
            ->where('tanggal', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Kondisi kendaraan: ok vs tidak_ok (exterior body_kendaraan)
        $exteriorOk = ChecklistExterior::where('body_kendaraan', 'ok')->count();
        $exteriorNok = ChecklistExterior::whereIn('body_kendaraan', ['no', 'tidak_ok'])->count();

        // Rata-rata BBM level per kendaraan
        $bbmPerKendaraan = Checklist::select('nomor_kendaraan', DB::raw('avg(level_bbm) as avg_bbm'))
            ->groupBy('nomor_kendaraan')
            ->orderByDesc('avg_bbm')
            ->limit(8)
            ->get();

        return [
            'perKendaraan' => [
                'labels' => $perKendaraan->pluck('nomor_kendaraan')->toArray(),
                'data' => $perKendaraan->pluck('total')->toArray(),
            ],
            'perShift' => [
                'labels' => $perShift->pluck('shift')->toArray(),
                'data' => $perShift->pluck('total')->toArray(),
            ],
            'perBulan' => [
                'labels' => $perBulan->pluck('bulan')->toArray(),
                'data' => $perBulan->pluck('total')->toArray(),
            ],
            'kondisi' => [
                'ok' => $exteriorOk,
                'nok' => $exteriorNok,
            ],
            'bbmPerKendaraan' => [
                'labels' => $bbmPerKendaraan->pluck('nomor_kendaraan')->toArray(),
                'data' => $bbmPerKendaraan->pluck('avg_bbm')->map(fn ($v) => round($v, 1))->toArray(),
            ],
        ];
    }

    /**
     * Sync all checklist data to Google Spreadsheet.
     */
    public function exportExcel()
    {
        abort_unless($this->isSuperAdmin(), 403);
        $expectsJson = request()->expectsJson() || request()->ajax();
        $spreadsheetId = (string) config('services.google_sheets.spreadsheet_id');
        $sheetName = (string) config('services.google_sheets.sheet_name', 'Database Sheet');
        $credentialsConfig = $this->resolveGoogleCredentialsConfig();

        if (! $spreadsheetId || ! $credentialsConfig) {
            $message = 'Konfigurasi Google Sheets belum lengkap. Isi GOOGLE_SHEETS_SPREADSHEET_ID dan GOOGLE_SHEETS_CREDENTIALS_JSON di .env.';
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('admin.portal-pemeriksaan')->with('error', $message);
        }

        // if (!file_exists($credentialsJson)) {
        //     return redirect()->route('admin.portal-pemeriksaan')->with(
        //         'error',
        //         "File service account tidak ditemukan: {$credentialsJson}"
        //     );
        // }

        try {
            $checklists = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan'])
                ->orderBy('created_at')
                ->get();

            $client = new GoogleClient;
            $client->setAuthConfig($credentialsConfig);
            $client->setScopes([Sheets::SPREADSHEETS]);

            $sheets = new Sheets($client);
            $this->ensureSheetExists($sheets, $spreadsheetId, $sheetName);

            $values = [[
                'No', 'Tanggal', 'Shift', 'Jam', 'Nomor Kendaraan', 'Jenis Kendaraan',
                'Driver Serah', 'Driver Terima', 'BBM (%)', 'BBM Terakhir', 'KM Awal', 'KM Akhir',
                'Ext-Body', 'Ext-Kaca', 'Ext-Spion', 'Ext-Lampu Utama', 'Ext-Lampu Sein', 'Ext-Ban', 'Ext-Velg', 'Ext-Wiper',
                'Int-Jok', 'Int-Dashboard', 'Int-AC', 'Int-Sabuk', 'Int-Audio', 'Int-Kebersihan',
                'Mesin', 'Oli', 'Radiator', 'Rem', 'Kopling', 'Transmisi', 'Indikator',
                'STNK', 'KIR & QR BBM', 'Dongkrak', 'Toolkit', 'Segitiga', 'APAR', 'Ban Cadangan',
                'Catatan',
            ]];

            foreach ($checklists as $i => $checklist) {
                $values[] = $this->checklistToSpreadsheetRow($checklist, $i + 1);
            }

            $normalizedValues = array_map(function ($row) {
                return array_map(function ($cell) {
                    if ($cell === null) {
                        return '';
                    }
                    if (is_scalar($cell)) {
                        return $cell;
                    }
                    if ($cell instanceof \DateTimeInterface) {
                        return $cell->format('Y-m-d H:i:s');
                    }

                    return (string) $cell;
                }, array_values((array) $row));
            }, array_values($values));

            $sheetRangeAll = "{$sheetName}!A:AZ";
            $sheetRangeStart = "{$sheetName}!A1";

            $sheets->spreadsheets_values->clear($spreadsheetId, $sheetRangeAll, new ClearValuesRequest);
            $valueRange = new ValueRange;
            $valueRange->setMajorDimension('ROWS');
            $valueRange->setValues($normalizedValues);
            $result = $sheets->spreadsheets_values->update(
                $spreadsheetId,
                $sheetRangeStart,
                $valueRange,
                ['valueInputOption' => 'RAW']
            );

            $sheetUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit";
            $updatedRows = $result->getUpdatedRows() ?? count($values);
            $message = "Sinkronisasi Google Spreadsheet berhasil ({$updatedRows} baris).";

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sheet_url' => $sheetUrl,
                    'updated_rows' => $updatedRows,
                ]);
            }

            return redirect()
                ->route('admin.portal-pemeriksaan')
                ->with('success', $message)
                ->with('sheet_url', $sheetUrl);
        } catch (\Throwable $e) {
            Log::error('Google Sheets sync failed', [
                'message' => $e->getMessage(),
            ]);
            $message = 'Gagal sinkronisasi ke Google Spreadsheet: '.$e->getMessage();

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->route('admin.portal-pemeriksaan')
                ->with('error', $message);
        }
    }

    private function checklistToSpreadsheetRow(Checklist $c, int $number): array
    {
        return [
            $number,
            $c->tanggal?->format('Y-m-d'),
            $c->shift,
            $c->jam_serah_terima,
            $c->nomor_kendaraan,
            $c->jenis_kendaraan,
            $c->driver_serah,
            $c->driver_terima,
            $c->level_bbm,
            $c->bbm_terakhir,
            $c->km_awal,
            $c->km_akhir,
            strtoupper($c->exterior?->body_kendaraan ?? '-'),
            strtoupper($c->exterior?->kaca ?? '-'),
            strtoupper($c->exterior?->spion ?? '-'),
            strtoupper($c->exterior?->lampu_utama ?? '-'),
            strtoupper($c->exterior?->lampu_sein ?? '-'),
            strtoupper($c->exterior?->ban ?? '-'),
            strtoupper($c->exterior?->velg ?? '-'),
            strtoupper($c->exterior?->wiper ?? '-'),
            strtoupper($c->interior?->jok ?? '-'),
            strtoupper($c->interior?->dashboard ?? '-'),
            strtoupper($c->interior?->ac ?? '-'),
            strtoupper($c->interior?->sabuk_pengaman ?? '-'),
            strtoupper($c->interior?->audio ?? '-'),
            strtoupper($c->interior?->kebersihan ?? '-'),
            strtoupper($c->mesin?->mesin ?? '-'),
            strtoupper($c->mesin?->oli ?? '-'),
            strtoupper($c->mesin?->radiator ?? '-'),
            strtoupper($c->mesin?->rem ?? '-'),
            strtoupper($c->mesin?->kopling ?? '-'),
            strtoupper($c->mesin?->transmisi ?? '-'),
            strtoupper($c->mesin?->indikator ?? '-'),
            strtoupper($c->perlengkapan?->stnk ?? '-'),
            strtoupper($c->perlengkapan?->kir ?? '-'),
            strtoupper($c->perlengkapan?->dongkrak ?? '-'),
            strtoupper($c->perlengkapan?->toolkit ?? '-'),
            strtoupper($c->perlengkapan?->segitiga ?? '-'),
            strtoupper($c->perlengkapan?->apar ?? '-'),
            strtoupper($c->perlengkapan?->ban_cadangan ?? '-'),
            $c->catatan_khusus ?? '-',
        ];
    }

    private function ensureSheetExists(Sheets $sheets, string $spreadsheetId, string $sheetName): void
    {
        $spreadsheet = $sheets->spreadsheets->get($spreadsheetId);
        $titles = collect($spreadsheet->getSheets())
            ->map(fn ($sheet) => $sheet->getProperties()?->getTitle())
            ->filter()
            ->all();

        if (in_array($sheetName, $titles, true)) {
            return;
        }

        $request = new BatchUpdateSpreadsheetRequest([
            'requests' => [
                new SheetsRequest([
                    'addSheet' => [
                        'properties' => ['title' => $sheetName],
                    ],
                ]),
            ],
        ]);

        $sheets->spreadsheets->batchUpdate($spreadsheetId, $request);
    }

    /**
     * Append a single checklist row to Google Spreadsheet.
     */
    private function syncSingleToSpreadsheet(Checklist $checklist): void
    {
        $spreadsheetId = (string) config('services.google_sheets.spreadsheet_id');
        $sheetName = (string) config('services.google_sheets.sheet_name', 'Database Sheet');
        $credentialsConfig = $this->resolveGoogleCredentialsConfig();

        if (! $spreadsheetId || ! $credentialsConfig) {
            return;
        }

        $checklist->loadMissing(['exterior', 'interior', 'mesin', 'perlengkapan']);

        // Calculate row number (total checklists)
        $rowNumber = Checklist::where('id', '<=', $checklist->id)->count();
        $row = $this->checklistToSpreadsheetRow($checklist, $rowNumber);

        $normalizedRow = array_map(function ($cell) {
            if ($cell === null) {
                return '';
            }
            if (is_scalar($cell)) {
                return $cell;
            }
            if ($cell instanceof \DateTimeInterface) {
                return $cell->format('Y-m-d H:i:s');
            }

            return (string) $cell;
        }, array_values($row));

        $client = new GoogleClient;
        $client->setAuthConfig($credentialsConfig);
        $client->setScopes([Sheets::SPREADSHEETS]);

        $sheets = new Sheets($client);
        $this->ensureSheetExists($sheets, $spreadsheetId, $sheetName);

        $range = "{$sheetName}!A1";
        $valueRange = new ValueRange;
        $valueRange->setMajorDimension('ROWS');
        $valueRange->setValues([$normalizedRow]);

        $sheets->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $valueRange,
            ['valueInputOption' => 'RAW', 'insertDataOption' => 'INSERT_ROWS']
        );
    }

    /**
     * Resolve credentials from env as JSON string or file path.
     */
    private function resolveGoogleCredentialsConfig(): ?array
    {
        $raw = trim((string) config('services.google_sheets.credentials_json', ''));
        $candidatePaths = [];

        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['client_email'], $decoded['private_key'])) {
                return $decoded;
            }

            $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $raw);
            if (! str_starts_with($normalized, DIRECTORY_SEPARATOR)
                && ! preg_match('/^[A-Za-z]:\\\\/', $normalized)) {
                $normalized = base_path($normalized);
            }
            $candidatePaths[] = $normalized;
        }

        $candidatePaths[] = storage_path('app/google-service-account.json');

        foreach ($candidatePaths as $path) {
            if (! is_string($path) || $path === '' || ! is_file($path)) {
                continue;
            }

            $content = @file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded['client_email'], $decoded['private_key'])) {
                return $decoded;
            }
        }

        return null;
    }
}
