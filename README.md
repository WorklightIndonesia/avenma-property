# Avenma Property

Website company profile dan katalog properti untuk Avenma Property. Website menampilkan katalog properti primer dan sekunder, detail properti, artikel blog, dan informasi perusahaan. Tidak ada transaksi, booking, atau pembayaran.

## Overview

- **Pengunjung:** melihat katalog properti, memfilter berdasarkan wilayah, tipe, harga, kamar, dan status; membaca detail properti dan artikel blog; menghubungi tim lewat tautan WhatsApp.
- **Admin:** semua akses Agen, ditambah pengelolaan akun, konten halaman, dan pengaturan situs.
- **Agen:** mengelola listing miliknya sendiri, artikel blog, dan developer.

Status saat ini: Phase 1 (Foundation) selesai. Katalog, blog, dan CMS sedang dibangun bertahap. Lihat `tasks/todo.md` untuk daftar task dan `tasks/plan.md` untuk rencana implementasi.

## Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend | Laravel 12.70, PHP >= 8.2 | Production Hostinger memakai PHP 8.3 |
| Frontend | Blade, TailwindCSS 3, Alpine.js 3 | Tanpa React/Vue |
| Build tool | Vite 7 | `npm run dev` / `npm run build` |
| Auth | Laravel Breeze 2.4 | Role `admin` dan `agen` via middleware |
| Database | SQLite (lokal), MariaDB 10.5 (production) | Eloquent ORM |
| Testing | PHPUnit 11 | `php artisan test` |
| Code style | Laravel Pint | `./vendor/bin/pint` |
| Peta | Google Maps embed iframe | Tanpa API key |
| WhatsApp | Tautan `wa.me` | Tanpa API |

## Project Structure

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/               # Controller auth dari Breeze
│   │   │   ├── Public/             # Rencana Phase 2: controller halaman publik
│   │   │   └── Admin/              # Rencana Phase 2: controller CMS
│   │   └── Middleware/             # Role.php: cek role admin / agen
│   └── Models/
├── bootstrap/
│   └── app.php                     # Registrasi middleware dan alias 'role'
├── config/
├── database/
│   ├── migrations/                 # Schema users, listing, blog, halaman, KPR
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── css/                        # Tailwind directives
│   ├── js/                         # Alpine.js bootstrap
│   └── views/
│       ├── layouts/                # Layout publik, admin, app, guest
│       ├── public/                 # Halaman publik (home, katalog, blog)
│       ├── admin/                  # Halaman CMS
│       ├── components/             # Komponen Blade reusable
│       ├── auth/                   # Login, reset password, verifikasi email
│       └── profile/                # Halaman profil admin
├── routes/
│   ├── web.php                     # Route publik dan admin
│   └── auth.php                    # Route autentikasi
├── tests/
│   ├── Feature/                    # Feature test (auth, role, profil)
│   └── Unit/
├── tasks/                          # plan.md (rencana implementasi) dan todo.md (task list)
└── .scratch/                       # Catatan lokal (gitignored)
```

## Dependency Checklist

Pastikan semua dependensi berikut tersedia sebelum menjalankan proyek.

### Tools

- [ ] PHP >= 8.2 dengan ekstensi `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`
- [ ] Composer 2.x
- [ ] Node.js >= 20.19 dan npm
- [ ] SQLite (lokal) atau MariaDB 10.5 (production)
- [ ] Git

Ekstensi `gd` dengan dukungan WebP dibutuhkan untuk konversi foto. Tanpa ekstensi itu, unggahan disimpan apa adanya.

### Package Composer

| Package | Versi | Fungsi |
|---|---|---|
| `laravel/framework` | ^12.0 | Framework inti |
| `laravel/tinker` | ^2.10 | REPL untuk debugging |
| `laravel/breeze` (dev) | ^2.4 | Scaffolding auth |
| `laravel/pint` (dev) | ^1.24 | Formatter PSR-12 |
| `laravel/pail` (dev) | ^1.2 | Log viewer |
| `phpunit/phpunit` (dev) | ^11.5 | Testing |

### Package NPM

| Package | Versi | Fungsi |
|---|---|---|
| `tailwindcss` | ^3.1 | Utility CSS |
| `alpinejs` | ^3.4 | Reaktivitas frontend |
| `vite` | ^7.0 | Build tool |
| `laravel-vite-plugin` | ^2.0 | Integrasi Vite dan Laravel |
| `@tailwindcss/forms` | ^0.5 | Style form |

Jalankan `composer install` dan `npm install` untuk memasang semua package.

### Windows (XAMPP)

`php artisan serve` memakai PHP CLI dan SQLite. Tidak perlu menjalankan XAMPP Control Panel, Apache, atau MySQL.

**Opsi 1: tambahkan PHP ke PATH (sekali saja)**

1. Buka **Edit environment variables for your account**.
2. Pilih variabel **Path**, lalu **Edit → New**. Isi `C:\xampp\php` dan klik **OK** di semua dialog.

   Jangan membuat variabel baru bernama `C:\xampp\php`. Direktori itu harus menjadi salah satu entri di dalam **Path**.

3. Tutup semua terminal dan IDE, lalu buka terminal baru. Perubahan PATH hanya berlaku untuk proses baru.
4. Verifikasi:

   ```powershell
   where.exe php
   php -v
   ```

**Opsi 2: pakai path lengkap (tanpa mengubah PATH)**

```powershell
C:\xampp\php\php.exe artisan serve
C:\xampp\php\php.exe C:\xampp\php\composer.phar install
```

XAMPP hanya menyertakan `composer.phar`, bukan `composer.bat`. Tanpa wrapper, jalankan Composer lewat `php` seperti contoh di atas.

## Menjalankan Lokal dari Nol

Panduan ini dimulai dari folder kosong dan berakhir dengan situs serta CMS yang bisa dibuka. Perintah ditulis untuk PowerShell, dengan versi bash bila berbeda.

1. Clone repositori:

   ```bash
   git clone https://github.com/WorklightIndonesia/avenma-property.git
   cd avenma-property
   ```

2. Pasang dependensi:

   ```bash
   composer install
   npm install
   ```

3. Salin `.env` lalu buat application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   bash: `cp .env.example .env && php artisan key:generate`

4. Sesuaikan tiga nilai di `.env`:

   ```dotenv
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   ADMIN_PASSWORD=GantiDengan1!
   ```

   `ADMIN_PASSWORD` wajib diisi dengan aturan password akun: minimal 8 karakter, ada huruf besar, angka, dan simbol. Tanpa nilai itu `AdminSeeder` berhenti dan data contoh tidak masuk. Variabel lain dijelaskan di **Setup Environment**.

5. Buat file database SQLite:

   ```powershell
   New-Item database/database.sqlite
   ```

   bash: `touch database/database.sqlite`

   File ini di-ignore git, jadi tidak ikut hasil clone. Tanpa file ini migrasi berhenti untuk menanyakan pembuatan database.

6. Jalankan migrasi sekaligus isi data contoh:

   ```bash
   php artisan migrate --seed
   ```

   Hasilnya: 7.285 kecamatan resmi Kemendagri, 20 listing contoh, dan satu akun admin `fellixwl@gmail.com` dengan password dari `ADMIN_PASSWORD`.

7. Jalankan server development. Satu perintah untuk server, queue, log, dan Vite sekaligus:

   ```bash
   composer run dev
   ```

   Atau dua terminal terpisah. Terminal pertama:

   ```bash
   php artisan serve
   ```

   Terminal kedua:

   ```bash
   npm run dev
   ```

8. Buka `http://localhost:8000`. CMS ada di `http://localhost:8000/login`.

Perintah PHP di atas mengasumsikan `php` sudah ada di PATH. Jika belum, lihat **Windows (XAMPP)**.

### Bila Ada yang Gagal

| Gejala | Solusi |
|---|---|
| `php` tidak dikenali | PHP belum ada di PATH. Lihat **Windows (XAMPP)**. |
| `Vite manifest not found` | Vite belum jalan. Jalankan `npm run dev` atau `npm run build`. |
| `ADMIN_PASSWORD belum diisi di .env` | Isi `ADMIN_PASSWORD` di `.env` (langkah 4), lalu ulangi langkah 6. |
| Database SQLite tidak ditemukan | File belum dibuat. Ulangi langkah 5. |
| `no such table: users` | Migrasi belum jalan. Ulangi langkah 6. |

## Setup Environment

Semua konfigurasi disalin dari `.env.example`. File `.env` tidak pernah di-commit. Langkah hariannya ada di **Menjalankan Lokal dari Nol**; bagian ini merinci variabel dan opsi database.

| Variabel | Nilai lokal | Keterangan |
|---|---|---|
| `APP_NAME` | `"Avenma Property"` | Nama aplikasi |
| `APP_ENV` | `local` | Gunakan `production` saat deploy |
| `APP_DEBUG` | `true` | Wajib `false` di production |
| `APP_URL` | `http://localhost:8000` | URL aplikasi |
| `APP_LOCALE` | `id` | Bahasa antarmuka dan pesan validasi |
| `DB_CONNECTION` | `sqlite` | Ganti ke `mysql` untuk MariaDB |
| `SESSION_DRIVER` | `database` | Tabel session dibuat oleh migrasi |
| `CACHE_STORE` | `database` | Tabel cache dibuat oleh migrasi |
| `QUEUE_CONNECTION` | `database` | Tabel jobs dibuat oleh migrasi |
| `MAIL_MAILER` | `log` | Email ditulis ke log saat development |

Untuk MariaDB, buat database lalu isi `DB_*`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avenma_property
DB_USERNAME=root
DB_PASSWORD=
```

Seeder data contoh berjalan dengan `php artisan db:seed` setelah migrasi. Seeder membuat satu akun: `fellixwl@gmail.com` (role `admin`). Password dibaca dari `ADMIN_PASSWORD` di `.env`; seeder berhenti bila kosong atau tidak memenuhi aturan (min 8 karakter, huruf besar, angka, simbol). Registrasi publik dimatikan, jadi akun agen berikutnya dibuat lewat panel admin.

## Testing dan Build

```bash
# Test
php artisan test

# Format kode
./vendor/bin/pint

# Build asset production
npm run build
```

## Deploy ke Hostinger

1. Set `.env` production:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=nama_database
   DB_USERNAME=user_database
   DB_PASSWORD=password_database
   ```

2. Jalankan build dan migrasi:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   ```

3. Bersihkan dan cache konfigurasi:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

   Jalankan ketiganya di server. Cache rute yang dibuat di lokal ikut membawa rute `/design-system`, yang seharusnya hanya ada di luar produksi.

4. Arahkan document root Hostinger ke folder `public/`.
