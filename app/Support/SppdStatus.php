<?php

namespace App\Support;

use App\Models\Sppd;

final class SppdStatus
{
    /**
     * @return array{value: string, label: string, badge: string}
     */
    public static function meta(?string $status): array
    {
        return match ($status) {
            Sppd::STATUS_PENDING => [
                'value' => Sppd::STATUS_PENDING,
                'label' => 'Menunggu Verifikasi',
                'badge' => 'sppd-badge sppd-badge--pending',
            ],
            Sppd::STATUS_REVISION => [
                'value' => Sppd::STATUS_REVISION,
                'label' => 'Revisi Admin',
                'badge' => 'sppd-badge sppd-badge--revision',
            ],
            Sppd::STATUS_PENDING_MANAGER => [
                'value' => Sppd::STATUS_PENDING_MANAGER,
                'label' => 'Menunggu Persetujuan',
                'badge' => 'sppd-badge sppd-badge--pending-manager',
            ],
            Sppd::STATUS_APPROVED => [
                'value' => Sppd::STATUS_APPROVED,
                'label' => 'Disetujui',
                'badge' => 'sppd-badge sppd-badge--approved',
            ],
            Sppd::STATUS_REJECTED => [
                'value' => Sppd::STATUS_REJECTED,
                'label' => 'Ditolak',
                'badge' => 'sppd-badge sppd-badge--rejected',
            ],
            Sppd::STATUS_COMPLETED => [
                'value' => Sppd::STATUS_COMPLETED,
                'label' => 'Selesai',
                'badge' => 'sppd-badge sppd-badge--completed',
            ],
            default => [
                'value' => (string) $status,
                'label' => $status ?? '-',
                'badge' => 'sppd-badge sppd-badge--muted',
            ],
        };
    }

    public static function label(?string $status): string
    {
        return self::meta($status)['label'];
    }

    /**
     * @return list<string>
     */
    public static function adminFilterOptions(): array
    {
        return [
            Sppd::STATUS_PENDING,
            Sppd::STATUS_REVISION,
            Sppd::STATUS_PENDING_MANAGER,
            Sppd::STATUS_APPROVED,
            Sppd::STATUS_REJECTED,
            Sppd::STATUS_COMPLETED,
        ];
    }
}
