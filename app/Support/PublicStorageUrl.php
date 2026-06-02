<?php

namespace App\Support;

class PublicStorageUrl
{
    public static function resolve(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $baseUrl = url('/');

        if (str_starts_with($path, 'http')) {
            return $path;
        }
        if (str_starts_with($path, '/storage/')) {
            return $baseUrl.$path;
        }
        if (str_starts_with($path, 'storage/')) {
            return $baseUrl.'/'.$path;
        }

        return $baseUrl.'/storage/'.ltrim($path, '/');
    }
}
