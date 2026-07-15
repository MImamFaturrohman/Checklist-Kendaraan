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

        // Jika ada http/https (misalnya sisa data lama), ambil bagian path + query + fragment
        if (str_starts_with($base, 'http://') || str_starts_with($base, 'https://')) {
            $parsed = parse_url($base);
            $path = $parsed['path'] ?? '';
            $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
            $base = '/'.trim($path, '/').$query.$fragment;
        }

        // Bersihkan subpath lama (misalnya /vms/public/admin/... menjadi /admin/...)
        // agar tidak terjadi double subpath saat dilewatkan ke helper url()
        $adminPos = strpos($base, '/admin/');
        if ($adminPos !== false) {
            $base = substr($base, $adminPos);
        } elseif (str_starts_with($base, 'admin/')) {
            $base = '/' . $base;
        }

        $path = parse_url($base, PHP_URL_PATH);
        $query = parse_url($base, PHP_URL_QUERY);
        $fragment = parse_url($base, PHP_URL_FRAGMENT);

        if (! is_string($path) || $path === '') {
            return url($base);
        }

        $pathNorm = '/'.trim($path, '/');

        if (!$fragment) {
            $fragment = match ($pathNorm) {
                '/admin/portal-pemeriksaan' => 'section-db',
                '/admin/portal-bbm-operasional' => 'section-bbm-table',
                default => null,
            };
        }

        $fullPath = $pathNorm . ($query ? '?'.$query : '');
        $resolvedUrl = url($fullPath);

        return $fragment ? $resolvedUrl.'#'.$fragment : $resolvedUrl;
    }
}
