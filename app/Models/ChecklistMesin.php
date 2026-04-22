<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistMesin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'mesin', 'mesin_keterangan',
        'oli', 'oli_keterangan',
        'radiator', 'radiator_keterangan',
        'rem', 'rem_keterangan',
        'kopling', 'kopling_keterangan',
        'transmisi', 'transmisi_keterangan',
        'indikator', 'indikator_keterangan',
        'foto_1', 'foto_2', 'foto_3',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
