<?php

namespace App\Http\Controllers;

use App\Models\BbmReport;
use App\Models\Kendaraan;
use App\Support\BbmOdometerPhotoGrid;
use App\Support\DriverShift;
use App\Support\SuperAdminNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BbmReportController extends Controller
{
    private function authorizeDriver(): void
    {
        abort_unless(auth()->user()?->role === 'driver', 403);
    }

    public function create(): View
    {
        $this->authorizeDriver();

        $kendaraans = Kendaraan::orderBy('nomor_kendaraan')->get();

        $driverShiftAtLogin = session('driver_shift_at_login');
        if (! is_array($driverShiftAtLogin) || empty($driverShiftAtLogin['code'])) {
            $s = DriverShift::current();
            $driverShiftAtLogin = ['code' => $s['code'], 'label' => $s['label']];
        }

        return view('bbm-reports.create', [
            'kendaraans' => $kendaraans,
            'user' => auth()->user(),
            'driverShiftAtLogin' => $driverShiftAtLogin,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeDriver();

        $wantsJson = $request->expectsJson();

        $shiftLogin = session('driver_shift_at_login');
        if (! is_array($shiftLogin) || empty($shiftLogin['code']) || ! in_array($shiftLogin['code'], DriverShift::CODES, true)) {
            $s = DriverShift::current();
            $shiftLogin = ['code' => $s['code'], 'label' => $s['label']];
            $request->session()->put('driver_shift_at_login', $shiftLogin);
        }
        $shiftCode = $shiftLogin['code'];

        $validated = $request->validate([
            'nomor_kendaraan' => ['required', 'string', 'exists:kendaraans,nomor_kendaraan'],
            'jenis_pengisian' => ['required', 'string', Rule::in(BbmReport::JENIS_PENGISIAN_VALUES)],
            'tanggal' => ['required', 'date'],
            'waktu' => ['required', 'date_format:H:i'],
            'odometer_sebelum' => ['required', 'integer', 'min:0'],
            'odometer_sesudah' => ['required', 'integer', 'min:0', 'gte:odometer_sebelum'],
            'liter' => ['required', 'numeric', 'min:0.001'],
            'harga_per_liter' => ['required', 'numeric', 'min:0'],
            'foto_odometer_sebelum' => ['required', 'image', 'max:5120'],
            'foto_odometer_sesudah' => ['required', 'image', 'max:5120'],
            'foto_struk' => ['required', 'image', 'max:5120'],
        ], [
            'nomor_kendaraan.required' => 'Pilih nomor kendaraan.',
            'nomor_kendaraan.exists' => 'Nomor kendaraan tidak terdaftar di master armada.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'waktu.required' => 'Waktu wajib diisi.',
            'waktu.date_format' => 'Format waktu tidak valid (gunakan jam dan menit).',
            'odometer_sebelum.required' => 'KM odometer sebelum wajib diisi.',
            'odometer_sebelum.integer' => 'KM sebelum harus bilangan bulat (tanpa desimal).',
            'odometer_sebelum.min' => 'KM sebelum tidak boleh negatif.',
            'odometer_sesudah.required' => 'KM odometer sesudah wajib diisi.',
            'odometer_sesudah.integer' => 'KM sesudah harus bilangan bulat (tanpa desimal).',
            'odometer_sesudah.min' => 'KM sesudah tidak boleh negatif.',
            'odometer_sesudah.gte' => 'KM sesudah harus sama atau lebih besar daripada KM sebelum.',
            'liter.required' => 'Jumlah liter BBM wajib diisi.',
            'liter.numeric' => 'Liter harus berupa angka.',
            'liter.min' => 'Liter minimal 0,001 L.',
            'harga_per_liter.required' => 'Harga per liter wajib diisi.',
            'harga_per_liter.numeric' => 'Harga per liter harus berupa angka.',
            'harga_per_liter.min' => 'Harga per liter tidak boleh negatif.',
            'foto_odometer_sebelum.required' => 'Foto odometer sebelum pengisian wajib diunggah.',
            'foto_odometer_sebelum.image' => 'Foto odometer sebelum harus berupa file gambar (JPG, PNG, dll.).',
            'foto_odometer_sebelum.max' => 'Ukuran foto odometer sebelum maksimal 5 MB.',
            'foto_odometer_sesudah.required' => 'Foto odometer sesudah pengisian wajib diunggah.',
            'foto_odometer_sesudah.image' => 'Foto odometer sesudah harus berupa file gambar (JPG, PNG, dll.).',
            'foto_odometer_sesudah.max' => 'Ukuran foto odometer sesudah maksimal 5 MB.',
            'foto_struk.required' => 'Foto struk pembelian wajib diunggah.',
            'foto_struk.image' => 'Foto struk harus berupa file gambar (JPG, PNG, dll.).',
            'foto_struk.max' => 'Ukuran foto struk maksimal 5 MB.',
        ]);

        $kendaraan = Kendaraan::where('nomor_kendaraan', $validated['nomor_kendaraan'])->firstOrFail();

        $liter = (float) $validated['liter'];
        $hargaPerLiter = (float) $validated['harga_per_liter'];
        $totalHarga = round($liter * $hargaPerLiter, 2);

        $odometerPath = BbmOdometerPhotoGrid::compose(
            $request->file('foto_odometer_sebelum'),
            $request->file('foto_odometer_sesudah'),
        );
        $strukPath = $request->file('foto_struk')->store('bbm-reports/struk', 'public');

        try {
            $report = BbmReport::create([
                'user_id' => auth()->id(),
                'kendaraan_id' => $kendaraan->id,
                'nomor_kendaraan' => $kendaraan->nomor_kendaraan,
                'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
                'jenis_pengisian' => $validated['jenis_pengisian'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'shift' => $shiftCode,
                'odometer_sebelum' => (int) $validated['odometer_sebelum'],
                'odometer_sesudah' => (int) $validated['odometer_sesudah'],
                'liter' => $liter,
                'harga_per_liter' => $hargaPerLiter,
                'total_harga' => $totalHarga,
                'odometer_photo_path' => $odometerPath,
                'struk_photo_path' => $strukPath,
            ]);
            DB::afterCommit(function () use ($report): void {
                SuperAdminNotifier::bbmReportSubmitted($report);
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete([$odometerPath, $strukPath]);

            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan laporan. Silakan coba lagi.',
                ], 500);
            }

            return redirect()->route('bbm-reports.create')->with('bbm_error', 'Gagal menyimpan laporan. Silakan coba lagi.');
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan pengisian BBM berhasil dikirim.',
            ]);
        }

        return redirect()->route('bbm-reports.create')->with('bbm_ok', 'Laporan pengisian BBM berhasil dikirim.');
    }
}
