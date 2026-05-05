<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class VehicleUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'kendaraan_id',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'jam_awal',
        'jam_akhir',
        'keperluan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    /** Label durasi antara jam_awal dan jam_akhir (asumsi sama hari). */
    public function durasiDeskripsi(): string
    {
        $awal = $this->getRawOriginal('jam_awal') ?? $this->jam_awal;
        $akhir = $this->getRawOriginal('jam_akhir') ?? $this->jam_akhir;
        $awalStr = is_string($awal) ? $awal : (string) $awal;
        $akhirStr = is_string($akhir) ? $akhir : (string) $akhir;
        $awalStr = substr(trim($awalStr), 0, 8);
        $akhirStr = substr(trim($akhirStr), 0, 8);

        $start = Carbon::parse('2000-01-01 '.$awalStr);
        $end = Carbon::parse('2000-01-01 '.$akhirStr);
        $mins = max(0, (int) round(abs($start->diffInMinutes($end))));
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        if ($h === 0) {
            return $m.' menit';
        }
        if ($m === 0) {
            return $h.' jam';
        }

        return $h.' jam '.$m.' menit';
    }
}