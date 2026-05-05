<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BbmReport extends Model
{
    public const JENIS_PENGISIAN_OPERASIONAL = 'Operasional';

    public const JENIS_PENGISIAN_PERJALANAN_DINAS = 'Perjalanan Dinas (SPPD)';

    /** @var list<string> */
    public const JENIS_PENGISIAN_VALUES = [
        self::JENIS_PENGISIAN_OPERASIONAL,
        self::JENIS_PENGISIAN_PERJALANAN_DINAS,
    ];

    protected $fillable = [
        'user_id',
        'kendaraan_id',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'jenis_pengisian',
        'tanggal',
        'waktu',
        'shift',
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
            'odometer_sebelum' => 'integer',
            'odometer_sesudah' => 'integer',
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
