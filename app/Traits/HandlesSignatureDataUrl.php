<?php

namespace App\Traits;

use App\Services\FileStorageService;

trait HandlesSignatureDataUrl
{
    /**
     * Validate and store a base64 signature data URL.
     *
     * Validates that the payload is well-formed, decodes into a valid
     * image (png, jpeg, webp), enforces a 5MB decoded binary size limit,
     * verifies actual image dimensions/MIME via getimagesizefromstring,
     * and stores via FileStorageService.
     *
     * @param  string  $signatureData  Base64 Data URL (e.g., data:image/png;base64,...)
     * @param  string  $directory  Target directory (e.g., landlords/{id}/signatures)
     * @param  string|null  $oldPath  Previous signature path to delete if replaced
     * @return string|null Relative storage path or null if invalid
     */
    protected function storeSignatureDataUrl(string $signatureData, string $directory, ?string $oldPath = null): ?string
    {
        $signatureData = trim($signatureData);
        if ($signatureData === '') {
            return null;
        }

        if (! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/is', $signatureData, $matches)) {
            return null;
        }

        $declaredMime = strtolower((string) $matches[1]);
        $encoded = (string) $matches[2];
        $binary = base64_decode($encoded, true);

        if ($binary === false || $binary === '') {
            return null;
        }

        // Limit size: 5MB maximum decoded binary
        if (strlen($binary) > (5 * 1024 * 1024)) {
            return null;
        }

        // Verify that the binary content is an actual valid image
        $imageInfo = @getimagesizefromstring($binary);
        if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
            return null;
        }

        // Enforce minimum dimensions so blank single-pixel images are rejected
        if ($imageInfo[0] < 5 || $imageInfo[1] < 5) {
            return null;
        }

        // Verify that the actual image MIME type is one of our allowed types
        $allowedMimes = ['image/png', 'image/jpeg', 'image/webp'];
        $actualMime = isset($imageInfo['mime']) ? strtolower((string) $imageInfo['mime']) : '';
        if (! in_array($actualMime, $allowedMimes, true)) {
            return null;
        }

        $extension = match ($actualMime) {
            'image/jpeg' => 'jpeg',
            'image/webp' => 'webp',
            default => 'png',
        };

        try {
            $path = app(FileStorageService::class)->put(
                $directory,
                $binary,
                $extension
            );

            if (! empty($oldPath)) {
                app(FileStorageService::class)->delete($oldPath);
            }

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
