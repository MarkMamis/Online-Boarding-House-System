<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Central file storage service.
 *
 * New uploads go to the configured Cloudflare R2 disk first. If R2 is not
 * configured in an environment, the existing Supabase-then-public development
 * behavior remains available. Reads check R2, Supabase, and then the legacy
 * public disk so existing records do not need a path migration.
 */
class FileStorageService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = $this->resolveActiveDisk();
    }

    /**
     * The disk new uploads are written to.
     */
    public function disk(): string
    {
        return $this->disk;
    }

    /**
     * Determine whether the R2 disk has every value required for a real write.
     *
     * This deliberately reads the resolved disk configuration rather than
     * checking a single environment variable. It returns only a boolean, so
     * credentials never need to be exposed to callers or logs.
     */
    public function isR2Configured(): bool
    {
        return $this->hasConfiguredDiskValues('r2', ['key', 'secret', 'bucket', 'endpoint']);
    }

    /**
     * Upload an uploaded file into a logical directory.
     * Returns the normalized object path (e.g. landlords/25/business-permits/abc123.pdf).
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $name = Str::random(40) . '.' . $this->guessExtension($file);
        $path = $directory . '/' . $name;

        try {
            $stored = Storage::disk($this->disk)->putFileAs($directory, $file, $name);
        } catch (Throwable $e) {
            $stored = false;
            Log::error('FileStorageService upload threw an exception.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'error' => $this->safeErrorMessage($e),
            ]);
        }

        if ($stored === false) {
            Log::error('FileStorageService upload failed.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'name' => $name,
            ]);
            throw new \RuntimeException('File upload failed. Please try again.');
        }

        return $this->normalize($path);
    }

    /**
     * Store raw binary contents (e.g. base64 signatures or generated images).
     */
    public function put(string $directory, string $contents, string $extension = 'png'): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $name = Str::random(40) . '.' . ltrim(strtolower($extension), '.');
        $path = $directory . '/' . $name;

        try {
            $ok = Storage::disk($this->disk)->put($path, $contents);
        } catch (Throwable $e) {
            $ok = false;
            Log::error('FileStorageService put threw an exception.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'error' => $this->safeErrorMessage($e),
            ]);
        }

        if (!$ok) {
            Log::error('FileStorageService put failed.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'name' => $name,
            ]);
            throw new \RuntimeException('File upload failed. Please try again.');
        }

        return $this->normalize($path);
    }

    /**
     * Upload the new file first, then delete the old object after the new one is stored.
     */
    public function replace(?string $oldPath, UploadedFile $newFile, string $directory): string
    {
        $newPath = $this->upload($newFile, $directory);

        $oldPath = $this->normalize((string) $oldPath);
        if ($oldPath !== '' && $oldPath !== $newPath) {
            $this->delete($oldPath);
        }

        return $newPath;
    }

    /**
     * Delete an object from whichever disk holds it. Safe to call with null/empty.
     */
    public function delete(?string $path): bool
    {
        $path = $this->normalize((string) $path);
        if ($path === '') {
            return false;
        }

        // During the storage transition, the same provider-agnostic key may
        // temporarily exist in more than one provider. Delete only the first
        // disk that owns the key, matching the read resolution order.
        $disk = $this->holdingDisk($path);
        if ($disk === null) {
            return false;
        }

        try {
            return (bool) Storage::disk($disk)->delete($path);
        } catch (Throwable $e) {
            Log::warning('FileStorageService delete failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $this->safeErrorMessage($e),
            ]);

            return false;
        }
    }

    /**
     * Check existence on R2 first, then Supabase, then the legacy public disk.
     */
    public function exists(?string $path): bool
    {
        $path = $this->normalize((string) $path);
        if ($path === '') {
            return false;
        }

        foreach ($this->candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return true;
                }
            } catch (Throwable $e) {
                // ignore per-disk errors
            }
        }

        return false;
    }

    /**
     * File size in bytes from whichever disk holds the object.
     */
    public function size(?string $path): int
    {
        $path = $this->normalize((string) $path);
        if ($path === '') {
            return 0;
        }

        foreach ($this->candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return (int) Storage::disk($disk)->size($path);
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        return 0;
    }

    /**
     * Short-lived temporary URL for private files (S3 presigned).
     * Falls back to the legacy public storage URL when the file lives locally.
     */
    public function temporaryUrl(string $path, int $minutes = 10): ?string
    {
        $path = $this->normalize($path);
        if ($path === '') {
            return null;
        }

        foreach ($this->candidateDisks() as $disk) {
            try {
                if (!Storage::disk($disk)->exists($path)) {
                    continue;
                }

                if ($disk === 'public') {
                    return asset('storage/' . $path);
                }

                // R2 and Supabase are private S3-compatible disks. This is
                // available for callers that explicitly need a short-lived
                // presigned URL; private document views continue to use the
                // authorized Laravel /files route.
                return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes($minutes));
            } catch (Throwable $e) {
                Log::warning('FileStorageService temporaryUrl failed.', [
                    'disk' => $disk,
                    'path' => $path,
                    'error' => $this->safeErrorMessage($e),
                ]);
            }
        }

        return null;
    }

    /**
     * Convenience display URL used by views (longer TTL than downloads).
     */
    public function url(string $path, int $minutes = 60): string
    {
        return (string) $this->temporaryUrl($path, $minutes);
    }

    /**
     * Stream / download a file with the correct headers from whichever disk holds it.
     */
    public function response(string $path, string $filename, bool $inline = true)
    {
        $path = $this->normalize($path);
        $disk = $this->holdingDisk($path);

        if ($disk === null) {
            abort(404, 'File not found');
        }

        $disposition = $inline ? 'inline' : 'attachment';

        return Storage::disk($disk)->response($path, $filename, [], $disposition);
    }

    /**
     * Raw contents from whichever disk holds the object (used by the PDF generator).
     */
    public function get(?string $path): ?string
    {
        $path = $this->normalize((string) $path);
        if ($path === '') {
            return null;
        }

        foreach ($this->candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->get($path);
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        return null;
    }

    /**
     * Copy an existing stored object to a new directory (used for landlord signatures).
     */
    public function copyToDirectory(?string $sourcePath, string $directory, string $prefix = 'signature'): ?string
    {
        $sourcePath = $this->normalize((string) $sourcePath);
        if ($sourcePath === '' || !$this->exists($sourcePath)) {
            return null;
        }

        $contents = $this->get($sourcePath);
        if ($contents === null) {
            return null;
        }

        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            $extension = 'png';
        }

        $directory = trim(str_replace('\\', '/', $directory), '/');
        $name = $prefix . '-' . Str::random(32) . '.' . $extension;

        try {
            $ok = Storage::disk($this->disk)->put($directory . '/' . $name, $contents);
        } catch (Throwable $e) {
            $ok = false;
            Log::error('FileStorageService copyToDirectory threw an exception.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'error' => $this->safeErrorMessage($e),
            ]);
        }

        if (!$ok) {
            Log::error('FileStorageService copyToDirectory failed.', [
                'disk' => $this->disk,
                'directory' => $directory,
                'source' => $sourcePath,
            ]);
            return null;
        }

        return $this->normalize($directory . '/' . $name);
    }

    /**
     * Normalize a stored path: forward slashes, no leading/trailing slashes.
     */
    public function normalize(?string $path): string
    {
        return trim(str_replace('\\', '/', (string) $path), '/');
    }

    /**
     * Disks to check when reading current and legacy files.
     *
     * R2 and Supabase are included only when their minimum connection values
     * are present. This preserves fast, predictable local fallback behavior in
     * environments that do not configure either cloud provider.
     */
    protected function candidateDisks(): array
    {
        $disks = [];

        if ($this->isR2Configured()) {
            $disks[] = 'r2';
        }

        if ($this->isSupabaseConfigured()) {
            $disks[] = 'supabase';
        }

        $disks[] = 'public';

        return $disks;
    }

    /**
     * The disk that actually holds a path (or null).
     */
    public function holdingDisk(?string $path): ?string
    {
        $path = $this->normalize((string) $path);
        if ($path === '') {
            return null;
        }

        foreach ($this->candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        return null;
    }

    /**
     * Resolve the disk for new writes.
     *
     * R2 is intentionally selected only when its complete S3-compatible
     * configuration is present. A configured R2 disk is never silently
     * replaced with another provider after a write failure.
     */
    protected function resolveActiveDisk(): string
    {
        if ($this->isR2Configured()) {
            return 'r2';
        }

        if ($this->isSupabaseConfigured()) {
            return 'supabase';
        }

        return 'public';
    }

    /**
     * Check a disk's resolved configuration without returning any values.
     */
    protected function hasConfiguredDiskValues(string $disk, array $keys): bool
    {
        $config = (array) config('filesystems.disks.' . $disk, []);

        foreach ($keys as $key) {
            if (trim((string) ($config[$key] ?? '')) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Keep the legacy Supabase activation rule for backward compatibility.
     */
    protected function isSupabaseConfigured(): bool
    {
        return $this->hasConfiguredDiskValues('supabase', ['endpoint', 'key']);
    }

    /**
     * Redact configured connection values before writing an error to logs.
     */
    protected function safeErrorMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        foreach (['r2', 'supabase'] as $disk) {
            $config = (array) config('filesystems.disks.' . $disk, []);

            foreach (['key', 'secret', 'endpoint'] as $key) {
                $value = trim((string) ($config[$key] ?? ''));
                if ($value !== '') {
                    $message = str_replace($value, '[redacted]', $message);
                }
            }
        }

        return $message;
    }

    protected function guessExtension(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== '') {
            return $extension;
        }

        return strtolower((string) $file->guessExtension()) ?: 'bin';
    }
}
