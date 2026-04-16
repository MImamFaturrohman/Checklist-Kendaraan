<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistInterior extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'jok', 'jok_keterangan',
        'dashboard', 'dashboard_keterangan',
        'ac', 'ac_keterangan',
        'sabuk_pengaman', 'sabuk_pengaman_keterangan',
        'audio', 'audio_keterangan',
        'kebersihan', 'kebersihan_keterangan',
        'catatan',
        'foto_1', 'foto_2', 'foto_3',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
