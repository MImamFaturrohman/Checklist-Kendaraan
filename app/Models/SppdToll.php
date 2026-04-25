<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppdToll extends Model
{
    protected $fillable = [
        'sppd_id',
        'dari_tol',
        'ke_tol',
        'harga',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function sppd(): BelongsTo
    {
        return $this->belongsTo(Sppd::class);
    }
}
