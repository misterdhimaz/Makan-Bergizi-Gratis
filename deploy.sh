#!/bin/bash
# =====================================================================
# SCRIPT DEPLOYMENT OTOMATIS SIM-MBG SPPG SUNGAI SELAYUR 1
# Standar Produksi & Keamanan Badan Gizi Nasional (BGN)
# =====================================================================

set -e

echo "🚀 [1/7] Mengaktifkan Mode Pemeliharaan (Maintenance Mode)..."
php artisan down --render="errors::503" --secret="sppg-selayur1-bypass" || true

echo "📥 [2/7] Mengambil pembaruan kode terbaru (Git Pull)..."
if [ -d ".git" ]; then
    git pull origin main
fi

echo "📦 [3/7] Mengoptimasi Composer Dependencies (No Dev, Classmap Optimized)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "🗄️ [4/7] Menjalankan Migrasi Basis Data..."
php artisan migrate --force

echo "🔗 [5/7] Memastikan Symlink Storage Aktif..."
php artisan storage:link || true

echo "⚡ [6/7] Menghangatkan Cache Produksi (Config, Route, View, Event)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔒 [7/7] Memastikan Permission Direktori Aman..."
chmod -R 775 storage bootstrap/cache || true

echo "✅ Menonaktifkan Mode Pemeliharaan (Aplikasi Aktif Kembali)..."
php artisan up

echo "🎉 Deployment SIM-MBG SPPG Sungai Selayur 1 Selesai & Berhasil!"

