<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Sppd;
use App\Models\SppdFuel;
use App\Models\SppdToll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SppdController extends Controller
{
    private function isDriverRole(): bool
    {
        $r = auth()->user()?->role;

        return in_array($r, ['driver', 'pic_kendaraan'], true);
    }

    public function index(Request $request): View
    {
        abort_unless($this->isDriverRole(), 403);

        $sppds = Sppd::query()
            ->where('user_id', auth()->id())
            ->with(['tolls', 'fuels'])
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('sppd.index', [
            'sppds' => $sppds,
        ]);
    }

    public function showJson(Sppd $sppd): JsonResponse
    {
        abort_unless($this->isDriverRole(), 403);
        abort_unless($sppd->isOwnedBy(auth()->id()), 403);

        $sppd->load(['tolls', 'fuels', 'approver:id,name']);

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
            foreach ($sppd->fuels as $f) {
                if ($f->odometer_path) {
                    Storage::disk('public')->delete($f->odometer_path);
                }
                if ($f->struk_path) {
                    Storage::disk('public')->delete($f->struk_path);
                }
            }
            if ($sppd->signature_path) {
                Storage::disk('public')->delete($sppd->signature_path);
            }
            if ($sppd->pdf_path) {
                Storage::disk('public')->delete($sppd->pdf_path);
            }
            $sppd->delete();
        });

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
    private function isFuelRowCompletelyEmpty(Request $request, int $idx, array $row): bool
    {
        if (filled($row['liter'] ?? null) || filled($row['harga_per_liter'] ?? null)) {
            return false;
        }
        if ($request->hasFile("fuels.{$idx}.odometer") || $request->hasFile("fuels.{$idx}.struk")) {
            return false;
        }
        if (filled($row['odometer_existing'] ?? null) || filled($row['struk_existing'] ?? null)) {
            return false;
        }

        return true;
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

        $validated = $request->validate([
            'keperluan_dinas' => 'required|string|max:500',
            'no_kendaraan' => 'required|string|max:50',
            'jenis_kendaraan' => 'required|string|max:120',
            'tanggal_dinas' => 'required|date',
            'tujuan' => 'required|string|max:2000',
            'tanda_tangan' => 'required|string',
            'tolls' => 'nullable|array',
            'tolls.*.dari_tol' => 'nullable|string|max:255',
            'tolls.*.ke_tol' => 'nullable|string|max:255',
            'tolls.*.harga' => 'nullable|numeric|min:0',
            'fuels' => 'required|array|min:1',
            'fuels.*.liter' => 'nullable|numeric|min:0',
            'fuels.*.harga_per_liter' => 'nullable|numeric|min:0',
            'fuels.*.odometer' => 'nullable|file|image|max:10240',
            'fuels.*.struk' => 'nullable|file|image|max:10240',
            'fuels.*.odometer_existing' => 'nullable|string|max:500',
            'fuels.*.struk_existing' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $namaDriver = $user->name ?? $user->username ?? 'Driver';

        $tollsIn = collect($validated['tolls'] ?? [])->filter(function ($row) {
            return filled($row['dari_tol'] ?? null)
                || filled($row['ke_tol'] ?? null)
                || (float) ($row['harga'] ?? 0) > 0;
        })->values();

        $nonEmptyFuels = [];
        foreach ($validated['fuels'] as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            $i = (int) $idx;
            if ($this->isFuelRowCompletelyEmpty($request, $i, $row)) {
                continue;
            }
            $nonEmptyFuels[] = ['idx' => $i, 'row' => $row];
        }

        if (count($nonEmptyFuels) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal isi satu baris BBM (Liter, harga, foto odometer & struk).',
            ], 422);
        }

        foreach ($nonEmptyFuels as $item) {
            $idx = $item['idx'];
            $row = $item['row'];
            if (! filled($row['liter'] ?? null) || ! filled($row['harga_per_liter'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lengkapi Liter dan harga per liter pada setiap baris BBM yang diisi.',
                ], 422);
            }
            $hasOdo = $request->hasFile("fuels.{$idx}.odometer")
                || filled($row['odometer_existing'] ?? null);
            $hasStruk = $request->hasFile("fuels.{$idx}.struk")
                || filled($row['struk_existing'] ?? null);
            if (! $hasOdo || ! $hasStruk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setiap baris BBM yang diisi wajib memiliki foto odometer dan foto struk.',
                ], 422);
            }
        }

        $totalBbm = 0.0;
        foreach ($nonEmptyFuels as $item) {
            $r = $item['row'];
            $totalBbm += round((float) $r['liter'] * (float) $r['harga_per_liter'], 2);
        }

        $totalTol = $tollsIn->sum(fn ($t) => (float) ($t['harga'] ?? 0));
        $grandTotal = $totalTol + $totalBbm;

        if (! str_starts_with($request->input('tanda_tangan'), 'data:image')) {
            return response()->json(['success' => false, 'message' => 'Tanda tangan tidak valid.'], 422);
        }

        $allowedExistingMedia = collect();
        if ($existing) {
            $existing->load('fuels');
            foreach ($existing->fuels as $f) {
                if ($f->odometer_path) {
                    $allowedExistingMedia->push($f->odometer_path);
                }
                if ($f->struk_path) {
                    $allowedExistingMedia->push($f->struk_path);
                }
            }
        }

        $savedSppdId = null;

        try {
            DB::transaction(function () use ($request, $existing, $validated, $user, $namaDriver, $tollsIn, $nonEmptyFuels, $totalTol, $totalBbm, $grandTotal, $allowedExistingMedia, &$savedSppdId) {
                $signaturePath = $this->saveBase64Image($request->input('tanda_tangan'), 'sppd_sig');

                if ($existing) {
                    $oldPdfPath = $existing->pdf_path;
                    $oldFuelPaths = $allowedExistingMedia->all();

                    if ($existing->signature_path) {
                        Storage::disk('public')->delete($existing->signature_path);
                    }
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
                        'signature_path' => $signaturePath,
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
                        'signature_path' => $signaturePath,
                    ]);
                }

                $sort = 0;
                foreach ($tollsIn as $t) {
                    SppdToll::create([
                        'sppd_id' => $sppd->id,
                        'dari_tol' => (string) ($t['dari_tol'] ?? ''),
                        'ke_tol' => (string) ($t['ke_tol'] ?? ''),
                        'harga' => (float) ($t['harga'] ?? 0),
                        'sort_order' => $sort++,
                    ]);
                }

                $fuelRows = $request->input('fuels', []);
                $newFuelPaths = [];
                $sortOrder = 0;

                foreach ($nonEmptyFuels as $item) {
                    $idx = $item['idx'];
                    $fuelData = $item['row'];
                    $liter = (float) $fuelData['liter'];
                    $hpl = (float) $fuelData['harga_per_liter'];
                    $rowTotal = round($liter * $hpl, 2);

                    $odoPath = null;
                    $strukPath = null;

                    $fileOdo = $request->file("fuels.{$idx}.odometer");
                    $fileStruk = $request->file("fuels.{$idx}.struk");

                    if ($fileOdo) {
                        $odoPath = $fileOdo->store('sppd/fuels/odometer', 'public');
                    } else {
                        $exOdo = $fuelRows[$idx]['odometer_existing'] ?? null;
                        if ($exOdo && $allowedExistingMedia->contains($exOdo)) {
                            $odoPath = $exOdo;
                        }
                    }

                    if ($fileStruk) {
                        $strukPath = $fileStruk->store('sppd/fuels/struk', 'public');
                    } else {
                        $exStruk = $fuelRows[$idx]['struk_existing'] ?? null;
                        if ($exStruk && $allowedExistingMedia->contains($exStruk)) {
                            $strukPath = $exStruk;
                        }
                    }

                    if ($odoPath) {
                        $newFuelPaths[] = $odoPath;
                    }
                    if ($strukPath) {
                        $newFuelPaths[] = $strukPath;
                    }

                    SppdFuel::create([
                        'sppd_id' => $sppd->id,
                        'liter' => $liter,
                        'harga_per_liter' => $hpl,
                        'total' => $rowTotal,
                        'odometer_path' => $odoPath,
                        'struk_path' => $strukPath,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                $savedSppdId = $sppd->id;

                if ($existing && isset($oldFuelPaths)) {
                    foreach (array_diff($oldFuelPaths, $newFuelPaths) as $orphan) {
                        if ($orphan) {
                            Storage::disk('public')->delete($orphan);
                        }
                    }
                }
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

    private function saveBase64Image(string $base64, string $prefix): string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image ?: '');
        $fileName = $prefix.'_'.now()->format('Ymd_His').'_'.uniqid().'.png';
        $path = 'sppd/signatures/'.$fileName;
        Storage::disk('public')->put($path, $image);

        return $path;
    }
}
