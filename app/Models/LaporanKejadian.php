<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKejadian extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'bidang_id',
        'waktu_kejadian',
        'kategori',
        'lokasi_kejadian',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'peristiwa',
        'sebelum_kejadian',
        'uraian_kejadian',
        'penjelasan_gambar',
        'foto_path',
        'ttd_manager',
        'ttd_pelapor',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kejadian' => 'datetime',
        ];
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
}
