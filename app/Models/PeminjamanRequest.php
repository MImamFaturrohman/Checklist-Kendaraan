<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanRequest extends Model
{
    protected $fillable = [
        'nama_lengkap',
        'nip',
        'jabatan',
        'bidang_id',
        'nomor_kendaraan',
        'jenis_kendaraan',
        'tanggal_peminjaman',
        'alasan',
        'tanda_tangan',
        'pdf_path',
        'status',
        'catatan_manager',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Mark all pending requests whose tanggal_peminjaman has already passed
     * as expired, and clear their signature data.
     */
    public static function expirePendingPastBorrowDate(): int
    {
        return static::where('status', 'pending')
            ->whereDate('tanggal_peminjaman', '<', now()->toDateString())
            ->update([
                'status'        => 'expired',
                'tanda_tangan'  => null,
            ]);
    }
}
