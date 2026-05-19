<?php

namespace Database\Seeders;

use App\Models\PeminjamanRequest;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;

class PeminjamanDummySeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    private const NAMA_POOL = [
        'Budi Santoso', 'Dewi Lestari', 'Agus Pratama', 'Rina Wulandari',
        'Fajar Nugroho', 'Maya Sari', 'Hendra Wijaya', 'Putri Anggraini',
        'Yusuf Ahmad', 'Lina Marlina', 'Doni Kurniawan', 'Sari Indah',
    ];

    private const ALASAN_SAMPLES = [
        'Koordinasi lapangan dengan kontraktor di area jetty.',
        'Pengawasan pekerjaan maintenance rutin.',
        'Pengantaran dokumen ke kantor cabang.',
        'Inspeksi HSE area operasi.',
        'Rapat koordinasi shift dengan tim produksi.',
        'Pendampingan audit internal.',
        'Survey kondisi jalan akses tambang.',
    ];

    public function run(): void
    {
        $bidangs = $this->requireLeafBidangs();
        $kendaraans = $this->requireKendaraans();
        $managerId = $this->requireManagerId();

        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();
        $rows = [];
        $today = Carbon::today();

        $monthCursor = $start->copy()->startOfMonth();
        while ($monthCursor->lte($end)) {
            $count = random_int(6, 14);

            for ($i = 0; $i < $count; $i++) {
                $tanggalPeminjaman = $this->randomDateBetween(
                    $monthCursor->copy()->startOfMonth(),
                    $monthCursor->copy()->endOfMonth()->min($end)
                )->toDateString();

                $status = $this->pickStatus($tanggalPeminjaman, $today->toDateString());
                $k = $kendaraans->random();
                $bidang = $bidangs->random();
                $createdAt = Carbon::parse($tanggalPeminjaman)->subDays(random_int(0, 12))
                    ->setTime(random_int(8, 17), random_int(0, 59));

                $row = [
                    'nama_lengkap' => fake()->randomElement(self::NAMA_POOL),
                    'nip' => (string) fake()->numberBetween(19850001001, 20050002999),
                    'jabatan' => fake()->randomElement([
                        'Staff Operasi', 'Supervisor HSE', 'Koordinator Lapangan',
                        'Engineer', 'Admin Proyek', 'Foreman',
                    ]),
                    'bidang_id' => $bidang->id,
                    'nomor_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'tanggal_peminjaman' => $tanggalPeminjaman,
                    'alasan' => fake()->randomElement(self::ALASAN_SAMPLES),
                    'tanda_tangan' => null,
                    'pdf_path' => null,
                    'status' => $status,
                    'catatan_manager' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if ($status === 'pending') {
                    $row['tanda_tangan'] = self::DUMMY_TTD;
                    if ($tanggalPeminjaman < $today->toDateString()) {
                        $row['status'] = 'expired';
                        $row['tanda_tangan'] = null;
                    }
                }

                if ($status === 'approved') {
                    $approvedAt = $createdAt->copy()->addHours(random_int(2, 72));
                    $row['approved_by'] = $managerId;
                    $row['approved_at'] = $approvedAt->format('Y-m-d H:i:s');
                    $row['pdf_path'] = 'peminjaman/pdf/seed-'.fake()->uuid().'.pdf';
                    $row['updated_at'] = $approvedAt->format('Y-m-d H:i:s');
                }

                if ($status === 'rejected') {
                    $row['catatan_manager'] = fake()->randomElement([
                        'Kendaraan sudah dibooking unit lain pada tanggal tersebut.',
                        'Mohon ajukan ulang dengan tanggal alternatif.',
                        'Kuota peminjaman minggu ini sudah penuh.',
                    ]);
                    $row['approved_by'] = $managerId;
                    $rejectedAt = $createdAt->copy()->addHours(random_int(4, 48));
                    $row['approved_at'] = $rejectedAt->format('Y-m-d H:i:s');
                    $row['updated_at'] = $rejectedAt->format('Y-m-d H:i:s');
                }

                if ($status === 'expired') {
                    $row['tanggal_peminjaman'] = Carbon::parse($tanggalPeminjaman)
                        ->min($today->copy()->subDays(random_int(3, 120)))
                        ->toDateString();
                    $row['updated_at'] = Carbon::parse($row['tanggal_peminjaman'])->addDay()->format('Y-m-d H:i:s');
                }

                $row['created_at'] = $createdAt->format('Y-m-d H:i:s');
                $row['updated_at'] = $row['updated_at'] instanceof Carbon
                    ? $row['updated_at']->format('Y-m-d H:i:s')
                    : $row['updated_at'];

                $rows[] = $row;
            }

            $monthCursor->addMonth();
        }

        $recentPending = 5;
        for ($p = 0; $p < $recentPending; $p++) {
            $k = $kendaraans->random();
            $futureDate = $today->copy()->addDays(random_int(2, 21))->toDateString();
            $rows[] = [
                'nama_lengkap' => fake()->randomElement(self::NAMA_POOL),
                'nip' => (string) fake()->numberBetween(19850001001, 20050002999),
                'jabatan' => 'Staff Operasi',
                'bidang_id' => $bidangs->random()->id,
                'nomor_kendaraan' => $k->nomor_kendaraan,
                'jenis_kendaraan' => $k->jenis_kendaraan,
                'tanggal_peminjaman' => $futureDate,
                'alasan' => 'Peminjaman operasional mendatang (data uji antrian pending).',
                'tanda_tangan' => self::DUMMY_TTD,
                'pdf_path' => null,
                'status' => 'pending',
                'catatan_manager' => null,
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            PeminjamanRequest::insert($chunk);
        }

        $this->command?->info('Berhasil menyimpan '.count($rows).' permohonan peminjaman dummy.');
    }

    private function pickStatus(string $tanggalPeminjaman, string $today): string
    {
        if ($tanggalPeminjaman >= $today) {
            return $this->weightedPick(['pending', 'approved'], [70, 30]);
        }

        return $this->weightedPick(
            ['approved', 'rejected', 'expired', 'pending'],
            [52, 18, 22, 8]
        );
    }
}
