<#
.SYNOPSIS
    OBHS Hostinger Shared-Hosting Deployment Packager
.DESCRIPTION
    Creates a clean, production-ready deployment package in dist/hostinger/ and dist/obhs-hostinger.zip
    WITHOUT altering local development files or removing local dev dependencies.
#>

$scriptFolder = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $scriptFolder) {
    $scriptFolder = Get-Location
}
$projectRoot = (Resolve-Path "$scriptFolder\..").Path
Set-Location $projectRoot

Write-Host "====================================================================" -ForegroundColor Cyan
Write-Host "     OBHS HOSTINGER SHARED-HOSTING DEPLOYMENT PACKAGER              " -ForegroundColor Yellow
Write-Host "====================================================================" -ForegroundColor Cyan
Write-Host "Project Root: $projectRoot" -ForegroundColor Gray

# Define target paths
$distDir = Join-Path $projectRoot "dist"
$stagingDir = Join-Path $distDir "hostinger"
$zipOutput = Join-Path $distDir "obhs-hostinger.zip"

# ------------------------------------------------------------------
# 1. Preflight Verification
# ------------------------------------------------------------------
Write-Host "`n[1/6] Running local preflight checks..." -ForegroundColor Green

$npmCmd = if (Get-Command "npm.cmd" -ErrorAction SilentlyContinue) { "npm.cmd" } else { "npm" }
$composerCmd = if (Get-Command "composer.bat" -ErrorAction SilentlyContinue) { "composer.bat" } else { "composer" }

if (-not (Get-Command $npmCmd -ErrorAction SilentlyContinue)) {
    Write-Host "[FAIL] 'npm' was not found in PATH. Node.js is required locally to build Vite assets." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------------
# 2. Build Frontend Production Assets
# ------------------------------------------------------------------
Write-Host "`n[2/6] Building production frontend bundle (npm run build)..." -ForegroundColor Green
& $npmCmd run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "[FAIL] 'npm run build' failed. Check Vite/Tailwind errors above." -ForegroundColor Red
    exit 1
}

$manifestFile = Join-Path $projectRoot "public\build\manifest.json"
if (-not (Test-Path $manifestFile)) {
    Write-Host "[FAIL] manifest.json was not generated in public/build/." -ForegroundColor Red
    exit 1
}
Write-Host "[PASS] Production assets built successfully into public/build/!" -ForegroundColor DarkGreen

# ------------------------------------------------------------------
# 3. Clean and Prepare Staging Directory
# ------------------------------------------------------------------
Write-Host "`n[3/6] Initializing clean staging directory in dist/hostinger/..." -ForegroundColor Green

if (Test-Path $stagingDir) {
    Remove-Item -Recurse -Force $stagingDir
}
if (Test-Path $zipOutput) {
    Remove-Item -Force $zipOutput
}

New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

# ------------------------------------------------------------------
# 4. Copy Required Application Files
# ------------------------------------------------------------------
Write-Host "`n[4/6] Copying production application tree..." -ForegroundColor Green

# Core folders to copy
$coreFolders = @("app", "bootstrap", "config", "database", "resources", "routes")
foreach ($folder in $coreFolders) {
    $src = Join-Path $projectRoot $folder
    if (Test-Path $src) {
        Write-Host "  -> Copying $folder/" -ForegroundColor Gray
        Copy-Item -Path $src -Destination (Join-Path $stagingDir $folder) -Recurse -Force
    }
}

# Ensure bootstrap/cache is empty of local runtime caches
$stagingBootstrapCache = Join-Path $stagingDir "bootstrap\cache"
if (-not (Test-Path $stagingBootstrapCache)) {
    New-Item -ItemType Directory -Path $stagingBootstrapCache -Force | Out-Null
}
Get-ChildItem -Path $stagingBootstrapCache -Filter "*.php" -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue

# Copy public directory (Strictly exclude Windows junction 'storage')
Write-Host "  -> Copying public/ (excluding Windows storage junction)..." -ForegroundColor Gray
$stagingPublic = Join-Path $stagingDir "public"
New-Item -ItemType Directory -Path $stagingPublic -Force | Out-Null

Get-ChildItem -Path (Join-Path $projectRoot "public") -Force | ForEach-Object {
    if ($_.Name -ne "storage") {
        Copy-Item -Path $_.FullName -Destination (Join-Path $stagingPublic $_.Name) -Recurse -Force
    }
}

# Explicitly ensure public/.htaccess exists in staging
if (Test-Path (Join-Path $projectRoot "public\.htaccess")) {
    Copy-Item -Path (Join-Path $projectRoot "public\.htaccess") -Destination (Join-Path $stagingPublic ".htaccess") -Force
}

# Prepare clean storage directory structure
Write-Host "  -> Preparing clean storage/ structure..." -ForegroundColor Gray
$stagingStorage = Join-Path $stagingDir "storage"
$storageSubDirs = @(
    "app\public",
    "framework\cache\data",
    "framework\sessions",
    "framework\testing",
    "framework\views",
    "logs"
)
foreach ($sub in $storageSubDirs) {
    $dirPath = Join-Path $stagingStorage $sub
    New-Item -ItemType Directory -Path $dirPath -Force | Out-Null
    # Add a .gitignore so directory structure is preserved in zip
    Set-Content -Path (Join-Path $dirPath ".gitignore") -Value "*`n!.gitignore"
}

# Copy existing public uploaded files if present in storage/app/public
$localStoragePublic = Join-Path $projectRoot "storage\app\public"
if (Test-Path $localStoragePublic) {
    Get-ChildItem -Path $localStoragePublic -Force | ForEach-Object {
        Copy-Item -Path $_.FullName -Destination (Join-Path $stagingStorage "app\public") -Recurse -Force
    }
}

# Copy root deployment files
$rootFiles = @(
    "artisan",
    "composer.json",
    "composer.lock",
    ".htaccess",
    ".env.hostinger.example"
)
foreach ($file in $rootFiles) {
    $src = Join-Path $projectRoot $file
    if (Test-Path $src) {
        Write-Host "  -> Copying $file" -ForegroundColor Gray
        Copy-Item -Path $src -Destination (Join-Path $stagingDir $file) -Force
    }
}

# Explicitly ensure root .htaccess is copied (including dotfile handling)
if (Test-Path (Join-Path $projectRoot ".htaccess")) {
    Copy-Item -Path (Join-Path $projectRoot ".htaccess") -Destination (Join-Path $stagingDir ".htaccess") -Force
}

# ------------------------------------------------------------------
# 5. Build Clean Production Vendor Dependencies
# ------------------------------------------------------------------
Write-Host "`n[5/6] Preparing production vendor dependencies..." -ForegroundColor Green

$composerInstalledInStaging = $false
if (Get-Command $composerCmd -ErrorAction SilentlyContinue) {
    Write-Host "  -> Running 'composer install --no-dev --prefer-dist --optimize-autoloader' inside staging..." -ForegroundColor Gray
    try {
        Push-Location $stagingDir
        & $composerCmd install --no-dev --prefer-dist --optimize-autoloader --no-interaction --quiet
        Pop-Location
        if (Test-Path (Join-Path $stagingDir "vendor\autoload.php")) {
            $composerInstalledInStaging = $true
            Write-Host "[PASS] Clean production vendor built via Composer!" -ForegroundColor DarkGreen
        }
    } catch {
        Pop-Location
        Write-Host "  [WARN] Composer install in staging encountered an issue. Falling back to local vendor copy." -ForegroundColor Yellow
    }
}

if (-not $composerInstalledInStaging) {
    Write-Host "  -> Copying existing vendor/ directory to staging..." -ForegroundColor Gray
    $localVendor = Join-Path $projectRoot "vendor"
    if (Test-Path $localVendor) {
        Copy-Item -Path $localVendor -Destination (Join-Path $stagingDir "vendor") -Recurse -Force
        Write-Host "[PASS] Local vendor copied to staging." -ForegroundColor DarkGreen
    } else {
        Write-Host "[FAIL] No vendor directory found! Run 'composer install' locally first." -ForegroundColor Red
        exit 1
    }
}

# Final security check on staging directory: NEVER package local .env
$forbiddenFiles = @(".env", ".env.local", ".git", ".github", ".vscode", "node_modules")
foreach ($bad in $forbiddenFiles) {
    $checkPath = Join-Path $stagingDir $bad
    if (Test-Path $checkPath) {
        Remove-Item -Recurse -Force $checkPath
        Write-Host "  -> Removed accidental inclusion of $bad from staging" -ForegroundColor Yellow
    }
}

# ------------------------------------------------------------------
# 6. Compress into dist/obhs-hostinger.zip
# ------------------------------------------------------------------
Write-Host "`n[6/6] Compressing package into dist/obhs-hostinger.zip..." -ForegroundColor Green

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($stagingDir, $zipOutput, [System.IO.Compression.CompressionLevel]::Optimal, $false)

if (-not (Test-Path $zipOutput)) {
    Write-Host "Falling back to Compress-Archive..." -ForegroundColor Yellow
    Compress-Archive -Path (Join-Path $stagingDir "*") -DestinationPath $zipOutput -Force
}

# Clean staging directory to save local disk space
Remove-Item -Recurse -Force $stagingDir

$zipItem = Get-Item $zipOutput
$sizeMB = [math]::Round($zipItem.Length / 1MB, 2)

Write-Host "`n====================================================================" -ForegroundColor Cyan
Write-Host "     PACKAGE COMPLETE!                                              " -ForegroundColor Yellow
Write-Host "====================================================================" -ForegroundColor Cyan
Write-Host "ZIP Archive       : $zipOutput" -ForegroundColor White
Write-Host "ZIP File Size     : $sizeMB MB" -ForegroundColor White
Write-Host "`nHostinger Deployment Quick Instructions:" -ForegroundColor Yellow
Write-Host "1. In Hostinger hPanel -> File Manager, go to 'public_html'." -ForegroundColor White
Write-Host "2. Upload '$zipOutput' and Extract it directly in 'public_html'." -ForegroundColor White
Write-Host "3. On FIRST deployment: copy .env.hostinger.example to .env and configure DB." -ForegroundColor White
Write-Host "4. On APPLICATION UPDATES: never overwrite the existing production .env!" -ForegroundColor White
Write-Host "5. Run 'php artisan storage:link' and 'php artisan migrate --force'." -ForegroundColor White
Write-Host "====================================================================`n" -ForegroundColor Cyan
