# SIM-MBG SPPG Sungai Selayur 1

**Sistem Informasi Manajemen Monitoring Pemenuhan Gizi (1.414 Porsi/Hari)**  
Satuan Pelayanan Pemenuhan Gizi (SPPG) Sungai Selayur 1 — Selaras SOP BGN (Badan Gizi Nasional) & Standar Keamanan Pangan HACCP.

---

## 📋 Ikhtisar Sistem

Aplikasi ini dikembangkan untuk mengelola dan memonitor seluruh rantai operasional program Makan Bergizi Gratis (MBG) secara transparan, akuntabel, dan real-time:
- **7 Role RBAC** (*Role-Based Access Control*): Hak akses dan alur kerja spesifik untuk masing-masing peran.
- **Siklus Validasi Menu Multi-Tahap**:
  1. Ahli Gizi menyusun draf menu & perhitungan AKG (Energi, Protein, Karbohidrat, Lemak, Serat).
  2. Akuntan memverifikasi kesesuaian anggaran pagu (Maks. Rp15.000 / porsi).
  3. Kepala Dapur memberikan persetujuan operasional (*Approval*).
- **HACCP & Keamanan Pangan**: Monitoring suhu pematangan inti masak (&ge; 75°C) & suhu simpan saji (&ge; 60°C).
- **Inspeksi APD Relawan Dapur**: Pengecekan masker, celemek, sarung tangan, penutup kepala, dan sanitasi berkala.
- **Distribusi & Uji Organoleptik**: Verifikasi serah terima porsi di sekolah mitra lengkap dengan pencatatan waktu konsumsi aman (maks. 4 jam).
- **Cetak Laporan Resmi BGN**: Ekspor format PDF Berita Acara, Excel Rekapitulasi Rinci, dan CSV Data Tabular tanpa meninggalkan sistem.

---

## 👥 Akun Default Pengujian (7 Role RBAC)

Semua akun menggunakan kata sandi standar untuk lingkungan pengujian:  
**Password Default: `password123`**

| No | Peran / Jabatan | Alamat Email | Hak Akses Utama |
|---|---|---|---|
| 1 | **Admin SPPG Pusat** | `admin@mbg-selayur1.id` | Akses penuh, manajemen RBAC & mitra, konfigurasi tema |
| 2 | **Ahli Gizi (Nutritionist)** | `gizi@mbg-selayur1.id` | Draf menu 1.414 porsi, perhitungan AKG & kalori |
| 3 | **Akuntan / Verifikator Biaya** | `akuntan@mbg-selayur1.id` | Verifikasi pagu anggaran, rincian biaya bahan baku |
| 4 | **Kepala Dapur (Head Chef)** | `dapur@mbg-selayur1.id` | Persetujuan draf menu, monitoring jadwal produksi harian |
| 5 | **Asisten Lapangan (QC / HACCP)** | `lapangan@mbg-selayur1.id` | Pencatatan suhu inti HACCP & inspeksi sanitasi/APD |
| 6 | **Tim Pengantar / Driver MBG** | `distribusi@mbg-selayur1.id` | Pengiriman box makanan ke 7 sekolah & posyandu |
| 7 | **Guru / PIC Sekolah Mitra** | `sdn01@mbg-selayur1.id` | Uji organoleptik, konfirmasi penerimaan, & tiket keluhan |

---

## 🚀 Panduan Menjalankan di Komputer Lokal (Local Setup)

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
👉 **`http://127.0.0.1:8000`**

---

## 🧪 Skenario Alur Pengujian Cepat

Untuk menguji alur kerja end-to-end selaras SOP BGN:
1. **Login Ahli Gizi** (`gizi@mbg-selayur1.id`):
   - Masuk ke menu **Perencanaan & Menu**.
   - Susun komposisi bahan baku & draf menu, ajukan ke tahap verifikasi pagu.
2. **Login Akuntan** (`akuntan@mbg-selayur1.id`):
   - Periksa rincian kalkulasi harga bahan baku.
   - Setujui status pagu jika &le; Rp15.000 / porsi.
3. **Login Kepala Dapur** (`dapur@mbg-selayur1.id`):
   - Berikan persetujuan (*Approval*) agar menu masuk antrean produksi dapur.
4. **Login Asisten Lapangan** (`lapangan@mbg-selayur1.id`):
   - Masuk ke **Cek Suhu (HACCP)** dan catat hasil uji suhu inti pematangan (&ge; 75°C).
   - Masuk ke **Cek APD Relawan** dan lakukan verifikasi kepatuhan atribut higienitas.
5. **Login Tim Distribusi** (`distribusi@mbg-selayur1.id`):
   - Catat keberangkatan armada pengantaran ke sekolah tujuan.
6. **Login Guru / PIC Sekolah** (`sdn01@mbg-selayur1.id`):
   - Masuk ke **Distribusi & Keluhan**, lakukan konfirmasi jumlah porsi tiba serta uji organoleptik (rasa, aroma, tekstur).
7. **Login Admin / Role Manapun**:
   - Masuk ke **Laporan Resmi (Cetak)** untuk mengekspor dokumen Berita Acara (PDF), Rekapitulasi Rinci (Excel), atau CSV tanpa pop-up baru.

---

## 🔒 Keamanan & Perlindungan Data
- `.env` dikecualikan secara ketat dari Git versioning (`.gitignore`).
- Proteksi CSRF (*Cross-Site Request Forgery*) pada seluruh formulir transaksi & logout.
- Enkripsi kata sandi menggunakan algoritma `Bcrypt`.
- Header keamanan HTTP terintegrasi (*X-Content-Type-Options*, *X-Frame-Options*, *X-XSS-Protection*).

---

&copy; 2026 SPPG Sungai Selayur 1. Dikelola untuk Program Pemenuhan Gizi Nasional.
