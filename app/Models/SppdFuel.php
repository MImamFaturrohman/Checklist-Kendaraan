<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppdFuel extends Model
{
    protected $fillable = [
        'sppd_id',
        'liter',
        'harga_per_liter',
        'total',
        'odometer_path',
        'struk_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'liter' => 'decimal:2',
            'harga_per_liter' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sppd(): BelongsTo
    {
        return $this->belongsTo(Sppd::class);
    }
}
