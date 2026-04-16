<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistExterior extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'body_kendaraan', 'body_kendaraan_keterangan',
        'kaca', 'kaca_keterangan',
        'spion', 'spion_keterangan',
        'lampu_utama', 'lampu_utama_keterangan',
        'lampu_sein', 'lampu_sein_keterangan',
        'ban', 'ban_keterangan',
        'velg', 'velg_keterangan',
        'wiper', 'wiper_keterangan',
        'catatan',
        'foto_depan', 'foto_kanan', 'foto_kiri', 'foto_belakang',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
