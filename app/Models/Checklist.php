<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Checklist extends Model
{
    protected $fillable = [
        'tanggal',
        'shift',
        'driver_serah',
        'driver_terima',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'jam_serah_terima',
        'level_bbm',
        'bbm_terakhir',
        'km_awal',
        'km_akhir',
        'foto_bbm_dashboard',
        'catatan_khusus',
        'tanda_tangan_serah',
        'tanda_tangan_terima',
        'pdf_path',
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** True if this checklist can be printed as PDF */
    public function canPrintPdf(): bool
    {
        // Has a driver_terima (normal flow) OR was explicitly marked complete by admin
        return ($this->driver_terima && $this->driver_terima !== '') || $this->status === 'complete';
    }

    /** Helper to parse driver_terima value for UI and PDF */
    public function getPenerimaDetails(): array
    {
        $val = $this->driver_terima;
        if (!$val) {
            return ['nama' => '', 'jabatan' => ''];
        }

        if (str_starts_with($val, 'Koordinator:')) {
            $nama = trim(substr($val, strlen('Koordinator:')));
            return ['nama' => $nama, 'jabatan' => 'Koordinator'];
        }

        return ['nama' => $val, 'jabatan' => 'Driver'];
    }

    public function exterior(): HasOne
    {
        return $this->hasOne(ChecklistExterior::class);
    }

    public function interior(): HasOne
    {
        return $this->hasOne(ChecklistInterior::class);
    }

    public function mesin(): HasOne
    {
        return $this->hasOne(ChecklistMesin::class);
    }

    public function perlengkapan(): HasOne
    {
        return $this->hasOne(ChecklistPerlengkapan::class);
    }
}
