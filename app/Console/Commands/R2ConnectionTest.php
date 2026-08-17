<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Safe Cloudflare R2 connectivity test.
 *
 * Uploads a small marker file to test/laravel-r2-connection.txt, verifies it
 * exists, reads it back, then deletes it. Never prints credentials.
 */
class R2ConnectionTest extends Command
{
    protected $signature = 'r2:test';

    protected $description = 'Verify Laravel -> Flysystem -> AWS S3 adapter -> Cloudflare R2 connectivity';

    public function handle(): int
    {
        $disk = 'r2';
        $config = config("filesystems.disks.{$disk}", []);

        $required = [
            'key' => 'R2_ACCESS_KEY_ID',
            'secret' => 'R2_SECRET_ACCESS_KEY',
            'bucket' => 'R2_BUCKET',
            'endpoint' => 'R2_ENDPOINT',
        ];

        $missing = [];
        foreach ($required as $configKey => $envKey) {
            if (empty($config[$configKey])) {
                $missing[] = $envKey;
            }
        }

        if ($missing !== []) {
            $this->error('Cloudflare R2 is not configured yet.');
            $this->newLine();
            $this->line('Add these variables to your local .env file:');
            foreach ($missing as $envKey) {
                $this->line('  ' . $envKey . '=...');
            }
            $this->newLine();
            $this->line('Then clear the config cache and run the test again:');
            $this->line('  php artisan config:clear');
            $this->line('  php artisan r2:test');

            return self::FAILURE;
        }

        $objectKey = 'test/laravel-r2-connection.txt';
        $contents = 'R2 connectivity test - ' . now()->toIso8601String() . ' - ' . Str::random(16);

        try {
            $this->info('1/4 Uploading ' . $objectKey . ' ...');
            if (!Storage::disk($disk)->put($objectKey, $contents)) {
                $this->error('FAILURE: Upload failed (Storage::put returned false).');

                return self::FAILURE;
            }
            $this->info('    Uploaded.');

            $this->info('2/4 Checking existence ...');
            if (!Storage::disk($disk)->exists($objectKey)) {
                $this->error('FAILURE: Object was not found after upload.');

                return self::FAILURE;
            }
            $this->info('    Exists.');

            $this->info('3/4 Reading object back ...');
            $read = Storage::disk($disk)->get($objectKey);
            if ($read !== $contents) {
                $this->error('FAILURE: Read-back contents did not match what was written.');

                return self::FAILURE;
            }
            $this->info('    Matches (' . strlen((string) $read) . ' bytes).');

            $this->info('4/4 Deleting test object ...');
            if (!Storage::disk($disk)->delete($objectKey)) {
                $this->warn('    Warning: delete returned false (object may already be gone).');
            } else {
                $this->info('    Deleted.');
            }
        } catch (Throwable $e) {
            $this->error('FAILURE: Exception while talking to Cloudflare R2.');
            $this->line('  ' . $this->sanitize($e->getMessage()));

            return self::FAILURE;
        } finally {
            // Best-effort cleanup so a failed test never leaves objects behind.
            // Runs on every path, including early returns and exceptions.
            try {
                Storage::disk($disk)->delete($objectKey);
            } catch (Throwable $e) {
                // ignore cleanup errors
            }
        }

        $this->newLine();
        $this->info('SUCCESS: Cloudflare R2 connectivity verified.');
        $this->line('Chain: Laravel Storage -> Flysystem -> AWS S3 adapter -> Cloudflare R2 (bucket "' . $config['bucket'] . '").');

        return self::SUCCESS;
    }

    /**
     * Strip configured secrets out of an exception message so credentials are
     * never printed to the console.
     */
    protected function sanitize(string $message): string
    {
        $secrets = array_values(array_filter([
            (string) config('filesystems.disks.r2.key', ''),
            (string) config('filesystems.disks.r2.secret', ''),
            (string) config('filesystems.disks.r2.endpoint', ''),
        ]));

        return str_replace($secrets, '[redacted]', $message);
    }
}
