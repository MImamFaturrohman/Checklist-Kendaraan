<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalize notification URLs so portal links scroll to table sections when appropriate.
 */
final class SuperadminNotificationLink
{
    /**
     * Append hash fragment when URL targets main portal indexes and has no fragment yet.
     */
    public static function href(?string $url): string
    {
        $base = trim((string) $url);

        if ($base === '' || $base === '#') {
            return '#';
        }

        if (str_contains($base, '#')) {
            return $base;
        }

        $path = parse_url($base, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return $base;
        }

        $pathNorm = '/'.trim($path, '/');

        $fragment = match ($pathNorm) {
            '/admin/portal-pemeriksaan' => 'section-db',
            '/admin/portal-bbm-operasional' => 'section-bbm-table',
            default => null,
        };

        return $fragment ? $base.'#'.$fragment : $base;
    }
}
