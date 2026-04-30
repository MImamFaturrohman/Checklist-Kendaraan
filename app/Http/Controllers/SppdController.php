<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Sppd;
use App\Models\SppdFuel;
use App\Models\SppdToll;
use App\Support\SppdStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SppdController extends Controller
{
    /** @var list<int> */
    private const SPPD_PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private function isDriverRole(): bool
    {
        $r = auth()->user()?->role;

        return in_array($r, ['driver', 'pic_kendaraan'], true);
    }

    private function resolveSppdPerPage(Request $request): int
    {
        $n = (int) $request->query('per_page', 10);

        return in_array($n, self::SPPD_PER_PAGE_OPTIONS, true) ? $n : 10;
    }

    public function index(Request $request): View|Response
    {
        abort_unless($this->isDriverRole(), 403);

        $perPage = $this->resolveSppdPerPage($request);

        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $query = Sppd::query()
            ->where('user_id', auth()->id())
            ->with(['tolls', 'fuels'])
            ->orderByDesc('created_at');

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like) {
                $w->where('keperluan_dinas', 'like', $like)
                    ->orWhere('no_kendaraan', 'like', $like)
                    ->orWhere('jenis_kendaraan', 'like', $like)
                    ->orWhere('tujuan', 'like', $like);
            });
        }

        if ($status && in_array($status, SppdStatus::adminFilterOptions(), true)) {
            $query->where('status', $status);
        }

        $sppds = $query
            ->paginate($perPage)
            ->onEachSide(0)
            ->withQueryString();

        $view = view('sppd.index', [
            'sppds' => $sppds,
        ]);

        if ($request->header('X-VMS-SPPD-Fragment') === '1') {
            return response($view->fragment('sppd-driver-body'));
        }

        return $view;
    }

    public function showJson(Sppd $sppd): JsonResponse
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);

        $sppd->load(['tolls', 'fuels', 'approver:id,name', 'adminVerifier:id,name']);

        return response()->json(['sppd' => $sppd->toDetailArray()]);
    }

    public function create(): View
    {
        abort_unless($this->isDriverRole(), 403);

        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();
        $user = auth()->user();

        return view('sppd.form', [
            'sppd' => null,
            'kendaraans' => $kendaraans,
            'user' => $user,
            'isEdit' => false,
        ]);
    }

    public function edit(Sppd $sppd): View
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);
        abort_unless($sppd->canDriverEdit(), 403);

        $sppd->load(['tolls', 'fuels']);
        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();

        return view('sppd.form', [
            'sppd' => $sppd,
            'kendaraans' => $kendaraans,
            'user' => auth()->user(),
            'isEdit' => true,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($this->isDriverRole(), 403);

        return $this->persistSppd($request, null);
    }

    public function update(Request $request, Sppd $sppd): JsonResponse
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);
        abort_unless($sppd->canDriverEdit(), 403);

        return $this->persistSppd($request, $sppd);
    }

    public function destroy(Sppd $sppd): RedirectResponse
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);
        abort_unless($sppd->canDriverDelete(), 403);

        DB::transaction(function () use ($sppd) {
            if ($sppd->pdf_path) {
                Storage::disk('public')->delete($sppd->pdf_path);
            }
            $sppd->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Rekap SPPD dihapus.']);
        }

        return redirect()->route('sppd.index')->with('ok', 'Rekap SPPD dihapus.');
    }

    public function downloadPdf(Sppd $sppd)
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);
        abort_unless($sppd->canDriverViewPdf(), 403);
        abort_unless($sppd->pdf_path && Storage::disk('public')->exists($sppd->pdf_path), 404);

        return response()->download(
            Storage::disk('public')->path($sppd->pdf_path),
            'Rekap_SPPD_'.$sppd->id.'.pdf'
        );
    }

    public function markCompleted(Sppd $sppd): RedirectResponse
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);
        abort_unless($sppd->status === Sppd::STATUS_APPROVED, 403);

        $sppd->update(['status' => Sppd::STATUS_COMPLETED]);

        return redirect()->route('sppd.index')->with('ok', 'Laporan ditandai selesai.');
    }

    /**
     * Baris BBM yang benar-benar kosong (tanpa isian & tanpa unggah) diabaikan.
     */
    private function isFuelRowCompletelyEmpty(array $row): bool
    {
        return ! filled($row['liter'] ?? null) && ! filled($row['harga_per_liter'] ?? null);
    }

    private function normalizeSppdTollGroups(Request $request): void
    {
        foreach (['tolls_berangkat', 'tolls_kembali'] as $key) {
            $rows = $request->input($key, []);
            if (! is_array($rows)) {
                continue;
            }
            foreach ($rows as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach (['dari_tol', 'ke_tol', 'harga'] as $k) {
                    if (array_key_exists($k, $row) && $row[$k] === '') {
                        $rows[$i][$k] = null;
                    }
                }
            }
            $request->merge([$key => $rows]);
        }
    }

    /**
     * @param  array<int, mixed>  $rows
     */
    private function filterNonEmptyTollRows(array $rows): Collection
    {
        return collect($rows)->filter(function ($row) {
            if (! is_array($row)) {
                return false;
            }

            return filled($row['dari_tol'] ?? null)
                || filled($row['ke_tol'] ?? null)
                || (float) ($row['harga'] ?? 0) > 0;
        })->values();
    }

    private function validateTollGroupOrFail(Collection $rows, string $label): ?JsonResponse
    {
        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Minimal isi satu baris biaya tol {$label} (Dari tol, Ke tol, dan Harga).",
            ], 422);
        }
        $first = $rows->first();
        $h0 = $first['harga'] ?? null;
        if (! filled($first['dari_tol'] ?? null) || ! filled($first['ke_tol'] ?? null)
            || $h0 === null || $h0 === '') {
            return response()->json([
                'success' => false,
                'message' => "Baris pertama biaya tol {$label} wajib diisi lengkap: Dari tol, Ke tol, dan Harga (Rp).",
            ], 422);
        }
        foreach ($rows as $idx => $row) {
            if ((int) $idx === 0) {
                continue;
            }
            $any = filled($row['dari_tol'] ?? null)
                || filled($row['ke_tol'] ?? null)
                || (float) ($row['harga'] ?? 0) > 0;
            if (! $any) {
                continue;
            }
            $h = $row['harga'] ?? null;
            if (! filled($row['dari_tol'] ?? null) || ! filled($row['ke_tol'] ?? null)
                || $h === null || $h === '') {
                return response()->json([
                    'success' => false,
                    'message' => "Baris biaya tol {$label} ke-".($idx + 1).': jika diisi, lengkapi Dari tol, Ke tol, dan Harga.',
                ], 422);
            }
        }

        return null;
    }

    private function persistSppd(Request $request, ?Sppd $existing): JsonResponse
    {
        $fuels = $request->input('fuels', []);
        if (is_array($fuels)) {
            foreach ($fuels as $i => $fuel) {
                if (! is_array($fuel)) {
                    continue;
                }
                foreach (['liter', 'harga_per_liter'] as $k) {
                    if (array_key_exists($k, $fuel) && $fuel[$k] === '') {
                        $fuels[$i][$k] = null;
                    }
                }
            }
            $request->merge(['fuels' => $fuels]);
        }

        $this->normalizeSppdTollGroups($request);

        $validated = $request->validate([
            'keperluan_dinas' => 'required|string|max:500',
            'no_kendaraan' => 'required|string|max:50',
            'jenis_kendaraan' => 'required|string|max:120',
            'tanggal_dinas' => 'required|date',
            'tujuan' => 'required|string|max:2000',
            'tolls_berangkat' => 'nullable|array',
            'tolls_berangkat.*.dari_tol' => 'nullable|string|max:255',
            'tolls_berangkat.*.ke_tol' => 'nullable|string|max:255',
            'tolls_berangkat.*.harga' => 'nullable|numeric|min:0',
            'tolls_kembali' => 'nullable|array',
            'tolls_kembali.*.dari_tol' => 'nullable|string|max:255',
            'tolls_kembali.*.ke_tol' => 'nullable|string|max:255',
            'tolls_kembali.*.harga' => 'nullable|numeric|min:0',
            'fuels' => 'required|array|min:1',
            'fuels.*.liter' => 'nullable|numeric|min:0',
            'fuels.*.harga_per_liter' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $namaDriver = $user->name ?? $user->username ?? 'Driver';

        $tollsBer = $this->filterNonEmptyTollRows($validated['tolls_berangkat'] ?? []);
        $tollsKem = $this->filterNonEmptyTollRows($validated['tolls_kembali'] ?? []);
        if ($err = $this->validateTollGroupOrFail($tollsBer, 'berangkat')) {
            return $err;
        }
        if ($err = $this->validateTollGroupOrFail($tollsKem, 'kembali')) {
            return $err;
        }

        $tollsIn = $tollsBer->map(fn (array $t) => array_merge($t, ['leg' => 'berangkat']))
            ->concat($tollsKem->map(fn (array $t) => array_merge($t, ['leg' => 'kembali'])));

        $nonEmptyFuels = [];
        foreach ($validated['fuels'] as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            $i = (int) $idx;
            if ($this->isFuelRowCompletelyEmpty($row)) {
                continue;
            }
            $nonEmptyFuels[] = ['idx' => $i, 'row' => $row];
        }

        if (count($nonEmptyFuels) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal isi satu baris BBM (Liter dan harga per liter).',
            ], 422);
        }

        foreach ($nonEmptyFuels as $item) {
            $row = $item['row'];
            if (! filled($row['liter'] ?? null) || ! filled($row['harga_per_liter'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lengkapi Liter dan harga per liter pada setiap baris BBM yang diisi.',
                ], 422);
            }
        }

        $totalBbm = 0.0;
        foreach ($nonEmptyFuels as $item) {
            $r = $item['row'];
            $totalBbm += round((float) $r['liter'] * (float) $r['harga_per_liter'], 2);
        }

        $totalTol = $tollsIn->sum(fn (array $t) => (float) ($t['harga'] ?? 0));
        $grandTotal = $totalTol + $totalBbm;

        $savedSppdId = null;

        try {
            DB::transaction(function () use ($request, $existing, $validated, $user, $namaDriver, $tollsIn, $nonEmptyFuels, $totalTol, $totalBbm, $grandTotal, &$savedSppdId) {
                if ($existing) {
                    $oldPdfPath = $existing->pdf_path;

                    $existing->tolls()->delete();
                    $existing->fuels()->delete();
                    $sppd = $existing;
                    $sppd->update([
                        'nama_driver' => $namaDriver,
                        'keperluan_dinas' => $validated['keperluan_dinas'],
                        'no_kendaraan' => $validated['no_kendaraan'],
                        'jenis_kendaraan' => $validated['jenis_kendaraan'],
                        'tanggal_dinas' => $validated['tanggal_dinas'],
                        'tujuan' => $validated['tujuan'],
                        'total_tol' => $totalTol,
                        'total_bbm' => $totalBbm,
                        'grand_total' => $grandTotal,
                        'status' => Sppd::STATUS_PENDING,
                        'revision_note' => null,
                        'revision_at' => null,
                        'rejection_note' => null,
                        'rejected_at' => null,
                        'rejected_by' => null,
                        'approved_by' => null,
                        'approved_at' => null,
                        'admin_verified_by' => null,
                        'admin_verified_at' => null,
                        'pdf_path' => null,
                    ]);
                    if ($oldPdfPath) {
                        Storage::disk('public')->delete($oldPdfPath);
                    }
                } else {
                    $sppd = Sppd::create([
                        'user_id' => $user->id,
                        'nama_driver' => $namaDriver,
                        'keperluan_dinas' => $validated['keperluan_dinas'],
                        'no_kendaraan' => $validated['no_kendaraan'],
                        'jenis_kendaraan' => $validated['jenis_kendaraan'],
                        'tanggal_dinas' => $validated['tanggal_dinas'],
                        'tujuan' => $validated['tujuan'],
                        'total_tol' => $totalTol,
                        'total_bbm' => $totalBbm,
                        'grand_total' => $grandTotal,
                        'status' => Sppd::STATUS_PENDING,
                    ]);
                }

                $sort = 0;
                foreach ($tollsIn as $t) {
                    SppdToll::create([
                        'sppd_id' => $sppd->id,
                        'leg' => (string) ($t['leg'] ?? 'berangkat'),
                        'dari_tol' => (string) ($t['dari_tol'] ?? ''),
                        'ke_tol' => (string) ($t['ke_tol'] ?? ''),
                        'harga' => (float) ($t['harga'] ?? 0),
                        'sort_order' => $sort++,
                    ]);
                }

                $sortOrder = 0;

                foreach ($nonEmptyFuels as $item) {
                    $fuelData = $item['row'];
                    $liter = (float) $fuelData['liter'];
                    $hpl = (float) $fuelData['harga_per_liter'];
                    $rowTotal = round($liter * $hpl, 2);

                    SppdFuel::create([
                        'sppd_id' => $sppd->id,
                        'liter' => $liter,
                        'harga_per_liter' => $hpl,
                        'total' => $rowTotal,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                $savedSppdId = $sppd->id;
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekap SPPD berhasil dikirim.',
            'redirect' => route('sppd.index'),
            'id' => $savedSppdId,
        ]);
    }
}
