<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    protected $fillable = [
        'parent_id',
        'nama',
        'pimpinan_nama',
        'pimpinan_email',
        'jabatan',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Bidang::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function peminjamanRequests(): HasMany
    {
        return $this->hasMany(PeminjamanRequest::class, 'bidang_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isLeaf(): bool
    {
        return ! $this->children()->exists();
    }

    public function labelLengkap(): string
    {
        $p = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();
        if ($p) {
            return $p->nama.'—'.$this->nama;
        }

        return $this->nama;
    }

    public function hasPimpinanContact(): bool
    {
        return filled($this->pimpinan_nama) && filled($this->pimpinan_email);
    }

    /** @deprecated Use hasPimpinanContact() */
    public function hasManagerContact(): bool
    {
        return $this->hasPimpinanContact();
    }

    /** @deprecated Always returns false — team leader concept removed */
    public function hasTeamLeaderContact(): bool
    {
        return false;
    }

    /** Kembalikan email penerima persetujuan. */
    public function approvalRecipientEmail(): ?string
    {
        if ($this->hasPimpinanContact()) return $this->pimpinan_email;
        return null;
    }

    /** Kembalikan nama penerima persetujuan. */
    public function approvalRecipientNama(): ?string
    {
        if ($this->hasPimpinanContact()) return $this->pimpinan_nama;
        return null;
    }

    /** True jika ada kontak pimpinan untuk persetujuan. */
    public function hasAnyApprovalContact(): bool
    {
        return $this->hasPimpinanContact();
    }

    /** Hanya sub-bidang (daun) yang boleh dipilih di form peminjaman. */
    public function scopeSelectableForPeminjaman($query)
    {
        return $query->whereNotNull('parent_id')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('bidangs as c')
                    ->whereColumn('c.parent_id', 'bidangs.id');
            });
    }
}
