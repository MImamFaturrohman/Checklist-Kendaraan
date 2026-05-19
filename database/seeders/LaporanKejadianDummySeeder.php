<?php

namespace Database\Seeders;

use App\Models\LaporanKejadian;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LaporanKejadianDummySeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    private const PERISTIWA = [
        'Tabrakan ringan dengan pembatas jalan saat mundur.',
        'Kendaraan tergelincir di jalan licin pasca hujan.',
        'Hampir tertabrak pejalan kaki di area parkir.',
        'Pecah kaca spion akibat ranting pohon.',
        'Mesin mati mendadak di tanjakan.',
    ];

    public function run(): void
    {
        $bidangs = $this->requireLeafBidangs();
        $kendaraans = $this->requireKendaraans();

        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();
        $rows = [];

        $monthCursor = $start->copy()->startOfMonth();
        while ($monthCursor->lte($end)) {
            $count = random_int(2, 6);

            for ($i = 0; $i < $count; $i++) {
                $waktu = $this->randomDateTimeBetween(
                    $monthCursor->copy()->startOfMonth(),
                    $monthCursor->copy()->endOfMonth()->min($end)
                );

                $kategori = $this->weightedPick(['Incident', 'Nearmiss'], [38, 62]);
                $k = $kendaraans->random();
                $bidang = $bidangs->random();
                $lampiranCount = fake()->numberBetween(1, 3);
                $lampiran = [];
                for ($p = 0; $p < $lampiranCount; $p++) {
                    $lampiran[] = [
                        'path' => 'laporan-kejadian/seed/foto-'.fake()->uuid().'.jpg',
                        'penjelasan' => fake()->randomElement([
                            'Kondisi lokasi kejadian',
                            'Kerusakan pada kendaraan',
                            'Sudut pandang jalan',
                            'Tanda bekas benturan',
                        ]),
                    ];
                }

                $withManagerTtd = fake()->boolean(78);
                $needsToken = $bidang->hasManagerContact() && ! $withManagerTtd && fake()->boolean(25);

                $rows[] = [
                    'nama' => fake()->name(),
                    'nip' => (string) fake()->numberBetween(19880001001, 20020001999),
                    'jabatan' => fake()->randomElement(['Staff HSE', 'Supervisor', 'Operator', 'Teknisi']),
                    'bidang_id' => $bidang->id,
                    'waktu_kejadian' => $waktu->format('Y-m-d H:i:s'),
                    'kategori' => $kategori,
                    'lokasi_kejadian' => fake()->randomElement([
                        'Area parkir kantor ADC', 'Jalan akses jetty KM 3', 'Workshop peralatan',
                        'Gudang sparepart', 'Jalan hauling menuju stockpile',
                    ]),
                    'nomor_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'peristiwa' => fake()->randomElement(self::PERISTIWA),
                    'sebelum_kejadian' => 'Kendaraan beroperasi normal pada kecepatan rendah.',
                    'uraian_kejadian' => fake()->paragraph(3),
                    'penjelasan_gambar' => $lampiran[0]['penjelasan'],
                    'lampiran_gambar' => json_encode($lampiran),
                    'foto_path' => $lampiran[0]['path'],
                    'ttd_manager' => $withManagerTtd ? self::DUMMY_TTD : null,
                    'ttd_pelapor' => self::DUMMY_TTD,
                    'pdf_path' => $withManagerTtd ? 'laporan-kejadian/pdf/seed-'.Str::random(8).'.pdf' : null,
                    'manager_approval_token' => $needsToken ? Str::random(64) : null,
                    'created_at' => $waktu->format('Y-m-d H:i:s'),
                    'updated_at' => ($withManagerTtd ? $waktu->copy()->addHours(random_int(4, 72)) : $waktu)->format('Y-m-d H:i:s'),
                ];
            }

            $monthCursor->addMonth();
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            LaporanKejadian::insert($chunk);
        }

        $this->command?->info('Berhasil menyimpan '.count($rows).' laporan kejadian dummy.');
        $this->command?->warn('Beberapa laporan tanpa ttd_manager mensimulasikan antrian persetujuan manager.');
    }
}
