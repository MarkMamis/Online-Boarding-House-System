<?php

use App\Services\FileStorageService;

if (!function_exists('file_storage')) {
    /**
     * Resolve the shared file storage service.
     */
    function file_storage(): FileStorageService
    {
        return app(FileStorageService::class);
    }
}

if (!function_exists('file_url')) {
    /**
     * Display URL for a stored path (temporary URL for Supabase files,
     * legacy public URL for local files). Returns '' when the file is missing.
     */
    function file_url(?string $path, int $minutes = 60): string
    {
        if (empty($path)) {
            return '';
        }

        return (string) file_storage()->url((string) $path, $minutes);
    }
}

if (!function_exists('file_exists_any')) {
    /**
     * True when the stored path exists on Supabase or the legacy public disk.
     */
    function file_exists_any(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return file_storage()->exists((string) $path);
    }
}

if (!function_exists('file_size_any')) {
    /**
     * Byte size of a stored path from whichever disk holds it (0 when missing).
     */
    function file_size_any(?string $path): int
    {
        if (empty($path)) {
            return 0;
        }

        return file_storage()->size((string) $path);
    }
}

if (!function_exists('file_download_url')) {
    /**
     * Authorized route for a stored path (secure streaming through Laravel).
     * Renders inline by default; pass $download = true for an attachment.
     */
    function file_download_url(?string $path, bool $download = false): string
    {
        if (empty($path)) {
            return '#';
        }

        return route('files.show', [
            'path' => ltrim(str_replace('\\', '/', (string) $path), '/'),
            'download' => $download ? 1 : 0,
        ]);
    }
}
