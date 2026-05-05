<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Kendaraan extends Model
{
    protected $fillable = [
        'nomor_kendaraan',
        'jenis_kendaraan',
        'bidang',
        'set_km',
        'km_current',
        'tanggal_stnk',
        'tanggal_pajak_stnk',
        'tanggal_kir',
        'status_kendaraan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_stnk' => 'date',
            'tanggal_pajak_stnk' => 'date',
            'tanggal_kir' => 'date',
        ];
    }

    /** Masa berlaku: masih di tanggal atau sebelumnya → AKTIF; setelahnya → EXPIRED. */
    public static function expiryStateForDate(CarbonInterface|string|null $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        $expiry = Carbon::parse($date)->startOfDay();

        return Carbon::today()->lte($expiry) ? 'AKTIF' : 'EXPIRED';
    }

    public static function formatArmadaDateId(CarbonInterface|string|null $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Carbon::parse($date)->locale('id')->translatedFormat('j F Y');
    }

    public static function statusPillClass(?string $status): string
    {
        return match ($status) {
            'Maintenance' => 'mgmt-status-pill mgmt-status-maint',
            'Non Aktif' => 'mgmt-status-pill mgmt-status-off',
            default => 'mgmt-status-pill mgmt-status-on',
        };
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'nomor_kendaraan', 'nomor_kendaraan');
    }

    public function bbmReports(): HasMany
    {
        return $this->hasMany(BbmReport::class);
    }

    public function vehicleUsageLogs(): HasMany
    {
        return $this->hasMany(VehicleUsageLog::class);
    }
}
