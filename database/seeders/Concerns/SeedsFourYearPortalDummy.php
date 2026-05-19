<?php

namespace Database\Seeders\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait SeedsFourYearPortalDummy
{
    /** Path placeholder — file fisik tidak ada; thumbnail/PDF bisa 404. */
    protected const DUMMY_IMAGE = 'seed/dummy-photo.jpg';

    protected const DUMMY_PDF = 'seed/dummy-document.pdf';

    protected const DUMMY_TTD = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function portalPeriodStart(): Carbon
    {
        return Carbon::now()->copy()->subYears(4)->startOfDay();
    }

    protected function portalPeriodEnd(): Carbon
    {
        return Carbon::now()->endOfDay();
    }

    protected function randomDateBetween(Carbon $start, Carbon $end): Carbon
    {
        $startTs = $start->timestamp;
        $endTs = $end->timestamp;

        if ($endTs <= $startTs) {
            return $start->copy();
        }

        return Carbon::createFromTimestamp(random_int($startTs, $endTs));
    }

    protected function randomDateTimeBetween(Carbon $start, Carbon $end): Carbon
    {
        return $this->randomDateBetween($start, $end)
            ->setTime(random_int(6, 20), random_int(0, 59), 0);
    }

    /**
     * @template T
     *
     * @param  list<T>  $items
     * @return T
     */
    protected function weightedPick(array $items, array $weights): mixed
    {
        $total = array_sum($weights);
        $r = random_int(1, max(1, $total));
        $acc = 0;
        foreach ($items as $i => $item) {
            $acc += $weights[$i];
            if ($r <= $acc) {
                return $item;
            }
        }

        return $items[array_key_last($items)];
    }

    /**
     * @return Collection<int, \App\Models\User>
     */
    protected function requireDrivers(): Collection
    {
        $drivers = \App\Models\User::query()->where('role', 'driver')->get();

        if ($drivers->isEmpty()) {
            $this->command?->error('Tidak ada user driver. Jalankan DatabaseSeeder terlebih dahulu.');

            exit(1);
        }

        return $drivers;
    }

    /**
     * @return Collection<int, \App\Models\Kendaraan>
     */
    protected function requireKendaraans(): Collection
    {
        $kendaraans = \App\Models\Kendaraan::query()->get();

        if ($kendaraans->isEmpty()) {
            $this->command?->error('Tidak ada data kendaraan. Jalankan DatabaseSeeder terlebih dahulu.');

            exit(1);
        }

        return $kendaraans;
    }

    protected function requireManagerId(): int
    {
        $id = \App\Models\User::query()->where('role', 'manager')->value('id');

        if (! $id) {
            $this->command?->error('Tidak ada user manager. Jalankan DatabaseSeeder terlebih dahulu.');

            exit(1);
        }

        return (int) $id;
    }

    protected function requireAdminId(): int
    {
        $id = \App\Models\User::query()->whereIn('role', ['admin', 'superadmin'])->value('id');

        if (! $id) {
            $this->command?->error('Tidak ada user admin. Jalankan DatabaseSeeder terlebih dahulu.');

            exit(1);
        }

        return (int) $id;
    }

    /**
     * @return Collection<int, \App\Models\Bidang>
     */
    protected function requireLeafBidangs(): Collection
    {
        $bidangs = \App\Models\Bidang::query()
            ->selectableForPeminjaman()
            ->get();

        if ($bidangs->isEmpty()) {
            $this->command?->error('Tidak ada bidang daun. Jalankan BidangPernyataanSeeder terlebih dahulu.');

            exit(1);
        }

        return $bidangs;
    }
}
