# fix-production-env.ps1
# Script untuk memperbaiki .env production yang rusak
# Jalankan dari PowerShell: .\fix-production-env.ps1

param(
    [string]$ServerUser = "root",
    [string]$ServerHost = "portal.apji.org",
    [string]$AppDir     = "/var/www/revana"
)

function Write-Ok  { Write-Host "  [OK] $args" -ForegroundColor Green }
function Write-Err { Write-Host "  [!!] $args" -ForegroundColor Red }
function Write-Inf { Write-Host "  --> $args" -ForegroundColor Yellow }

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   Fix Production .env — portal.apji.org"    -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ── Step 1: Test SSH ─────────────────────────────────────────────────────────
Write-Inf "Tes koneksi SSH ke $ServerHost ..."
$sshTest = ssh -o ConnectTimeout=10 -o BatchMode=yes "${ServerUser}@${ServerHost}" "echo SSH_OK" 2>&1
if ($sshTest -notmatch "SSH_OK") {
    Write-Err "SSH gagal: $sshTest"
    Write-Host ""
    Write-Host "Pastikan:" -ForegroundColor White
    Write-Host "  1. Kamu sudah punya SSH key yang terdaftar di server"
    Write-Host "  2. Coba manual: ssh root@portal.apji.org"
    exit 1
}
Write-Ok "SSH berhasil"

# ── Step 2: Cek .env sekarang ────────────────────────────────────────────────
Write-Inf "Cek .env production saat ini ..."
$currentEnv = ssh "${ServerUser}@${ServerHost}" "cat $AppDir/.env 2>/dev/null | head -6" 2>&1
Write-Host "  .env saat ini:" -ForegroundColor Gray
$currentEnv | ForEach-Object { Write-Host "    $_" -ForegroundColor Gray }

$isLocalEnv = $currentEnv -match "127\.0\.0\.1|APP_ENV=local"
if (-not $isLocalEnv) {
    Write-Ok ".env production tampak sudah benar — tidak perlu diperbaiki"
    Write-Host ""
    Write-Host "Kalau masih ada masalah, coba clear cache:" -ForegroundColor Yellow
    Write-Host "  ssh root@portal.apji.org 'cd $AppDir && php artisan optimize:clear'"
    exit 0
}
Write-Err ".env berisi nilai lokal (DB/URL salah) — akan diperbaiki dari backup"

# ── Step 3: Cari backup .env yang benar ──────────────────────────────────────
Write-Inf "Mencari backup dengan .env production yang benar ..."

$findScript = @'
#!/bin/bash
BACKUP_DIR='/var/backups/revana'

echo "=== Backup tersedia ==="
ls -lt $BACKUP_DIR/app_backup_*.tar.gz 2>/dev/null | awk '{print $NF, $6, $7, $8}' | head -20

echo ""
echo "=== Mencari backup dengan .env production ==="
GOOD_BACKUP=""
for backup in $(ls -t $BACKUP_DIR/app_backup_*.tar.gz 2>/dev/null | sort); do
    env_content=$(tar -xzf "$backup" "./.env" --to-stdout 2>/dev/null)
    if [ $? -eq 0 ]; then
        app_url=$(echo "$env_content" | grep "^APP_URL=" | head -1)
        if echo "$app_url" | grep -qv "127\.0\.0\.1"; then
            echo "GOOD: $backup"
            echo "  $app_url"
            GOOD_BACKUP="$backup"
        else
            echo "BAD (local .env): $backup — $app_url"
        fi
    fi
done

if [ -n "$GOOD_BACKUP" ]; then
    echo ""
    echo "BEST_BACKUP:$GOOD_BACKUP"
fi
'@

$scriptPath = Join-Path $env:TEMP "find-good-backup.sh"
$findScript | Out-File -FilePath $scriptPath -Encoding utf8 -NoNewline
scp -q $scriptPath "${ServerUser}@${ServerHost}:/tmp/find-good-backup.sh"
$findResult = ssh "${ServerUser}@${ServerHost}" "bash /tmp/find-good-backup.sh && rm /tmp/find-good-backup.sh" 2>&1
Remove-Item $scriptPath -Force

Write-Host ""
$findResult | ForEach-Object { Write-Host "  $_" }
Write-Host ""

$goodBackup = ($findResult | Where-Object { $_ -match "^BEST_BACKUP:" }) -replace "BEST_BACKUP:", ""

if (-not $goodBackup) {
    Write-Err "Tidak ditemukan backup .env production yang benar."
    Write-Host ""
    Write-Host "Solusi manual — SSH ke server lalu edit .env:" -ForegroundColor Yellow
    Write-Host "  ssh root@portal.apji.org"
    Write-Host "  nano /var/www/revana/.env"
    Write-Host ""
    Write-Host "Nilai yang perlu diperbaiki dari .env lokal:" -ForegroundColor White
    Write-Host "  APP_ENV=production"
    Write-Host "  APP_DEBUG=false"
    Write-Host "  APP_URL=https://portal.apji.org   (atau URL yang benar)"
    Write-Host "  DB_HOST=localhost"
    Write-Host "  DB_PASSWORD=<password_mysql_production>"
    Write-Host ""
    Write-Host "Setelah edit, jalankan:"
    Write-Host "  cd /var/www/revana && php artisan optimize:clear && php artisan config:cache"
    exit 1
}

# ── Step 4: Restore .env dari backup terbaik ────────────────────────────────
Write-Inf "Restore .env dari: $goodBackup ..."
$confirm = Read-Host "  Lanjutkan restore? (yes/no)"
if ($confirm -ne "yes") {
    Write-Inf "Dibatalkan."
    exit 0
}

$restoreScript = @"
#!/bin/bash
set -e
APP_DIR='$AppDir'
BACKUP='$goodBackup'

echo '→ Backup .env rusak dulu ke /tmp/.env.broken.bak ...'
cp \$APP_DIR/.env /tmp/.env.broken.bak

echo '→ Extract .env dari backup ...'
tar -xzf "\$BACKUP" "./.env" --to-stdout > \$APP_DIR/.env

echo '→ Verifikasi ...'
grep -E "APP_URL|APP_ENV|DB_HOST|APP_DEBUG" \$APP_DIR/.env

echo '→ Clear dan rebuild cache ...'
cd \$APP_DIR
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo ''
echo 'SELESAI. .env production berhasil dipulihkan.'
"@

$restorePath = Join-Path $env:TEMP "restore-env.sh"
$restoreScript | Out-File -FilePath $restorePath -Encoding utf8 -NoNewline
scp -q $restorePath "${ServerUser}@${ServerHost}:/tmp/restore-env.sh"
ssh "${ServerUser}@${ServerHost}" "bash /tmp/restore-env.sh && rm /tmp/restore-env.sh"
Remove-Item $restorePath -Force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Ok "Berhasil! Coba akses https://portal.apji.org/login"
} else {
    Write-Err "Ada error saat restore. Cek output di atas."
}
