<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;

/**
 * Mengisi data dummy 4 tahun terakhir untuk portal admin:
 * Checklist, Peminjaman, SPPD, Laporan Kejadian, Log Pemakaian Kendaraan.
 */
class PortalDummyDataSeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    public function run(): void
    {
        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();

        $this->command?->info('Portal dummy data — rentang: '.$start->format('d M Y').' s/d '.$end->format('d M Y'));

        $this->call([
            ChecklistDummySeeder::class,
            PeminjamanDummySeeder::class,
            SppdDummySeeder::class,
            LaporanKejadianDummySeeder::class,
            VehicleUsageLogDummySeeder::class,
        ]);

        $this->command?->info('Selesai. Buka portal admin untuk memeriksa grafik, filter, dan status.');
    }
}
