<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DeploymentCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obhs:deployment-check {--production : Enforce strict production requirements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preflight deployment diagnostics for local development and Hostinger production';

    protected int $passCount = 0;
    protected int $warnCount = 0;
    protected int $failCount = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isProductionCheck = $this->option('production') || app()->environment('production');

        $this->line('');
        $this->line('<fg=cyan>====================================================================</>');
        $this->line($isProductionCheck
            ? '<fg=yellow>  OBHS PRODUCTION READINESS CHECK (Hostinger Shared Hosting)</>'
            : '<fg=green>  OBHS ENVIRONMENT PREFLIGHT CHECK (Local Development)</>');
        $this->line('<fg=cyan>====================================================================</>');
        $this->line('');

        // 1. Framework & Core Configuration
        $this->checkEnvironmentConfig($isProductionCheck);

        // 2. PHP Version & Required Extensions
        $this->checkPhpPlatform();

        // 3. Database Connectivity & Schema
        $this->checkDatabase();

        // 4. Storage & File Permissions
        $this->checkStoragePermissions($isProductionCheck);

        // 5. Routing & .htaccess Files
        $this->checkRoutingArtifacts();

        // 6. Frontend Production Build
        $this->checkFrontendBuild($isProductionCheck);

        // 7. Services & Integrations (R2, Mail, Hugging Face)
        $this->checkServices();

        // Summary
        $this->line('');
        $this->line('<fg=cyan>--------------------------------------------------------------------</>');
        $this->line(sprintf(
            'Results: <fg=green>%d PASS</>, <fg=yellow>%d WARN</>, <fg=red>%d FAIL</>',
            $this->passCount,
            $this->warnCount,
            $this->failCount
        ));
        $this->line('<fg=cyan>--------------------------------------------------------------------</>');

        if ($this->failCount > 0) {
            $this->error('Preflight check FAILED with blocking issues. Please resolve the [FAIL] items above.');
            return Command::FAILURE;
        }

        if ($this->warnCount > 0) {
            $this->warn('Preflight check passed with warnings. Review [WARN] items before deployment.');
            return Command::SUCCESS;
        }

        $this->info('Preflight check PASSED! System is fully configured and ready.');
        return Command::SUCCESS;
    }

    protected function checkEnvironmentConfig(bool $isProduction): void
    {
        $this->comment('--- 1. Environment & Configuration ---');

        $env = (string) config('app.env');
        $debug = (bool) config('app.debug');
        $url = (string) config('app.url');
        $key = (string) config('app.key');

        if ($isProduction) {
            if ($env === 'production') {
                $this->recordPass('APP_ENV is set to "production"');
            } else {
                $this->recordFail("APP_ENV is '{$env}', expected 'production' for production deployment");
            }

            if (!$debug) {
                $this->recordPass('APP_DEBUG is false (error details hidden)');
            } else {
                $this->recordFail('APP_DEBUG is true! Sensitive debug details could be exposed to users');
            }

            if (str_starts_with(strtolower($url), 'https://')) {
                $this->recordPass("APP_URL uses secure HTTPS ({$url})");
            } else {
                $this->recordFail("APP_URL must use HTTPS in production (currently: {$url})");
            }
        } else {
            $this->recordPass("APP_ENV is '{$env}' (Valid for local workflow)");
            if ($debug) {
                $this->recordPass('APP_DEBUG is enabled (Normal for local debugging)');
            } else {
                $this->recordPass('APP_DEBUG is disabled');
            }
            $this->recordPass("APP_URL is '{$url}'");
        }

        if (!empty($key)) {
            $this->recordPass('APP_KEY is generated and present');
        } else {
            $this->recordFail('APP_KEY is missing! Run "php artisan key:generate"');
        }
    }

    protected function checkPhpPlatform(): void
    {
        $this->comment('--- 2. PHP Platform & Extensions ---');

        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.2.0', '>=')) {
            $this->recordPass("PHP version is {$phpVersion} (>= 8.2 required)");
        } else {
            $this->recordFail("PHP version {$phpVersion} is outdated. Hostinger requires PHP 8.2 or 8.3");
        }

        $requiredExtensions = [
            'pdo_mysql',
            'mbstring',
            'openssl',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'bcmath',
            'fileinfo',
            'curl',
        ];

        $missingExt = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExt[] = $ext;
            }
        }

        if (empty($missingExt)) {
            $this->recordPass('All required PHP extensions are loaded (' . implode(', ', $requiredExtensions) . ')');
        } else {
            $this->recordFail('Missing required PHP extensions: ' . implode(', ', $missingExt));
        }
    }

    protected function checkDatabase(): void
    {
        $this->comment('--- 3. Database Connectivity & Core Tables ---');

        try {
            DB::connection()->getPdo();
            $connectionName = DB::getDefaultConnection();
            $databaseName = DB::connection()->getDatabaseName();
            $this->recordPass("Database connected successfully (Driver: {$connectionName}, Database: {$databaseName})");

            $coreTables = ['users', 'properties', 'rooms', 'bookings', 'messages', 'landlord_profiles'];
            $missingTables = [];
            foreach ($coreTables as $table) {
                if (!Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                $this->recordPass('Core database tables are present (' . implode(', ', $coreTables) . ')');
            } else {
                $this->recordFail('Missing database tables: ' . implode(', ', $missingTables) . '. Run "php artisan migrate --force"');
            }

            // Session table check
            if (config('session.driver') === 'database') {
                if (Schema::hasTable('sessions')) {
                    $this->recordPass('Database session table "sessions" exists');
                } else {
                    $this->recordFail('SESSION_DRIVER is "database" but "sessions" table is missing');
                }
            }

            // Cache table check
            if (config('cache.default') === 'database') {
                if (Schema::hasTable('cache')) {
                    $this->recordPass('Database cache table "cache" exists');
                } else {
                    $this->recordFail('CACHE_STORE is "database" but "cache" table is missing');
                }
            }
        } catch (Throwable $e) {
            $this->recordFail('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function checkStoragePermissions(bool $isProduction): void
    {
        $this->comment('--- 4. Storage & Directory Writability ---');

        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($directories as $name => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0775, true);
            }

            if (is_writable($path)) {
                $this->recordPass("{$name} is writable");
            } else {
                $this->recordFail("{$name} ({$path}) is not writable. Ensure permissions 755 or 775");
            }
        }

        $publicStorage = public_path('storage');
        if (file_exists($publicStorage) || is_link($publicStorage)) {
            $this->recordPass('public/storage exists or is linked');
        } else {
            if (config('filesystems.default') === 'public') {
                $this->recordWarn('public/storage symlink not found. Run "php artisan storage:link"');
            } else {
                $this->recordPass('public/storage link not required (Cloud storage configured as default)');
            }
        }
    }

    protected function checkRoutingArtifacts(): void
    {
        $this->comment('--- 5. Server & Routing Artifacts ---');

        $rootHtaccess = base_path('.htaccess');
        if (file_exists($rootHtaccess)) {
            $content = file_get_contents($rootHtaccess);
            if (str_contains($content, 'RewriteRule') && str_contains($content, 'public/')) {
                $this->recordPass('Root .htaccess is present with Hostinger front-controller routing');
            } else {
                $this->recordWarn('Root .htaccess exists but may not have standard Hostinger routing');
            }
        } else {
            $this->recordFail('Root .htaccess is missing! Required for Hostinger shared hosting document root');
        }

        $publicHtaccess = public_path('.htaccess');
        if (file_exists($publicHtaccess)) {
            $this->recordPass('public/.htaccess is present');
        } else {
            $this->recordFail('public/.htaccess is missing');
        }
    }

    protected function checkFrontendBuild(bool $isProduction): void
    {
        $this->comment('--- 6. Frontend Production Build ---');

        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (is_array($manifest) && !empty($manifest)) {
                $this->recordPass('public/build/manifest.json is present and valid');
            } else {
                $this->recordWarn('public/build/manifest.json exists but appears empty');
            }
        } else {
            if ($isProduction) {
                $this->recordFail('public/build/manifest.json is missing! Run "npm run build" before deploying to Hostinger');
            } else {
                $this->recordWarn('public/build/manifest.json not found (Run "npm run build" for production)');
            }
        }
    }

    protected function checkServices(): void
    {
        $this->comment('--- 7. External Services & Resilience ---');

        // Mailer
        $mailer = (string) config('mail.default');
        $this->recordPass("MAIL_MAILER is configured as '{$mailer}'");

        // Cloudflare R2
        $r2Key = config('filesystems.disks.r2.key');
        $r2Secret = config('filesystems.disks.r2.secret');
        $r2Bucket = config('filesystems.disks.r2.bucket');
        $r2Endpoint = config('filesystems.disks.r2.endpoint');

        if (!empty($r2Key) && !empty($r2Secret) && !empty($r2Bucket) && !empty($r2Endpoint)) {
            $this->recordPass('Cloudflare R2 storage credentials are configured');
        } else {
            $this->recordPass('Cloudflare R2 not configured (Local public disk active as fallback)');
        }

        // Hugging Face
        $hfToken = config('services.huggingface.token');
        if (!empty($hfToken)) {
            $this->recordPass('Hugging Face AI Chatbot token is configured');
        } else {
            $this->recordPass('Hugging Face AI token not configured (AI Chatbot operates in safe offline fallback mode)');
        }

        // Queue
        $queue = (string) config('queue.default');
        if ($queue === 'sync') {
            $this->recordPass('QUEUE_CONNECTION is "sync" (Optimal for shared hosting without background daemons)');
        } else {
            $this->recordPass("QUEUE_CONNECTION is '{$queue}'");
        }
    }

    protected function recordPass(string $message): void
    {
        $this->passCount++;
        $this->line("  <fg=green>[PASS]</> {$message}");
    }

    protected function recordWarn(string $message): void
    {
        $this->warnCount++;
        $this->line("  <fg=yellow>[WARN]</> {$message}");
    }

    protected function recordFail(string $message): void
    {
        $this->failCount++;
        $this->line("  <fg=red>[FAIL]</> {$message}");
    }
}
