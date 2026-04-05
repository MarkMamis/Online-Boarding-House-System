<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ImportDatabase extends Command
{
    protected $signature = 'import
        {file? : SQL dump file path (.sql or .gz). Defaults to latest file in storage/app/exports}
        {--stack=auto : Preferred local SQL stack (auto|laragon|xampp)}
        {--binary= : mysql executable path (optional; defaults to DB_IMPORT_BINARY or mysql)}
        {--database= : Override target database name}
        {--force : Import without confirmation prompt}';

    protected $description = 'Import an SQL dump into a MySQL database';

    public function handle(): int
    {
        $connectionName = config('database.default');
        $driver = (string) config("database.connections.{$connectionName}.driver");

        if ($driver !== 'mysql') {
            $this->error("The import command currently supports only mysql. Current driver: {$driver}");

            return Command::FAILURE;
        }

        $host = (string) config("database.connections.{$connectionName}.host", '127.0.0.1');
        $port = (string) config("database.connections.{$connectionName}.port", '3306');
        $username = (string) config("database.connections.{$connectionName}.username", '');
        $password = (string) config("database.connections.{$connectionName}.password", '');

        $database = trim((string) $this->option('database'));
        if ($database === '') {
            $database = (string) config("database.connections.{$connectionName}.database", '');
        }

        if ($database === '' || $username === '') {
            $this->error('Database configuration is incomplete. Please check DB_DATABASE and DB_USERNAME.');

            return Command::FAILURE;
        }

        $stack = strtolower(trim((string) $this->option('stack')));
        if (!in_array($stack, ['auto', 'laragon', 'xampp'], true)) {
            $stack = 'auto';
        }

        $binaryOption = trim((string) $this->option('binary'));
        $binaryHint = $binaryOption !== ''
            ? $binaryOption
            : trim((string) env('DB_IMPORT_BINARY', 'mysql'));

        $binaryCandidates = $this->resolveMysqlBinaries($binaryHint, $stack);
        if (count($binaryCandidates) === 0) {
            $this->error("Unable to locate mysql binary from '{$binaryHint}'.");
            $this->line('Tip: set DB_IMPORT_BINARY in .env or pass --binary with a full path.');
            if (PHP_OS_FAMILY === 'Windows') {
                $this->line('Example: php artisan import --binary="C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe"');
            }

            return Command::FAILURE;
        }

        $fileInput = (string) ($this->argument('file') ?? '');
        $filePath = $this->resolveImportFilePath($fileInput);
        if ($filePath === null) {
            if ($fileInput !== '') {
                $this->error("Import file not found: {$fileInput}");
            } else {
                $this->error('No dump file found in storage/app/exports. Pass a file path to import.');
            }

            return Command::FAILURE;
        }

        $sql = $this->loadSql($filePath);
        if ($sql === null) {
            $this->error("Unable to read SQL content from: {$filePath}");

            return Command::FAILURE;
        }

        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                "Import {$filePath} into database '{$database}'? This may overwrite existing data.",
                false
            );

            if (!$confirmed) {
                $this->warn('Import cancelled.');

                return Command::SUCCESS;
            }
        }

        $env = null;
        if ($password !== '') {
            $env = ['MYSQL_PWD' => $password];
        }

        $this->line('Starting database import...');
        if (PHP_OS_FAMILY === 'Windows') {
            $this->line('Preferred stack: '.$stack);
        }

        $usedBinary = null;
        $lastError = '';
        $attempted = [];

        foreach ($binaryCandidates as $candidate) {
            $attempted[] = $candidate;
            if (count($binaryCandidates) > 1) {
                $this->line('Trying binary: '.$candidate);
            }

            $arguments = [
                $candidate,
                '--host='.$host,
                '--port='.$port,
                '--user='.$username,
                '--default-character-set=utf8mb4',
                $database,
            ];

            $process = new Process($arguments, base_path(), $env);
            $process->setTimeout(null);
            $process->setInput($sql);
            $process->run();

            if ($process->isSuccessful()) {
                $usedBinary = $candidate;
                break;
            }

            $lastError = trim($process->getErrorOutput());
            if ($lastError === '') {
                $lastError = trim($process->getOutput());
            }
        }

        if ($usedBinary === null) {
            if ($lastError === '') {
                $lastError = 'Unknown import error. Ensure mysql client is installed and matches your server version.';
            }

            $this->error($lastError);

            if (str_contains(strtolower($lastError), 'caching_sha2_password')) {
                $this->line('Tip: use a MySQL 8+ mysql.exe binary (not older XAMPP clients).');
                $this->line('Try: php artisan import --stack=laragon --force');
            }

            if (count($attempted) > 0) {
                $this->line('Attempted binaries:');
                foreach ($attempted as $path) {
                    $this->line(' - '.$path);
                }
            }

            return Command::FAILURE;
        }

        $this->line('Using binary: '.$usedBinary);
        $this->info('Database import complete.');

        return Command::SUCCESS;
    }

    protected function resolveImportFilePath(string $fileInput): ?string
    {
        $fileInput = trim($fileInput);
        if ($fileInput !== '') {
            $candidates = [$fileInput, base_path($fileInput)];
            foreach (array_values(array_unique($candidates)) as $candidate) {
                if (is_file($candidate)) {
                    return $candidate;
                }
            }

            return null;
        }

        $exportDir = storage_path('app/exports');
        if (!is_dir($exportDir)) {
            return null;
        }

        $files = array_filter(glob($exportDir.DIRECTORY_SEPARATOR.'*') ?: [], function ($path) {
            return is_file($path) && preg_match('/\.(sql|gz)$/i', $path);
        });

        if (count($files) === 0) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        return $files[0] ?? null;
    }

    protected function loadSql(string $filePath): ?string
    {
        $raw = file_get_contents($filePath);
        if ($raw === false) {
            return null;
        }

        if (preg_match('/\.gz$/i', $filePath)) {
            $decoded = gzdecode($raw);

            return $decoded === false ? null : $decoded;
        }

        return $raw;
    }

    protected function resolveMysqlBinaries(string $binaryHint, string $stack = 'auto'): array
    {
        $binaryHint = trim($binaryHint);
        if ($binaryHint === '') {
            $binaryHint = 'mysql';
        }

        $candidates = [$binaryHint];
        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = array_merge($candidates, $this->guessWindowsBinaries($binaryHint, $stack));
        }

        $resolved = [];
        foreach (array_values(array_unique($candidates)) as $candidate) {
            if ($this->binaryExists($candidate)) {
                $resolved[] = $candidate;
            }
        }

        return $resolved;
    }

    protected function binaryExists(string $binary): bool
    {
        $hasPathSeparator = str_contains($binary, DIRECTORY_SEPARATOR)
            || str_contains($binary, '/')
            || str_contains($binary, '\\');

        if ($hasPathSeparator) {
            return is_file($binary);
        }

        $checker = new Process(PHP_OS_FAMILY === 'Windows' ? ['where', $binary] : ['which', $binary]);
        $checker->run();

        return $checker->isSuccessful();
    }

    protected function guessWindowsBinaries(string $binaryHint, string $stack = 'auto'): array
    {
        $binaryFile = basename($binaryHint);
        if (!str_ends_with(strtolower($binaryFile), '.exe')) {
            $binaryFile .= '.exe';
        }

        $programFilesCandidates = [];
        $laragonCandidates = [];
        $xamppCandidates = ['C:\\xampp\\mysql\\bin\\'.$binaryFile];
        $wampCandidates = [];

        $roots = array_filter([
            getenv('ProgramFiles') ?: null,
            getenv('ProgramW6432') ?: null,
            getenv('ProgramFiles(x86)') ?: null,
        ]);

        foreach ($roots as $root) {
            $mysqlPattern = rtrim($root, '\\')."\\MySQL\\MySQL Server *\\bin\\{$binaryFile}";
            foreach (glob($mysqlPattern) ?: [] as $found) {
                $programFilesCandidates[] = $found;
            }

            $mariaPattern = rtrim($root, '\\')."\\MariaDB *\\bin\\{$binaryFile}";
            foreach (glob($mariaPattern) ?: [] as $found) {
                $programFilesCandidates[] = $found;
            }
        }

        foreach (glob('C:\\laragon\\bin\\mysql\\mysql*\\bin\\'.$binaryFile) ?: [] as $found) {
            $laragonCandidates[] = $found;
        }
        foreach (glob('C:\\laragon\\bin\\mysql\\mariadb*\\bin\\'.$binaryFile) ?: [] as $found) {
            $laragonCandidates[] = $found;
        }
        foreach (glob('C:\\laragon\\bin\\mariadb\\mariadb*\\bin\\'.$binaryFile) ?: [] as $found) {
            $laragonCandidates[] = $found;
        }

        foreach (glob('C:\\wamp64\\bin\\mysql\\mysql*\\bin\\'.$binaryFile) ?: [] as $found) {
            $wampCandidates[] = $found;
        }

        if ($stack === 'laragon') {
            $candidates = array_merge($laragonCandidates, $programFilesCandidates, $xamppCandidates, $wampCandidates);
        } elseif ($stack === 'xampp') {
            $candidates = array_merge($xamppCandidates, $programFilesCandidates, $laragonCandidates, $wampCandidates);
        } else {
            $candidates = array_merge($programFilesCandidates, $laragonCandidates, $xamppCandidates, $wampCandidates);
        }

        return array_values(array_unique($candidates));
    }
}
