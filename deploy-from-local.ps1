# REVANA Deployment Script dari Windows ke Server
# Script untuk upload dan deploy dari local ke portal.apji.org

param(
    [string]$ServerUser = "root",
    [string]$ServerHost = "portal.apji.org",
    [string]$ServerPath = "/var/www/revana",
    [switch]$SkipDatabase
)

# Colors
function Write-Success { Write-Host "✓ $args" -ForegroundColor Green }
function Write-Error { Write-Host "✗ $args" -ForegroundColor Red }
function Write-Info { Write-Host "→ $args" -ForegroundColor Yellow }

# Header
Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "  REVANA Deployment dari Local" -ForegroundColor Cyan
Write-Host "  ke portal.apji.org" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Check prerequisites
Write-Info "Checking prerequisites..."

# Check if SCP is available
try {
    $null = Get-Command scp -ErrorAction Stop
    Write-Success "SCP tersedia"
} catch {
    Write-Error "SCP tidak ditemukan. Install OpenSSH Client terlebih dahulu."
    Write-Host "Cara install: Settings -> Apps -> Optional Features -> OpenSSH Client"
    exit 1
}

# Check if SSH is available
try {
    $null = Get-Command ssh -ErrorAction Stop
    Write-Success "SSH tersedia"
} catch {
    Write-Error "SSH tidak ditemukan. Install OpenSSH Client terlebih dahulu."
    exit 1
}

# Confirm deployment
Write-Host ""
$confirm = Read-Host "Deploy ke $ServerHost? (yes/no)"
if ($confirm -ne "yes") {
    Write-Info "Deployment dibatalkan"
    exit 0
}

Write-Host ""
Write-Info "Memulai deployment..."
Write-Host ""

# Step 1: Create archive
Write-Info "Step 1: Membuat archive dari project..."

$excludeFiles = @(
    "node_modules",
    "vendor",
    ".git",
    "storage/logs/*",
    "storage/framework/cache/*",
    "storage/framework/sessions/*",
    "storage/framework/views/*",
    ".env",
    "*.log"
)

$archiveName = "revana-deploy-$(Get-Date -Format 'yyyyMMdd-HHmmss').zip"
$archivePath = Join-Path $env:TEMP $archiveName

Write-Info "Membuat archive: $archiveName"

# Create zip excluding unnecessary files
$7zipPath = "C:\Program Files\7-Zip\7z.exe"
if (Test-Path $7zipPath) {
    $excludeArgs = $excludeFiles | ForEach-Object { "-x!$_" }
    & $7zipPath a -tzip $archivePath * $excludeArgs -mx1
    Write-Success "Archive created with 7-Zip"
} else {
    # Fallback to PowerShell Compress-Archive
    Write-Info "Using PowerShell compression (slower)..."
    Compress-Archive -Path * -DestinationPath $archivePath -CompressionLevel Fastest -Force
    Write-Success "Archive created"
}

# Step 2: Upload archive to server
Write-Info "Step 2: Upload archive ke server..."
Write-Host "Uploading to ${ServerUser}@${ServerHost}:/tmp/$archiveName"

scp $archivePath "${ServerUser}@${ServerHost}:/tmp/$archiveName"
if ($LASTEXITCODE -eq 0) {
    Write-Success "Archive uploaded"
} else {
    Write-Error "Upload gagal!"
    Remove-Item $archivePath -Force
    exit 1
}

# Cleanup local archive
Remove-Item $archivePath -Force

# Step 3: Export database (if not skipped)
if (-not $SkipDatabase) {
    Write-Info "Step 3: Export database..."
    
    # Read database config from .env
    $envContent = Get-Content .env
    $dbName = ($envContent | Select-String "DB_DATABASE=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value })
    $dbUser = ($envContent | Select-String "DB_USERNAME=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value })
    $dbPass = ($envContent | Select-String "DB_PASSWORD=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value })
    
    if ($dbName) {
        Write-Info "Exporting database: $dbName"
        $dumpFile = "revana-db-$(Get-Date -Format 'yyyyMMdd-HHmmss').sql"
        $dumpPath = Join-Path $env:TEMP $dumpFile
        
        # Export using mysqldump
        if ($dbPass) {
            mysqldump -u $dbUser -p$dbPass $dbName > $dumpPath
        } else {
            mysqldump -u $dbUser $dbName > $dumpPath
        }
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Database exported"
            
            # Upload database dump
            Write-Info "Upload database dump..."
            scp $dumpPath "${ServerUser}@${ServerHost}:/tmp/$dumpFile"
            if ($LASTEXITCODE -eq 0) {
                Write-Success "Database dump uploaded"
                $uploadedDbDump = $dumpFile
            }
            
            Remove-Item $dumpPath -Force
        } else {
            Write-Error "Database export gagal! Lanjut tanpa database..."
        }
    }
} else {
    Write-Info "Step 3: Skip database export (--SkipDatabase)"
}

# Step 4: Execute deployment on server
Write-Info "Step 4: Execute deployment di server..."

$deployScript = @"
#!/bin/bash
set -e

APP_DIR='$ServerPath'
ARCHIVE_NAME='$archiveName'
DB_DUMP='$uploadedDbDump'

echo '→ Creating backup...'
BACKUP_DIR='/var/backups/revana'
mkdir -p `$BACKUP_DIR
DATE=`$(date +%Y%m%d_%H%M%S)

# Backup current application
if [ -d "`$APP_DIR" ]; then
    tar -czf `$BACKUP_DIR/app_backup_`$DATE.tar.gz -C `$APP_DIR .
    echo '✓ Application backup created'
fi

echo '→ Enable maintenance mode...'
if [ -f "`$APP_DIR/artisan" ]; then
    cd `$APP_DIR
    php artisan down --message='Sistem sedang update' --retry=60 || true
fi

echo '→ Extract new files...'
mkdir -p `$APP_DIR
cd `$APP_DIR
unzip -o /tmp/`$ARCHIVE_NAME

echo '→ Restore .env from backup...'
if [ -f "`$BACKUP_DIR/.env.backup" ]; then
    cp `$BACKUP_DIR/.env.backup `$APP_DIR/.env
else
    # Backup current .env for future use
    if [ -f "`$APP_DIR/.env" ]; then
        cp `$APP_DIR/.env `$BACKUP_DIR/.env.backup
    fi
fi

echo '→ Install dependencies...'
composer install --optimize-autoloader --no-dev --no-interaction

if [ -f 'package.json' ]; then
    npm ci --production
    npm run build
fi

if [ -n "`$DB_DUMP" ] && [ -f "/tmp/`$DB_DUMP" ]; then
    echo '→ Import database...'
    DB_NAME=`$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USER=`$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_PASS=`$(grep DB_PASSWORD .env | cut -d '=' -f2)
    
    if [ -z "`$DB_PASS" ]; then
        mysql -u `$DB_USER `$DB_NAME < /tmp/`$DB_DUMP
    else
        mysql -u `$DB_USER -p`$DB_PASS `$DB_NAME < /tmp/`$DB_DUMP
    fi
    echo '✓ Database imported'
    rm /tmp/`$DB_DUMP
fi

echo '→ Run migrations...'
php artisan migrate --force

echo '→ Clear and optimize cache...'
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

composer dump-autoload --optimize

echo '→ Set permissions...'
chown -R www-data:www-data `$APP_DIR
chmod -R 755 `$APP_DIR
chmod -R 775 `$APP_DIR/storage
chmod -R 775 `$APP_DIR/bootstrap/cache

echo '→ Create storage link...'
php artisan storage:link || true

echo '→ Restart services...'
systemctl restart php8.1-fpm 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
systemctl restart nginx 2>/dev/null || systemctl restart apache2 2>/dev/null || true

echo '→ Disable maintenance mode...'
php artisan up

echo '→ Cleanup...'
rm /tmp/`$ARCHIVE_NAME

echo '✓ Deployment completed!'
echo ''
echo 'Backup tersimpan di: `$BACKUP_DIR/app_backup_`$DATE.tar.gz'
echo 'Untuk rollback: cd `$APP_DIR && tar -xzf `$BACKUP_DIR/app_backup_`$DATE.tar.gz'
"@

# Save script to temp file
$scriptPath = Join-Path $env:TEMP "deploy-script.sh"
$deployScript | Out-File -FilePath $scriptPath -Encoding utf8 -NoNewline

# Upload and execute script
scp $scriptPath "${ServerUser}@${ServerHost}:/tmp/deploy-script.sh"
ssh "${ServerUser}@${ServerHost}" "chmod +x /tmp/deploy-script.sh && sudo bash /tmp/deploy-script.sh && rm /tmp/deploy-script.sh"

Remove-Item $scriptPath -Force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "======================================" -ForegroundColor Cyan
    Write-Success "Deployment berhasil!"
    Write-Host "======================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Aplikasi dapat diakses di: https://$ServerHost" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Error "Deployment gagal! Check error messages di atas."
    Write-Host ""
    exit 1
}
