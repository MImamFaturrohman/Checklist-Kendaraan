<?php

namespace Database\Seeders;

use App\Models\VehicleUsageLog;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;

class VehicleUsageLogDummySeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    private const KEPERLUAN = [
        'Antar-jemput tamu audit ke site.',
        'Pengawalan muatan peralatan ringan.',
        'Patroli rutin area operasi.',
        'Pengantaran staf shift malam.',
        'Koordinasi dengan security gate.',
        'Pengambilan dokumen di gudang Pusat.',
    ];

    private const KONDISI_SEBELUM = [
        'Eksterior bersih, interior rapi, BBM cukup.',
        'Ban tekanan normal, tidak ada indikator warning.',
        'Kaca dan spion utuh, AC dingin.',
        'Odometer sesuai catatan terakhir.',
    ];

    private const KONDISI_SESUDAH = [
        'Kendaraan dikembalikan dalam kondisi sama, BBM berkurang sesuai jarak.',
        'Tidak ada kerusakan baru, interior dibersihkan ringan.',
        'Odometer sesuai rencana perjalanan.',
        'Tidak ada keluhan dari penumpang.',
    ];

    public function run(): void
    {
        $drivers = $this->requireDrivers();
        $kendaraans = $this->requireKendaraans();

        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();
        $rows = [];

        $monthCursor = $start->copy()->startOfMonth();
        while ($monthCursor->lte($end)) {
            $count = random_int(14, 32);

            for ($i = 0; $i < $count; $i++) {
                $tanggal = $this->randomDateBetween(
                    $monthCursor->copy()->startOfMonth(),
                    $monthCursor->copy()->endOfMonth()->min($end)
                );

                $jamAwalH = random_int(6, 18);
                $durasiMenit = random_int(45, 480);
                $jamAwal = Carbon::createFromTime($jamAwalH, random_int(0, 59));
                $jamAkhir = $jamAwal->copy()->addMinutes($durasiMenit);
                if ($jamAkhir->hour >= 23) {
                    $jamAkhir = Carbon::createFromTime(22, 30);
                }

                $k = $kendaraans->random();
                $bbmAwal = fake()->numberBetween(35, 98);
                $bbmAkhir = max(5, $bbmAwal - fake()->numberBetween(2, 25));
                $kmAwal = fake()->numberBetween(8_000, 400_000);
                $kmAkhir = $kmAwal + fake()->numberBetween(5, 120);

                $createdAt = $tanggal->copy()->setTimeFrom($jamAkhir)->addMinutes(random_int(5, 90));

                $rows[] = [
                    'user_id' => (int) $drivers->random()->id,
                    'kendaraan_id' => $k->id,
                    'nomor_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'jam_awal' => $jamAwal->format('H:i:s'),
                    'jam_akhir' => $jamAkhir->format('H:i:s'),
                    'keperluan' => fake()->randomElement(self::KEPERLUAN),
                    'level_bbm_awal' => (string) $bbmAwal,
                    'level_bbm_akhir' => (string) $bbmAkhir,
                    'km_awal' => $kmAwal,
                    'km_akhir' => $kmAkhir,
                    'kondisi_sebelum_penggunaan' => fake()->randomElement(self::KONDISI_SEBELUM),
                    'kondisi_setelah_penggunaan' => fake()->randomElement(self::KONDISI_SESUDAH),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }

            $monthCursor->addMonth();
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            VehicleUsageLog::insert($chunk);
        }

        $this->command?->info('Berhasil menyimpan '.count($rows).' log pemakaian kendaraan dummy.');
    }
}
