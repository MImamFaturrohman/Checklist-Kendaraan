<?php

namespace App\Models;

use App\Support\SppdStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Sppd extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REVISION = 'revision';

    public const STATUS_PENDING_MANAGER = 'pending_manager';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'nama_driver',
        'keperluan_dinas',
        'no_kendaraan',
        'jenis_kendaraan',
        'tanggal_dinas',
        'tujuan',
        'total_tol',
        'total_bbm',
        'grand_total',
        'status',
        'revision_note',
        'revision_at',
        'rejection_note',
        'rejected_at',
        'rejected_by',
        'approved_by',
        'approved_at',
        'signature_path',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dinas' => 'date',
            'total_tol' => 'decimal:2',
            'total_bbm' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'revision_at' => 'datetime',
            'rejected_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function tolls(): HasMany
    {
        return $this->hasMany(SppdToll::class)->orderBy('sort_order');
    }

    public function fuels(): HasMany
    {
        return $this->hasMany(SppdFuel::class)->orderBy('sort_order');
    }

    public function isOwnedBy(?int $userId): bool
    {
        return $userId !== null && (int) $this->user_id === (int) $userId;
    }

    public function canDriverEdit(): bool
    {
        return in_array($this->status, [self::STATUS_REVISION], true);
    }

    public function canDriverDelete(): bool
    {
        return $this->status === self::STATUS_REVISION;
    }

    public function canDriverViewPdf(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_COMPLETED], true)
            && $this->pdf_path;
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailArray(): array
    {
        $disk = fn (?string $p) => $this->resolveMediaUrl($p);

        return [
            'id' => $this->id,
            'nama_driver' => $this->nama_driver,
            'keperluan_dinas' => $this->keperluan_dinas,
            'no_kendaraan' => $this->no_kendaraan,
            'jenis_kendaraan' => $this->jenis_kendaraan,
            'tanggal_dinas' => $this->tanggal_dinas?->format('Y-m-d'),
            'tujuan' => $this->tujuan,
            'total_tol' => (string) $this->total_tol,
            'total_bbm' => (string) $this->total_bbm,
            'grand_total' => (string) $this->grand_total,
            'status' => $this->status,
            'status_label' => SppdStatus::label($this->status),
            'revision_note' => $this->revision_note,
            'revision_at' => $this->revision_at?->toIso8601String(),
            'rejection_note' => $this->rejection_note,
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'approver_name' => $this->approver?->name,
            'driver_username' => $this->user?->username,
            'signature_url' => $disk($this->signature_path),
            'pdf_url' => $this->pdf_path ? $disk($this->pdf_path) : null,
            'tolls' => $this->tolls->map(fn ($t) => [
                'dari_tol' => $t->dari_tol,
                'ke_tol' => $t->ke_tol,
                'harga' => (string) $t->harga,
            ]),
            'fuels' => $this->fuels->map(fn ($f) => [
                'liter' => (string) $f->liter,
                'harga_per_liter' => (string) $f->harga_per_liter,
                'total' => (string) $f->total,
                'odometer_url' => $disk($f->odometer_path),
                'struk_url' => $disk($f->struk_path),
            ]),
        ];
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $raw = trim($path);

        if (preg_match('/^https?:\/\//i', $raw) || str_starts_with($raw, 'data:image')) {
            return $raw;
        }

        $candidates = collect([
            $raw,
            ltrim($raw, '/'),
            preg_replace('#^public/#', '', $raw),
            preg_replace('#^storage/#', '', $raw),
            preg_replace('#^/storage/#', '', $raw),
        ])->filter()->unique()->values();

        foreach ($candidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return asset('storage/'.ltrim($candidate, '/'));
            }
        }

        // Fallback to first candidate even if file existence check fails.
        $first = (string) $candidates->first();
        if ($first !== '') {
            if (str_starts_with($first, 'storage/')) {
                return asset($first);
            }

            return asset('storage/'.ltrim($first, '/'));
        }

        return null;
    }
}
