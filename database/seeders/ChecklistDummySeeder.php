<?php

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\ChecklistExterior;
use App\Models\ChecklistInterior;
use App\Models\ChecklistMesin;
use App\Models\ChecklistPerlengkapan;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsFourYearPortalDummy;
use Illuminate\Database\Seeder;

class ChecklistDummySeeder extends Seeder
{
    use SeedsFourYearPortalDummy;

    private const EXTERIOR_ITEMS = ['body_kendaraan', 'kaca', 'spion', 'lampu_utama', 'lampu_sein', 'ban', 'velg', 'wiper'];

    private const INTERIOR_ITEMS = ['jok', 'dashboard', 'ac', 'sabuk_pengaman', 'audio', 'kebersihan'];

    private const MESIN_ITEMS = ['mesin', 'oli', 'radiator', 'rem', 'kopling', 'transmisi', 'indikator'];

    private const PERLENGKAPAN_ITEMS = ['stnk', 'kir', 'dongkrak', 'toolkit', 'segitiga', 'apar', 'ban_cadangan'];

    public function run(): void
    {
        $drivers = $this->requireDrivers();
        $kendaraans = $this->requireKendaraans();
        $driverNames = $drivers->pluck('name')->all();

        $start = $this->portalPeriodStart();
        $end = $this->portalPeriodEnd();
        $created = 0;

        $monthCursor = $start->copy()->startOfMonth();
        while ($monthCursor->lte($end)) {
            $reportsThisMonth = random_int(22, 42);

            for ($i = 0; $i < $reportsThisMonth; $i++) {
                $tanggal = $this->randomDateBetween(
                    $monthCursor->copy()->startOfMonth(),
                    $monthCursor->copy()->endOfMonth()->min($end)
                );

                $shift = $this->weightedPick(['Pagi', 'Siang'], [58, 42]);
                $hour = $shift === 'Pagi' ? random_int(7, 11) : random_int(12, 15);
                $jam = sprintf('%02d:%02d:00', $hour, random_int(0, 59));

                $k = $kendaraans->random();
                $kmAwal = fake()->numberBetween(5_000, 420_000);
                $kmAkhir = $kmAwal + fake()->numberBetween(15, 280);

                $driverSerah = fake()->randomElement($driverNames);
                $driverTerima = fake()->randomElement(array_values(array_filter($driverNames, fn ($n) => $n !== $driverSerah)) ?: $driverNames);

                $withPdf = fake()->boolean(72);
                $withFotoBbm = fake()->boolean(65);
                $withCatatan = fake()->boolean(28);

                $createdAt = Carbon::parse($tanggal->toDateString().' '.$jam);

                $checklist = Checklist::create([
                    'tanggal' => $tanggal->toDateString(),
                    'shift' => $shift,
                    'driver_serah' => $driverSerah,
                    'driver_terima' => $driverTerima,
                    'nomor_kendaraan' => $k->nomor_kendaraan,
                    'jenis_kendaraan' => $k->jenis_kendaraan,
                    'jam_serah_terima' => $jam,
                    'level_bbm' => (string) fake()->numberBetween(18, 98),
                    'bbm_terakhir' => fake()->boolean(35)
                        ? $tanggal->copy()->subDays(random_int(1, 14))->format('Y-m-d H:i')
                        : null,
                    'km_awal' => $kmAwal,
                    'km_akhir' => $kmAkhir,
                    'foto_bbm_dashboard' => $withFotoBbm ? 'checklists/seed/'.self::DUMMY_IMAGE : null,
                    'catatan_khusus' => $withCatatan ? fake()->randomElement([
                        'Ban belakang kanan perlu dicek tekanan.',
                        'AC kurang dingin, laporkan ke bengkel.',
                        'Ada gores kecil di pintu penumpang.',
                        'Kaca spion kanan agak goyang.',
                        'Stiker KIR akan habis bulan depan.',
                    ]) : null,
                    'tanda_tangan_serah' => null,
                    'tanda_tangan_terima' => null,
                    'pdf_path' => $withPdf ? 'checklists/pdf/seed-dummy-'.$created.'.pdf' : null,
                    'user_id' => (int) $drivers->random()->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                ChecklistExterior::create($this->buildExteriorPayload($checklist->id));
                ChecklistInterior::create($this->buildInteriorPayload($checklist->id));
                ChecklistMesin::create($this->buildMesinPayload($checklist->id));
                ChecklistPerlengkapan::create($this->buildPerlengkapanPayload($checklist->id));

                $created++;
            }

            $monthCursor->addMonth();
        }

        $this->command?->info("Berhasil menyimpan {$created} checklist dummy ({$start->format('M Y')} – {$end->format('M Y')}).");
    }

    /**
     * @return array<string, mixed>
     */
    private function buildExteriorPayload(int $checklistId): array
    {
        $data = ['checklist_id' => $checklistId];
        $issueCount = $this->weightedPick([0, 1, 2, 3], [62, 22, 11, 5]);
        $issueFields = fake()->randomElements(self::EXTERIOR_ITEMS, min($issueCount, count(self::EXTERIOR_ITEMS)));

        foreach (self::EXTERIOR_ITEMS as $item) {
            $isIssue = in_array($item, $issueFields, true);
            $data[$item] = $isIssue ? 'no' : 'ok';
            $data["{$item}_keterangan"] = $isIssue ? fake()->randomElement([
                'Ada lecet ringan',
                'Perlu pengecekan ulang',
                'Kotor / berdebu',
                'Tidak berfungsi optimal',
            ]) : null;
        }

        if (fake()->boolean(55)) {
            foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side) {
                if (fake()->boolean(88)) {
                    $data["foto_{$side}"] = "checklists/exterior/seed-{$side}.jpg";
                }
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInteriorPayload(int $checklistId): array
    {
        $data = ['checklist_id' => $checklistId];
        $issueCount = $this->weightedPick([0, 1, 2], [70, 22, 8]);
        $issueFields = fake()->randomElements(self::INTERIOR_ITEMS, min($issueCount, count(self::INTERIOR_ITEMS)));

        foreach (self::INTERIOR_ITEMS as $item) {
            $isIssue = in_array($item, $issueFields, true);
            $data[$item] = $isIssue ? 'no' : 'ok';
            $data["{$item}_keterangan"] = $isIssue ? fake()->sentence(4) : null;
        }

        for ($i = 1; $i <= 3; $i++) {
            if (fake()->boolean(45)) {
                $data["foto_{$i}"] = "checklists/interior/seed-{$i}.jpg";
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMesinPayload(int $checklistId): array
    {
        $data = ['checklist_id' => $checklistId];
        $issueCount = $this->weightedPick([0, 1, 2], [75, 18, 7]);
        $issueFields = fake()->randomElements(self::MESIN_ITEMS, min($issueCount, count(self::MESIN_ITEMS)));

        foreach (self::MESIN_ITEMS as $item) {
            $isIssue = in_array($item, $issueFields, true);
            $data[$item] = $isIssue ? 'no' : 'ok';
            $data["{$item}_keterangan"] = $isIssue ? fake()->randomElement([
                'Oli mendekati batas minimum',
                'Rem terasa agak keras',
                'Indikator check engine menyala sebentar',
            ]) : null;
        }

        for ($i = 1; $i <= 3; $i++) {
            if (fake()->boolean(35)) {
                $data["foto_{$i}"] = "checklists/mesin/seed-{$i}.jpg";
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPerlengkapanPayload(int $checklistId): array
    {
        $data = ['checklist_id' => $checklistId];

        foreach (self::PERLENGKAPAN_ITEMS as $item) {
            if ($item === 'kir') {
                $data[$item] = fake()->boolean(55) ? 'ada' : 'tidak_ada';
            } else {
                $data[$item] = fake()->boolean(92) ? 'ada' : 'tidak_ada';
            }
        }

        return $data;
    }
}
