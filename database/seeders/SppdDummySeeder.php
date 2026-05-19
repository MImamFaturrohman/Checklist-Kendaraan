<?php

namespace Database\Seeders;

use App\Models\Sppd;
use App\Models\SppdFuel;
use App\Models\SppdToll;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;

class SppdDummySeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    private const TUJUAN = [
        'Kunjungan site PLTU dan koordinasi dengan owner.',
        'Pengiriman dokumen kontrak ke Jakarta.',
        'Rapat koordinasi dengan vendor alat berat.',
        'Inspeksi lokasi tambang batubara.',
        'Pendampingan audit K3LL.',
        'Survey rute distribusi BBM.',
    ];

    private const TOL_GERBANG = [
        ['Cikupa', 'Cikampek', 16500],
        ['Cikampek', 'Palimanan', 45200],
        ['Palimanan', 'Kanci', 28100],
        ['Japek', 'Cikampek', 18900],
        ['Cileunyi', 'Padalarang', 12400],
        ['Ngawi', 'Wilangan', 15800],
    ];

    public function run(): void
    {
        $drivers = $this->requireDrivers();
        $kendaraans = $this->requireKendaraans();
        $managerId = $this->requireManagerId();
        $adminId = $this->requireAdminId();

        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();
        $created = 0;

        $monthCursor = $start->copy()->startOfMonth();
        while ($monthCursor->lte($end)) {
            $count = random_int(8, 18);

            for ($i = 0; $i < $count; $i++) {
                $tanggalDinas = $this->randomDateBetween(
                    $monthCursor->copy()->startOfMonth(),
                    $monthCursor->copy()->endOfMonth()->min($end)
                );

                $driver = $drivers->random();
                $k = $kendaraans->random();
                $status = $this->pickSppdStatus();
                $createdAt = $tanggalDinas->copy()->subDays(random_int(1, 8))->setTime(random_int(7, 18), random_int(0, 59));

                [$totalTol, $totalBbm, $grandTotal, $tolls, $fuels] = $this->buildFinancials();

                $sppd = Sppd::create([
                    'user_id' => $driver->id,
                    'nama_driver' => $driver->name,
                    'keperluan_dinas' => fake()->randomElement([
                        'Pengiriman sparepart', 'Rapat koordinasi', 'Inspeksi lapangan', 'Pendampingan tamu perusahaan',
                    ]),
                    'no_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'tanggal_dinas' => $tanggalDinas->toDateString(),
                    'tujuan' => fake()->randomElement(self::TUJUAN),
                    'total_tol' => $totalTol,
                    'total_bbm' => $totalBbm,
                    'grand_total' => $grandTotal,
                    'status' => $status,
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
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $sort = 0;
                foreach ($tolls as $t) {
                    SppdToll::create([
                        'sppd_id' => $sppd->id,
                        'leg' => $t['leg'],
                        'dari_tol' => $t['dari_tol'],
                        'ke_tol' => $t['ke_tol'],
                        'harga' => $t['harga'],
                        'sort_order' => $sort++,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                $fuelSort = 0;
                foreach ($fuels as $f) {
                    SppdFuel::create([
                        'sppd_id' => $sppd->id,
                        'liter' => $f['liter'],
                        'harga_per_liter' => $f['harga_per_liter'],
                        'total' => $f['total'],
                        'sort_order' => $fuelSort++,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                $this->applyStatusMetadata($sppd, $status, $managerId, $adminId, $createdAt);
                $created++;
            }

            $monthCursor->addMonth();
        }

        $this->command?->info("Berhasil menyimpan {$created} SPPD dummy beserta rincian tol & BBM.");
    }

    private function pickSppdStatus(): string
    {
        return $this->weightedPick(
            [
                Sppd::STATUS_PENDING,
                Sppd::STATUS_REVISION,
                Sppd::STATUS_PENDING_MANAGER,
                Sppd::STATUS_APPROVED,
                Sppd::STATUS_REJECTED,
                Sppd::STATUS_COMPLETED,
            ],
            [10, 6, 8, 22, 14, 40]
        );
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: list<array{leg: string, dari_tol: string, ke_tol: string, harga: float}>, 4: list<array{liter: float, harga_per_liter: float, total: float}>}
     */
    private function buildFinancials(): array
    {
        $tolls = [];
        $legs = fake()->boolean(70) ? ['berangkat'] : ['berangkat', 'kembali'];

        foreach ($legs as $leg) {
            $segments = fake()->numberBetween(1, 3);
            for ($s = 0; $s < $segments; $s++) {
                $g = fake()->randomElement(self::TOL_GERBANG);
                $tolls[] = [
                    'leg' => $leg,
                    'dari_tol' => $g[0],
                    'ke_tol' => $g[1],
                    'harga' => (float) $g[2] + fake()->numberBetween(-500, 1500),
                ];
            }
        }

        $fuels = [];
        $fuelRows = fake()->numberBetween(1, 3);
        for ($f = 0; $f < $fuelRows; $f++) {
            $liter = round(fake()->randomFloat(2, 15, 85), 2);
            $hpl = round(fake()->randomFloat(2, 10500, 15800), 2);
            $fuels[] = [
                'liter' => $liter,
                'harga_per_liter' => $hpl,
                'total' => round($liter * $hpl, 2),
            ];
        }

        $totalTol = array_sum(array_column($tolls, 'harga'));
        $totalBbm = array_sum(array_column($fuels, 'total'));

        return [$totalTol, $totalBbm, $totalTol + $totalBbm, $tolls, $fuels];
    }

    private function applyStatusMetadata(Sppd $sppd, string $status, int $managerId, int $adminId, Carbon $createdAt): void
    {
        $updates = [];

        if (in_array($status, [
            Sppd::STATUS_PENDING_MANAGER,
            Sppd::STATUS_APPROVED,
            Sppd::STATUS_REJECTED,
            Sppd::STATUS_COMPLETED,
        ], true)) {
            $updates['admin_verified_by'] = $adminId;
            $updates['admin_verified_at'] = $createdAt->copy()->addHours(random_int(4, 36));
        }

        if (in_array($status, [Sppd::STATUS_APPROVED, Sppd::STATUS_COMPLETED], true)) {
            $updates['approved_by'] = $managerId;
            $updates['approved_at'] = ($updates['admin_verified_at'] ?? $createdAt)->copy()->addHours(random_int(8, 72));
            $updates['pdf_path'] = 'sppd/pdf/seed-'.$sppd->id.'.pdf';
        }

        if ($status === Sppd::STATUS_COMPLETED) {
            $updates['updated_at'] = ($updates['approved_at'] ?? $createdAt)->copy()->addDays(random_int(1, 5));
        }

        if ($status === Sppd::STATUS_REJECTED) {
            $updates['rejected_by'] = $managerId;
            $updates['rejected_at'] = $createdAt->copy()->addHours(random_int(12, 96));
            $updates['rejection_note'] = fake()->randomElement([
                'Dokumen pendukung tidak lengkap.',
                'Tujuan dinas perlu konfirmasi atasan langsung.',
                'Biaya melebihi plafon tanpa persetujuan tertulis.',
            ]);
        }

        if ($status === Sppd::STATUS_REVISION) {
            $updates['revision_note'] = fake()->randomElement([
                'Lampirkan foto struk BBM yang lebih jelas.',
                'Perbaiki rute tol pada leg kembali.',
                'Isi liter BBM sesuai struk terlampir.',
            ]);
            $updates['revision_at'] = $createdAt->copy()->addHours(random_int(6, 48));
        }

        if ($updates !== []) {
            $sppd->update($updates);
        }
    }
}
