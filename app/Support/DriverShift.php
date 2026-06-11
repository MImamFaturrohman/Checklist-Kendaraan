<?php

namespace App\Support;

/**
 * Aturan shift mengikuti dashboard driver (resources/views/dashboard.blade.php).
 */
final class DriverShift
{
    public const CODES = ['pagi', 'siang', 'luar'];

    /**
     * @return array{code: string, label: string, badge_class: string, icon_class: string}
     */
    public static function fromHour(int $hour): array
    {
        if ($hour >= 7 && $hour < 12) {
            return [
                'code' => 'pagi',
                'label' => 'Shift Pagi',
                'badge_class' => 'bbm-shift-pagi',
                'icon_class' => 'bi bi-sunrise-fill',
            ];
        }

        if ($hour >= 12 && $hour < 16) {
            return [
                'code' => 'siang',
                'label' => 'Shift Siang',
                'badge_class' => 'bbm-shift-siang',
                'icon_class' => 'bi bi-sun-fill',
            ];
        }

        return [
            'code' => 'luar',
            'label' => 'Di Luar Shift',
            'badge_class' => 'bbm-shift-luar',
            'icon_class' => 'bi bi-moon-fill',
        ];
    }

    public static function iconClassFromCode(?string $code): string
    {
        return self::fromCode($code)['icon_class'];
    }

    /**
     * @return array{code: string, label: string, badge_class: string, icon_class: string}
     */
    public static function current(): array
    {
        $hour = (int) now(config('app.timezone'))->format('G');

        return self::fromHour($hour);
    }

    public static function labelFromCode(?string $code): string
    {
        return self::fromCode($code)['label'];
    }

    /** Label pendek untuk tabel portal admin (tanpa kata "Shift" di pagi/siang). */
    public static function tableLabelFromCode(?string $code): string
    {
        return match ($code) {
            'pagi' => 'Pagi',
            'siang' => 'Siang',
            default => self::fromCode($code)['label'],
        };
    }

    public static function badgeClassFromCode(?string $code): string
    {
        return self::fromCode($code)['badge_class'];
    }

    /**
     * @return array{code: string, label: string, badge_class: string, icon_class: string}
     */
    public static function fromCode(?string $code): array
    {
        return match ($code) {
            'pagi' => self::fromHour(8),
            'siang' => self::fromHour(13),
            default => self::fromHour(18),
        };
    }
}
