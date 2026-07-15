<?php

namespace App\Notifications;

use App\Models\PeminjamanRequest;
use Illuminate\Notifications\Notification;

class PeminjamanApprovedNotification extends Notification
{
    public function __construct(
        public PeminjamanRequest $peminjaman
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->peminjaman->loadMissing('approver');

        $approverName = $this->peminjaman->approver?->name
            ?? $this->peminjaman->approver?->username
            ?? 'Manager';

        return [
            'title' => 'Peminjaman kendaraan disetujui',
            'body'  => "{$approverName} menyetujui peminjaman {$this->peminjaman->nomor_kendaraan} atas nama {$this->peminjaman->nama_lengkap}.",
            'url'   => 'admin/peminjaman',
            'peminjaman_id' => $this->peminjaman->id,
        ];
    }
}
