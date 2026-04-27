<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BbmReport extends Model
{
    protected $fillable = [
        'user_id',
        'kendaraan_id',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'tanggal',
        'waktu',
        'odometer_sebelum',
        'odometer_sesudah',
        'liter',
        'harga_per_liter',
        'total_harga',
        'odometer_photo_path',
        'struk_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'odometer_sebelum' => 'decimal:2',
            'odometer_sesudah' => 'decimal:2',
            'liter' => 'decimal:3',
            'harga_per_liter' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }
}
