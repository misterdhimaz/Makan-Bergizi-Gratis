# 🛡️ PANDUAN LENGKAP DEPLOYMENT & HARDENING KEAMANAN
## SISTEM INFORMASI MANAJEMEN MBG (SIM-MBG) — SPPG SUNGAI SELAYUR 1
**Standar Pelayanan Pemenuhan Gizi — Badan Gizi Nasional (BGN) Republik Indonesia**

---

### 1. Prasyarat Server Produksi (Production Requirements)
- **Sistem Operasi**: Linux Ubuntu 22.04 / 24.04 LTS (Direkomendasikan), Debian, atau AlmaLinux
- **Web Server**: Nginx (direkomendasikan) atau Apache 2.4+
- **PHP**: Versi **8.2** atau **8.3**
  - Ekstensi PHP wajib: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `gd`
- **Database**: MySQL 8.0+ atau MariaDB 10.5+
- **Composer**: Versi 2.7+
- **SSL Certificate**: Let's Encrypt (HTTPS Wajib untuk integritas data gizi)

---

### 2. Langkah-Langkah Deployment Pertama Kali (Initial Setup)

#### Langkah 1: Kloning / Unggah Proyek
```bash
cd /var/www
git clone <URL_REPOSITORY_ANDA> sim-mbg-selayur
cd sim-mbg-selayur
```

#### Langkah 2: Konfigurasi Environment Produksi (.env)
Salin template konfigurasi resmi:
```bash
cp .env.example .env
```
Buka berkas `.env` dan sesuaikan parameter kritis:
```ini
APP_NAME="SIM-MBG SPPG Sungai Selayur 1"
APP_ENV=production
APP_DEBUG=false               # PENTING: Wajib FALSE agar tidak membocorkan credential saat error
APP_URL=https://domain-anda.id # Ganti dengan domain HTTPS resmi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sim_mbg_selayur
DB_USERNAME=mbg_db_user
DB_PASSWORD=Password_Sangat_Kuat_123!

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true    # Wajib TRUE jika menggunakan HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

#### Langkah 3: Generate Application Key
```bash
php artisan key:generate
```

#### Langkah 4: Instalasi Dependensi Tanpa Paket Dev
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

#### Langkah 5: Migrasi Basis Data & Data Awal
```bash
php artisan migrate --force
# (Opsional untuk instalasi baru) Jalankan seeder master 1.414 kuota & 7 akun:
php artisan db:seed --force
```

#### Langkah 6: Symlink Storage Publik
Pastikan folder storage terhubung agar logo, background, dan surat jalan dapat diakses:
```bash
php artisan storage:link
```

#### Langkah 7: Pengaturan Hak Akses (Permissions)
Beri hak akses tulis hanya kepada user web server (`www-data`):
```bash
sudo chown -R www-data:www-data /var/www/sim-mbg-selayur
sudo find /var/www/sim-mbg-selayur -type f -exec chmod 644 {} \;
sudo find /var/www/sim-mbg-selayur -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/sim-mbg-selayur/storage
sudo chmod -R 775 /var/www/sim-mbg-selayur/bootstrap/cache
```

#### Langkah 8: Optimasi Kecepatan & Cache Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

### 3. Konfigurasi Nginx & Sertifikat SSL Gratis (Certbot)

1. Buat berkas konfigurasi Nginx:
   ```bash
   sudo nano /etc/nginx/sites-available/sim-mbg
   ```
   *(Salin isi dari berkas `deployment/nginx.conf.example`)*
2. Aktifkan konfigurasi dan uji sintaks:
   ```bash
   sudo ln -s /etc/nginx/sites-available/sim-mbg /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```
3. Pasang SSL Let's Encrypt secara otomatis:
   ```bash
   sudo apt install certbot python3-certbot-nginx -y
   sudo certbot --nginx -d domain-anda.id
   ```

---

### 4. Otomatisasi Deployment Selanjutnya
Setiap kali ada pembaruan kode baru, jalankan satu perintah:
```bash
bash deploy.sh
```
Script ini akan:
1. Mengaktifkan halaman pemeliharaan ramah pengguna (`errors::503`).
2. Melakukan `git pull`.
3. Memperbarui dependensi & migrasi basis data.
4. Menghangatkan cache produksi.
5. Membuka kembali aplikasi secara instan.

---

### 5. Daftar Periksa Keamanan (Security Checklist)
- [x] **APP_DEBUG = false**: Mencegah kebocoran struktur kode, query SQL, dan password di layar pengguna.
- [x] **Rate Limiter Anti-Bruteforce**: Endpoint `/login` dilindungi maksimal 5 percobaan per menit per IP.
- [x] **HTTP Security Headers Aktif**: Proteksi `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, dan HSTS.
- [x] **Blokir File Sensitif**: Nginx dan Apache (`.htaccess`) dikonfigurasi menolak akses langsung ke `.env`, `.git`, dan folder log.
- [x] **Halaman Error Kustom**: Tersedia tampilan resmi untuk error 403, 404, 419, 500, dan 503 tanpa jejak error server teknis.
- [x] **Session Cookie Secure**: `SESSION_SECURE_COOKIE=true` dan `SESSION_HTTP_ONLY=true` mencegah pencurian cookie sesi via skrip XSS.
- [x] **Sanitasi File Upload**: Validasi ekstensi MIME terdaftar ketat (PNG, JPG, PDF) dengan pembatasan ukuran maksimal.

