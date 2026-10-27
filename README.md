## Panduan Menjalankan di Komputer Lokal (Local Setup)

Ikuti langkah-langkah berikut untuk menjalankan sistem di lingkungan pengembang lokal:

### 1. Prasyarat Sistem
- **PHP** &ge; 8.2 (ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `bcmath`)
- **Composer** &ge; 2.x
- **MySQL** &ge; 8.0 atau **MariaDB** &ge; 10.4
- **Web Browser Modern** (Chrome, Edge, Firefox, Brave)

### 2. Kloning Repositori
```bash
git clone <URL_REPOSITORY_GITHUB>
cd AgnesBelajar
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
