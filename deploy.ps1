# =====================================================================
# SCRIPT DEPLOYMENT OTOMATIS (WINDOWS POWERSHELL)
# SIM-MBG SPPG SUNGAI SELAYUR 1
# =====================================================================

Write-Host "🚀 [1/6] Mengaktifkan Mode Pemeliharaan..." -ForegroundColor Cyan
php artisan down --render="errors::503" --secret="sppg-selayur1-bypass"

Write-Host "📦 [2/6] Mengoptimasi Composer Dependencies..." -ForegroundColor Cyan
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

Write-Host "🗄️ [3/6] Menjalankan Migrasi Basis Data..." -ForegroundColor Cyan
php artisan migrate --force

Write-Host "🔗 [4/6] Memastikan Symlink Storage Aktif..." -ForegroundColor Cyan
php artisan storage:link

Write-Host "⚡ [5/6] Menghangatkan Cache Produksi (Config, Route, View)..." -ForegroundColor Cyan
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "✅ [6/6] Menonaktifkan Mode Pemeliharaan (Aplikasi Aktif)..." -ForegroundColor Green
php artisan up

Write-Host "🎉 Deployment SIM-MBG Berhasil Selesai!" -ForegroundColor Green

