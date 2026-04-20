<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    protected $fillable = [
        'nomor_kendaraan',
        'jenis_kendaraan',
        'set_km',
    ];

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'nomor_kendaraan', 'nomor_kendaraan');
    }
}
