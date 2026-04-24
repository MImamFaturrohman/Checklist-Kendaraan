<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pernyataan extends Model
{
    protected $table = 'pernyataans';

    protected $fillable = [
        'isi_pernyataan',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }
}
