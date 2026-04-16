<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Checklist extends Model
{
    protected $fillable = [
        'tanggal',
        'shift',
        'driver_serah',
        'driver_terima',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'jam_serah_terima',
        'level_bbm',
        'bbm_terakhir',
        'km_awal',
        'km_akhir',
        'foto_bbm_dashboard',
        'catatan_khusus',
        'tanda_tangan_serah',
        'tanda_tangan_terima',
        'pdf_path',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exterior(): HasOne
    {
        return $this->hasOne(ChecklistExterior::class);
    }

    public function interior(): HasOne
    {
        return $this->hasOne(ChecklistInterior::class);
    }

    public function mesin(): HasOne
    {
        return $this->hasOne(ChecklistMesin::class);
    }

    public function perlengkapan(): HasOne
    {
        return $this->hasOne(ChecklistPerlengkapan::class);
    }
}
