<?php

namespace App\Console\Commands;

use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\TenantOnboarding;
use App\Models\TenantPayment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Copies legacy local/public storage files to the Supabase S3 disk.
 *
 * - Keeps the same logical paths where possible.
 * - Skips files that already exist remotely.
 * - Does NOT delete local source files by default (use --delete-source).
 * - Logs missing local files.
 * - Idempotent. Safe to run multiple times.
 */
class MigrateStorageToSupabase extends Command
{
    protected $signature = 'storage:migrate-to-supabase
        {--dry-run : Report what would be copied without copying anything}
        {--delete-source : Delete the local source file after a successful copy}';

    protected $description = 'Copy legacy local/public storage files to Supabase (S3) storage';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleteSource = (bool) $this->option('delete-source');

        $sourceDisk = 'public';
        $targetDisk = 'supabase';

        if (!$this->diskConfigured($targetDisk)) {
            $this->error('Supabase storage is not configured. Set SUPABASE_STORAGE_KEY and SUPABASE_STORAGE_ENDPOINT first.');
            return self::FAILURE;
        }

        $paths = $this->collectStoredPaths();

        $total = count($paths);
        $copied = 0;
        $skippedRemote = 0;
        $missingLocal = 0;
        $failed = 0;

        $this->info('Found ' . $total . ' stored path(s) in the database.');
        if ($dryRun) {
            $this->warn('DRY RUN - nothing will be copied.');
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($paths as $path) {
            $path = trim((string) $path, '/');
            $bar->advance();

            if ($path === '') {
                continue;
            }

            if (Storage::disk($targetDisk)->exists($path)) {
                $skippedRemote++;
                continue;
            }

            if (!Storage::disk($sourceDisk)->exists($path)) {
                $missingLocal++;
                Log::warning('storage:migrate-to-supabase missing source file.', ['path' => $path]);
                continue;
            }

            $contents = Storage::disk($sourceDisk)->get($path);
            if ($contents === null) {
                $failed++;
                Log::error('storage:migrate-to-supabase could not read source file.', ['path' => $path]);
                continue;
            }

            if ($dryRun) {
                $copied++; // simulated
                continue;
            }

            try {
                $ok = Storage::disk($targetDisk)->put($path, $contents);
            } catch (\Throwable $e) {
                $ok = false;
                Log::error('storage:migrate-to-supabase upload failed.', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }

            if (!$ok) {
                $failed++;
                Log::error('storage:migrate-to-supabase upload failed.', ['path' => $path]);
                continue;
            }

            $copied++;

            if ($deleteSource) {
                Storage::disk($sourceDisk)->delete($path);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Copied', 'Already on Supabase', 'Missing locally', 'Failed'],
            [[$copied, $skippedRemote, $missingLocal, $failed]]
        );

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Every stored file path referenced by the application.
     */
    protected function collectStoredPaths(): array
    {
        $paths = [];

        foreach (User::query()->whereNotNull('profile_image_path')->cursor() as $user) {
            $paths[] = $user->profile_image_path;
        }
        foreach (User::query()->whereNotNull('school_id_path')->cursor() as $user) {
            $paths[] = $user->school_id_path;
        }
        foreach (User::query()->whereNotNull('enrollment_proof_path')->cursor() as $user) {
            $paths[] = $user->enrollment_proof_path;
        }
        foreach (User::query()->whereNotNull('parent_contact_photo_path')->cursor() as $user) {
            $paths[] = $user->parent_contact_photo_path;
        }

        foreach (LandlordProfile::query()->whereNotNull('business_permit_path')->cursor() as $p) {
            $paths[] = $p->business_permit_path;
        }
        foreach (LandlordProfile::query()->whereNotNull('safety_certificate_path')->cursor() as $p) {
            $paths[] = $p->safety_certificate_path;
        }
        foreach (LandlordProfile::query()->whereNotNull('payment_gcash_qr_path')->cursor() as $p) {
            $paths[] = $p->payment_gcash_qr_path;
        }
        foreach (LandlordProfile::query()->whereNotNull('contract_signature_path')->cursor() as $p) {
            $paths[] = $p->contract_signature_path;
        }

        foreach (Property::query()->whereNotNull('image_path')->cursor() as $p) {
            $paths[] = $p->image_path;
        }

        foreach (Room::query()->whereNotNull('image_path')->cursor() as $r) {
            $paths[] = $r->image_path;
        }
        foreach (RoomImage::query()->whereNotNull('image_path')->cursor() as $img) {
            $paths[] = $img->image_path;
        }

        foreach (TenantOnboarding::query()->cursor() as $o) {
            foreach ((array) ($o->uploaded_documents ?? []) as $doc) {
                $paths[] = $doc;
            }
            if (!empty($o->contract_signature_path)) {
                $paths[] = $o->contract_signature_path;
            }
            if (!empty($o->landlord_contract_signature_path)) {
                $paths[] = $o->landlord_contract_signature_path;
            }
            if (!empty($o->payment_proof_path)) {
                $paths[] = $o->payment_proof_path;
            }
        }

        foreach (TenantPayment::query()->whereNotNull('payment_proof_path')->cursor() as $p) {
            $paths[] = $p->payment_proof_path;
        }

        return array_values(array_unique(array_filter($paths)));
    }

    protected function diskConfigured(string $disk): bool
    {
        $config = config("filesystems.disks.{$disk}", []);

        return !empty($config['endpoint']) && !empty($config['key']);
    }
}
