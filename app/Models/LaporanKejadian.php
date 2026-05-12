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
        'lampiran_gambar',
        'foto_path',
        'ttd_manager',
        'ttd_pelapor',
        'pdf_path',
        'manager_approval_token',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kejadian' => 'datetime',
            'lampiran_gambar' => 'array',
        ];
    }

    /**
     * @return array<int, array{path: string, penjelasan: string}>
     */
    public function lampiranItems(): array
    {
        $raw = $this->lampiran_gambar;
        if (is_array($raw) && $raw !== []) {
            $out = [];
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $path = isset($row['path']) ? (string) $row['path'] : '';
                if ($path === '') {
                    continue;
                }
                $out[] = [
                    'path' => $path,
                    'penjelasan' => isset($row['penjelasan']) ? (string) $row['penjelasan'] : '',
                ];
            }

            return $out;
        }

        if ($this->foto_path) {
            return [[
                'path' => (string) $this->foto_path,
                'penjelasan' => (string) ($this->penjelasan_gambar ?? ''),
            ]];
        }

        return [];
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
}
