<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistPerlengkapan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'stnk', 'kir', 'dongkrak', 'toolkit',
        'segitiga', 'apar', 'ban_cadangan',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
