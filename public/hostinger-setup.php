<?php
/**
 * OBHS Hostinger Shared Hosting Setup & Migration Helper
 * 
 * Access via browser: https://yourdomain.com/hostinger-setup.php?key=YourSecretKey
 * Delete this file after completing setup!
 */

// Disable direct access without authorization key
$expectedSecret = getenv('SETUP_SECRET') ?: 'obhs2026';
$providedKey = $_GET['key'] ?? $_POST['key'] ?? '';

// Check if vendor and bootstrap exist (supports both root public_html and public/ subfolder)
$baseDir = __DIR__;
if (!file_exists($baseDir . '/vendor/autoload.php') || !file_exists($baseDir . '/bootstrap/app.php')) {
    $baseDir = dirname(__DIR__);
}

if (!file_exists($baseDir . '/vendor/autoload.php') || !file_exists($baseDir . '/bootstrap/app.php')) {
    die('<h1>Setup Error</h1><p>Laravel core files (vendor/autoload.php or bootstrap/app.php) not found. Looked in: ' . htmlspecialchars(__DIR__) . ' and ' . htmlspecialchars(dirname(__DIR__)) . '</p>');
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if secret key in .env is configured
$envSecret = config('app.setup_secret') ?: env('SETUP_SECRET', 'obhs2026');
$isAuthorized = ($providedKey !== '' && ($providedKey === $envSecret || $providedKey === 'obhs2026'));

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$outputMessage = '';
$outputType = 'info';

if ($isAuthorized && $_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'migrate':
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $outputMessage = \Illuminate\Support\Facades\Artisan::output();
                $outputType = 'success';
            } catch (\Throwable $e) {
                $outputMessage = 'Migration Failed: ' . $e->getMessage();
                $outputType = 'danger';
            }
            break;

        case 'storage_link':
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                $outputMessage = \Illuminate\Support\Facades\Artisan::output();
                $outputType = 'success';
            } catch (\Throwable $e) {
                $outputMessage = 'Storage Link Failed: ' . $e->getMessage();
                $outputType = 'danger';
            }
            break;

        case 'optimize':
            try {
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                $clearOut = \Illuminate\Support\Facades\Artisan::output();
                \Illuminate\Support\Facades\Artisan::call('optimize');
                $optOut = \Illuminate\Support\Facades\Artisan::output();
                $outputMessage = $clearOut . "\n" . $optOut;
                $outputType = 'success';
            } catch (\Throwable $e) {
                $outputMessage = 'Optimization Failed: ' . $e->getMessage();
                $outputType = 'danger';
            }
            break;

        case 'delete_self':
            if (unlink(__FILE__)) {
                die('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Deleted</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="bg-light p-5"><div class="container text-center"><div class="alert alert-success p-4 rounded-4"><h4><i class="bi bi-check-circle"></i> hostinger-setup.php deleted successfully!</h4><p>Your setup file has been securely removed. You can now visit your site.</p><a href="/" class="btn btn-success mt-2">Go to Homepage</a></div></div></body></html>');
            } else {
                $outputMessage = 'Failed to automatically delete this file. Please remove hostinger-setup.php manually from File Manager.';
                $outputType = 'warning';
            }
            break;
    }
}

// Check database connection status
$dbConnected = false;
$dbError = '';
$pendingMigrations = [];

try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbConnected = true;

    $migrator = app('migrator');
    $files = $migrator->getMigrationFiles(database_path('migrations'));
    $ran = $migrator->getRepository()->getRan();
    $pendingMigrations = array_diff(array_keys($files), $ran);
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}

$storageLinked = file_exists(__DIR__ . '/storage');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OBHS - Hostinger Setup & Migration Helper</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0fdf4; font-family: system-ui, -apple-system, sans-serif; color: #0f172a; }
        .setup-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #dcfce7; }
        .badge-step { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #166534; color: #fff; font-weight: bold; font-size: 14px; }
        pre { background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 12px; font-size: 13px; max-height: 250px; overflow-y: auto; white-space: pre-wrap; }
    </style>
</head>
<body class="py-5">
<div class="container" style="max-width: 760px;">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-success"><i class="bi bi-shield-check"></i> OBHS Deployment Helper</h2>
        <p class="text-muted">Hostinger Shared Hosting One-Click Setup & Migration Wizard</p>
    </div>

    <?php if (!$isAuthorized): ?>
        <div class="setup-card p-4 p-md-5 text-center">
            <i class="bi bi-lock-fill text-warning display-4 mb-3"></i>
            <h4 class="fw-bold">Authorization Required</h4>
            <p class="text-muted">Enter the setup secret key to unlock deployment tools:</p>
            <form method="GET" class="d-flex justify-content-center gap-2 mt-3" style="max-width: 400px; margin: 0 auto;">
                <input type="password" name="key" class="form-control" placeholder="Enter setup key (default: obhs2026)" required autofocus>
                <button type="submit" class="btn btn-success px-4">Unlock</button>
            </form>
            <div class="small text-muted mt-3">Note: Default key is <code>obhs2026</code> or what is configured in your <code>SETUP_SECRET</code> .env variable.</div>
        </div>
    <?php else: ?>
        <?php if (!empty($outputMessage)): ?>
            <div class="alert alert-<?= $outputType ?> rounded-4 shadow-sm mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> Operation Output:</h6>
                <pre class="m-0"><?= htmlspecialchars($outputMessage) ?></pre>
            </div>
        <?php endif; ?>

        <!-- Database Status -->
        <div class="setup-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-database text-success me-2"></i> Database Status</h5>
            <?php if ($dbConnected): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>MySQL Connection Successful! (Connected to database: <strong><?= config('database.connections.mysql.database') ?></strong>)</div>
                </div>

                <?php if (count($pendingMigrations) > 0): ?>
                    <div class="alert alert-warning mb-3">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> <?= count($pendingMigrations) ?> pending migration(s) detected!</strong>
                        <div class="small mt-1">These tables/columns are missing from your database, which causes Error 500 on admin dashboard:</div>
                        <ul class="small mb-0 mt-2" style="max-height: 120px; overflow-y: auto;">
                            <?php foreach ($pendingMigrations as $migration): ?>
                                <li><?= htmlspecialchars($migration) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-check2-all me-1"></i> All migrations are up to date!
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-danger mb-3">
                    <strong><i class="bi bi-x-circle-fill"></i> Database Connection Failed:</strong>
                    <div class="small mt-1"><?= htmlspecialchars($dbError) ?></div>
                    <div class="small mt-2">Please check your database credentials in <code>.env</code>.</div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-grid gap-3">
                <form method="POST" onsubmit="return confirm('Run php artisan migrate --force now?');">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="migrate">
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-play-circle-fill"></i> Run Database Migrations (Fix Error 500)
                    </button>
                </form>

                <div class="row g-2">
                    <div class="col-6">
                        <form method="POST">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                            <input type="hidden" name="action" value="storage_link">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-link-45deg"></i> Link Storage (Images)
                            </button>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                            <input type="hidden" name="action" value="optimize">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-speedometer2"></i> Clear & Rebuild Cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finish & Clean up -->
        <div class="setup-card p-4 border-danger">
            <h5 class="fw-bold text-danger mb-2"><i class="bi bi-trash text-danger me-2"></i> Clean Up (Security)</h5>
            <p class="small text-muted mb-3">Once migrations are done and your site is working, delete this helper file so nobody else can access it:</p>
            <div class="d-flex gap-2">
                <a href="/admin/dashboard" class="btn btn-primary fw-semibold">
                    <i class="bi bi-speedometer me-1"></i> Open Admin Dashboard
                </a>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete hostinger-setup.php?');">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="delete_self">
                    <button type="submit" class="btn btn-outline-danger fw-semibold">
                        <i class="bi bi-trash-fill me-1"></i> Delete hostinger-setup.php Now
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
