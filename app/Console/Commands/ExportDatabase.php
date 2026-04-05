<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ExportDatabase extends Command
{
    protected $signature = 'export
        {--path= : Output directory (default: storage/app/exports)}
        {--file= : Output file name}
        {--schema-only : Export schema only (no row data)}
        {--gzip : Compress output as .gz}
        {--stack=auto : Preferred local SQL stack (auto|laragon|xampp)}
        {--binary= : mysqldump executable path (optional; defaults to DB_DUMP_BINARY or mysqldump)}';

    protected $description = 'Export MySQL database to an SQL dump file';

    public function handle(): int
    {
        $connectionName = config('database.default');
        $driver = (string) config("database.connections.{$connectionName}.driver");

        if ($driver !== 'mysql') {
            $this->error("The export command currently supports only mysql. Current driver: {$driver}");

            return Command::FAILURE;
        }

        $host = (string) config("database.connections.{$connectionName}.host", '127.0.0.1');
        $port = (string) config("database.connections.{$connectionName}.port", '3306');
        $database = (string) config("database.connections.{$connectionName}.database", '');
        $username = (string) config("database.connections.{$connectionName}.username", '');
        $password = (string) config("database.connections.{$connectionName}.password", '');

        if ($database === '' || $username === '') {
            $this->error('Database configuration is incomplete. Please check DB_DATABASE and DB_USERNAME.');

            return Command::FAILURE;
        }

        $gzip = (bool) $this->option('gzip');
        $schemaOnly = (bool) $this->option('schema-only');
        $stack = strtolower(trim((string) $this->option('stack')));
        if (!in_array($stack, ['auto', 'laragon', 'xampp'], true)) {
            $stack = 'auto';
        }

        $binaryOption = trim((string) $this->option('binary'));
        $binaryHint = $binaryOption !== ''
            ? $binaryOption
            : trim((string) env('DB_DUMP_BINARY', 'mysqldump'));

        $binaryCandidates = $this->resolveDumpBinaries($binaryHint, $stack);
        if (count($binaryCandidates) === 0) {
            $this->error("Unable to locate mysqldump binary from '{$binaryHint}'.");
            $this->line('Tip: set DB_DUMP_BINARY in .env or pass --binary with a full path.');
            if (PHP_OS_FAMILY === 'Windows') {
                $this->line('Example: php artisan export --binary="C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe"');
                $this->line('Or prefer Laragon: php artisan export --stack=laragon');
            }

            return Command::FAILURE;
        }

        $directory = (string) ($this->option('path') ?: storage_path('app/exports'));
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->error("Unable to create export directory: {$directory}");

            return Command::FAILURE;
        }

        $filename = (string) ($this->option('file') ?: sprintf('%s_%s.sql', $database, now()->format('Ymd_His')));
        if (!$gzip && !preg_match('/\.sql$/i', $filename)) {
            $filename .= '.sql';
        }
        if ($gzip && !preg_match('/\.gz$/i', $filename)) {
            $filename .= '.gz';
        }

        $filePath = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;

        $env = null;
        if ($password !== '') {
            $env = ['MYSQL_PWD' => $password];
        }

        $this->line('Starting database export...');
        if (PHP_OS_FAMILY === 'Windows') {
            $this->line('Preferred stack: '.$stack);
        }

        $output = null;
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
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
            ];

            if ($schemaOnly) {
                $arguments[] = '--no-data';
            }

            $arguments[] = $database;

            $process = new Process($arguments, base_path(), $env);
            $process->setTimeout(null);
            $process->run();

            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $usedBinary = $candidate;
                break;
            }

            $lastError = trim($process->getErrorOutput());
            if ($lastError === '') {
                $lastError = trim($process->getOutput());
            }
        }

        if ($usedBinary === null || $output === null) {
            if ($lastError === '') {
                $lastError = 'Unknown export error. Ensure mysqldump is installed and matches your MySQL server version.';
            }

            $this->error($lastError);

            if (str_contains(strtolower($lastError), 'caching_sha2_password')) {
                $this->line('Tip: use a MySQL 8+ mysqldump binary (not older XAMPP clients).');
                $this->line('Example: php artisan export --binary="C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe"');
                $this->line('Or if your DB runs in Laragon: php artisan export --stack=laragon');
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
        if ($gzip) {
            $compressed = gzencode($output, 9);
            if ($compressed === false) {
                $this->error('Failed to compress SQL dump output.');

                return Command::FAILURE;
            }

            $output = $compressed;
        }

        if (file_put_contents($filePath, $output) === false) {
            $this->error("Unable to write dump file: {$filePath}");

            return Command::FAILURE;
        }

        $this->info("Database export complete: {$filePath}");

        return Command::SUCCESS;
    }

    protected function resolveDumpBinaries(string $binaryHint, string $stack = 'auto'): array
    {
        $binaryHint = trim($binaryHint);
        if ($binaryHint === '') {
            $binaryHint = 'mysqldump';
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
