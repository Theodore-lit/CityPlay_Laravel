<?php

namespace App\Support;

class StorageUrl
{
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return '/' . $normalized;
        }

        return '/storage/' . $normalized;
    }

    /**
     * @param  array<int, string>|null  $paths
     * @return array<int, string>
     */
    public static function urls(?array $paths): array
    {
        return array_values(array_filter(array_map([self::class, 'url'], $paths ?? [])));
    }

    /** Chemin relatif pour Storage::disk('public') */
    public static function diskPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return ltrim($normalized, '/') ?: null;
    }

    /**
     * @param  array<int, string>|null  $paths
     * @return array<int, string>
     */
    public static function diskPaths(?array $paths): array
    {
        return array_values(array_filter(array_map([self::class, 'diskPath'], $paths ?? [])));
    }
}
