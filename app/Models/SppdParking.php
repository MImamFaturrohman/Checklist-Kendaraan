<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppdParking extends Model
{
    protected $table = 'sppd_parkings';

    protected $fillable = [
        'sppd_id',
        'lokasi',
        'biaya_parkir',
        'total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'lokasi'       => 'string',
            'biaya_parkir' => 'decimal:2',
            'total'        => 'decimal:2',
        ];
    }

    public function sppd(): BelongsTo
    {
        return $this->belongsTo(Sppd::class);
    }
}
