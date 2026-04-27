<?php

namespace Database\Seeders;

use App\Models\BbmReport;
use App\Models\Kendaraan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BbmReportDummySeeder extends Seeder
{
    /**
     * Hapus semua BBM Report lalu isi data dummy ~3 tahun kalender:
     * dari 1 Januari (tahun sekarang − 3) sampai hari ini, agar grafik per-bulan per-tahun
     * (Jan–Des) punya angka untuk bulan awal tahun paling lama — bukan hanya dari “bulan yang sama” tiga tahun lalu.
     */
    public function run(): void
    {
        $deleted = BbmReport::query()->delete();
        $this->command->info("Menghapus {$deleted} baris lama dari bbm_reports.");

        $driverIds = User::query()->where('role', 'driver')->pluck('id');
        $kendaraans = Kendaraan::query()->get(['id', 'nomor_kendaraan', 'jenis_kendaraan']);

        if ($driverIds->isEmpty()) {
            $this->command->error('Tidak ada user dengan role driver. Jalankan DatabaseSeeder atau buat driver dulu.');

            return;
        }

        if ($kendaraans->isEmpty()) {
            $this->command->error('Tidak ada data kendaraan. Jalankan DatabaseSeeder atau isi master armada dulu.');

            return;
        }

        $start = Carbon::now()->copy()->subYears(3)->startOfYear();
        $end = Carbon::now()->endOfDay();

        $rows = [];
        $monthCursor = $start->copy();

        while ($monthCursor->lte($end)) {
            $daysInMonth = $monthCursor->daysInMonth;
            $reportsThisMonth = random_int(14, 38);

            for ($i = 0; $i < $reportsThisMonth; $i++) {
                $day = random_int(1, $daysInMonth);
                $tanggal = $monthCursor->copy()->day($day)->startOfDay();

                if ($tanggal->gt($end)) {
                    continue;
                }

                $k = $kendaraans->random();
                $liter = round(fake()->randomFloat(3, 8, 95), 3);
                $hargaPerLiter = round(fake()->randomFloat(2, 10200, 16200), 2);
                $totalHarga = round($liter * $hargaPerLiter, 2);

                $kmBefore = fake()->numberBetween(8_000, 380_000);
                $kmDelta = max(1, min(800, (int) round($liter * fake()->randomFloat(2, 0.45, 1.35))));
                $kmAfter = $kmBefore + $kmDelta;

                $hour = random_int(5, 20);
                $minute = random_int(0, 59);
                $waktu = sprintf('%02d:%02d:00', $hour, $minute);

                $createdAt = Carbon::parse($tanggal->toDateString().' '.$waktu);

                $rows[] = [
                    'user_id' => (int) $driverIds->random(),
                    'kendaraan_id' => $k->id,
                    'nomor_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'tanggal' => $tanggal->toDateString(),
                    'waktu' => $waktu,
                    'odometer_sebelum' => $kmBefore,
                    'odometer_sesudah' => $kmAfter,
                    'liter' => $liter,
                    'harga_per_liter' => $hargaPerLiter,
                    'total_harga' => $totalHarga,
                    'odometer_photo_path' => 'bbm-reports/seed/dummy-odometer.jpg',
                    'struk_photo_path' => 'bbm-reports/seed/dummy-struk.jpg',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }

            $monthCursor->addMonth();
        }

        foreach (array_chunk($rows, 400) as $chunk) {
            BbmReport::insert($chunk);
        }

        $this->command->info('Berhasil menyimpan '.count($rows).' laporan BBM dummy ('.$start->format('M Y').' – '.$end->format('M Y').').');
        $this->command->warn('Path foto dummy tidak mengarah ke file nyata; thumbnail di portal bisa 404 sampai ada file di storage.');
    }
}
