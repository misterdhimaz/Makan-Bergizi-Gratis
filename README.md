## Panduan Menjalankan di Komputer Lokal (Local Setup)

Ikuti langkah-langkah berikut untuk menjalankan sistem di lingkungan pengembang lokal:

### 1. Prasyarat Sistem
- **PHP** &ge; 8.2 (ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `bcmath`)
- **Composer** &ge; 2.x
- **MySQL** &ge; 8.0 atau **MariaDB** &ge; 10.4
- **Web Browser Modern** (Chrome, Edge, Firefox, Brave)

### 2. Kloning Repositori
```bash
git clone https://github.com/misterdhimaz/Makan-Bergizi-Gratis.git
cd Makan-Bergizi-Gratis.git
```

### 3. Instalasi Dependensi PHP
```bash
composer install
```

### 4. Konfigurasi Lingkungan (`.env`)
Salin berkas contoh konfigurasi ke `.env`:
```bash
cp .env.example .env
```
Buka berkas `.env` dan sesuaikan pengaturan database lokal Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbg_db
DB_USERNAME=root
DB_PASSWORD=
```
*Catatan: Pastikan database dengan nama tersebut sudah dibuat di MySQL (`CREATE DATABASE mbg_db;`).*

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Migrasi Database & Seeding Data Awal
Jalankan migrasi lengkap beserta data seeder awal (1.414 porsi, bahan baku, data HACCP, dan akun 7 role):
```bash
php artisan migrate:fresh --seed
```

### 7. Buat Symlink Storage
```bash
php artisan storage:link
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi kini dapat diakses melalui browser di alamat:  
**`http://127.0.0.1:8000`**

### 9. Utilitas Backup & Restore MySQL (`.env`)
Fitur **Backup & Restore** pada panel Administrator Sistem menggunakan utilitas `mysqldump` dan `mysql`. Atur path biner pada berkas `.env` (contoh untuk Windows Laragon):
```env
MYSQLDUMP_PATH="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe"
MYSQL_PATH="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe"
```
Berkas backup disimpan di disk privat (`storage/app/private/backups`) dan mengecualikan tabel `audit_logs`, `system_backups`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, serta `failed_jobs`.

### 10. Menjalankan Scheduler Backup Harian di Windows
Backup otomatis dijadwalkan setiap hari pukul **02:00 WIB (`Asia/Jakarta`)** melalui perintah `mbg:backup-daily`.
- **Untuk Demo / Pengembangan:** jalankan di terminal terpisah:
  ```bash
  php artisan schedule:work
  ```
- **Untuk Produksi di Windows (Task Scheduler):** buat tugas terjadwal yang berjalan **setiap 1 menit** dengan aksi:
  - Program/script: `php`
  - Arguments: `artisan schedule:run`
  - Start in: `<path-folder-proyek>`

## 11. Menjalankan Uji End-to-End (Playwright) Secara Terisolasi

Uji browser E2E menggunakan basis data terpisah (`mbg_e2e`) agar tidak menyentuh basis data pengembangan (`sim_mbg_selayur`). Kata sandi admin uji dibaca dari variabel lingkungan `E2E_ADMIN_PASSWORD` (jika tidak diisi, `global-setup.js` akan menghasilkan kata sandi acak sementara untuk sesi uji tersebut).

```powershell
# Opsional: tentukan kata sandi uji melalui environment variable
$env:E2E_ADMIN_PASSWORD = "KataSandiUjiSementara123!"
$env:E2E_DB_DATABASE = "mbg_e2e"

npx playwright test
```

Laporan dan artefak uji (`test-results/`, `playwright-report/`) diabaikan oleh Git melalui `.gitignore`. Screenshot bukti QA tersimpan di `docs/qa-admin/`.
