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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
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
            $exteriorData['catatan'] = $request->input('exterior_catatan');

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
            $interiorData['catatan'] = $request->input('interior_catatan');

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
            $mesinData['catatan'] = $request->input('mesin_catatan');

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
            $pdfFileName = 'checklist_' . $checklist->id . '_' . now()->format('Ymd_His') . '.pdf';
            $pdfPath = 'checklists/pdf/' . $pdfFileName;
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $checklist->update(['pdf_path' => $pdfPath]);

            $pdfUrl = 'http://127.0.0.1:8000/storage/' . ltrim($pdfPath, '/');

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

        return response()->json([
            'km' => $lastChecklist?->km_akhir ?? 0,
        ]);
    }

    /**
     * Save base64 image to storage.
     */
    private function saveBase64Image(string $base64, string $prefix): string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);
        $fileName = $prefix . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.png';
        $path = 'checklists/signatures/' . $fileName;
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
     * Database Sheet admin page with pagination, search, filter.
     */
    public function databaseSheet(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        // Stats (unfiltered)
        $allChecklists = Checklist::all();
        $stats = [
            'total' => $allChecklists->count(),
            'kendaraan_unik' => $allChecklists->unique('nomor_kendaraan')->count(),
            'driver_aktif' => $allChecklists->unique('driver_serah')->count(),
            'bulan_ini' => $allChecklists->where('tanggal', '>=', now()->startOfMonth())->count(),
        ];

        // Nopol options for filter dropdown
        $nopolList = Checklist::select('nomor_kendaraan')->distinct()->orderBy('nomor_kendaraan')->pluck('nomor_kendaraan');

        // Filtered + paginated query
        $query = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan', 'user'])
            ->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $checklists = $query->paginate(10)->withQueryString();

        return view('admin.database-sheet', compact('checklists', 'stats', 'nopolList'));
    }

    /**
     * Log Foto Fisik admin page with pagination, search, filter.
     */
    public function logFotoFisik(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $query = Checklist::with(['exterior', 'interior', 'mesin'])
            ->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $checklists = $query->paginate(10)->withQueryString();

        // Nopol options
        $nopolList = Checklist::select('nomor_kendaraan')->distinct()->orderBy('nomor_kendaraan')->pluck('nomor_kendaraan');

        return view('admin.log-foto-fisik', compact('checklists', 'nopolList'));
    }

    /**
     * Arsip PDF admin page with pagination, search, filter.
     */
    public function arsipPdf(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        // Stats (unfiltered)
        $allPdf = Checklist::whereNotNull('pdf_path');
        $stats = [
            'total' => (clone $allPdf)->count(),
            'bulan_ini' => (clone $allPdf)->whereDate('tanggal', '>=', now()->startOfMonth())->count(),
        ];

        // Nopol options
        $nopolList = Checklist::whereNotNull('pdf_path')
            ->select('nomor_kendaraan')->distinct()->orderBy('nomor_kendaraan')->pluck('nomor_kendaraan');

        $query = Checklist::whereNotNull('pdf_path')->orderByDesc('created_at');
        $this->applyChecklistFilters($request, $query);
        $checklists = $query->paginate(10)->withQueryString();

        return view('admin.arsip-pdf', compact('checklists', 'stats', 'nopolList'));
    }

    /**
     * Sync all checklist data to Google Spreadsheet.
     */
    public function exportExcel()
    {
        $spreadsheetId = (string) config('services.google_sheets.spreadsheet_id');
        $sheetName = (string) config('services.google_sheets.sheet_name', 'Database Sheet');
        $credentialsPath = (string) config('services.google_sheets.credentials_path');

        if ($spreadsheetId === '' || $credentialsPath === '') {
            return redirect()->route('admin.database-sheet')->with(
                'error',
                'Konfigurasi Google Sheets belum lengkap. Isi GOOGLE_SHEETS_SPREADSHEET_ID dan GOOGLE_SHEETS_CREDENTIALS_PATH di .env.'
            );
        }

        if (!file_exists($credentialsPath)) {
            return redirect()->route('admin.database-sheet')->with(
                'error',
                "File service account tidak ditemukan: {$credentialsPath}"
            );
        }

        try {
            $checklists = Checklist::with(['exterior', 'interior', 'mesin', 'perlengkapan'])
                ->orderBy('created_at')
                ->get();

            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
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

            $sheets->spreadsheets_values->clear($spreadsheetId, $sheetRangeAll, new ClearValuesRequest());
            $valueRange = new ValueRange();
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

            return redirect()
                ->route('admin.database-sheet')
                ->with('success', "Sinkronisasi Google Spreadsheet berhasil ({$updatedRows} baris).")
                ->with('sheet_url', $sheetUrl);
        } catch (\Throwable $e) {
            Log::error('Google Sheets sync failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.database-sheet')
                ->with('error', 'Gagal sinkronisasi ke Google Spreadsheet: ' . $e->getMessage());
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
            ->map(fn($sheet) => $sheet->getProperties()?->getTitle())
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
}
