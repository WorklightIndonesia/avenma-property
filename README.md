# Avenma Property

Website company profile dan katalog properti untuk Avenma Property. Website menampilkan katalog properti primer dan sekunder, detail properti, artikel blog, dan informasi perusahaan. Tidak ada transaksi, booking, atau pembayaran.

## Overview

- **Pengunjung:** melihat katalog properti, memfilter berdasarkan kategori, lokasi, developer, tipe, harga, kamar, dan status; membaca detail properti dan artikel blog; menghubungi tim lewat tautan WhatsApp.
- **Admin:** semua akses Agen, ditambah pengelolaan akun, konten halaman, dan pengaturan situs.
- **Agen:** mengelola listing miliknya sendiri, artikel blog, serta kategori, lokasi, dan developer.

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

Ekstensi `gd` atau `imagick` dibutuhkan saat konversi foto ke WebP (Phase 4).

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

## Setup Environment

Semua konfigurasi disalin dari `.env.example`. File `.env` tidak pernah di-commit.

1. Salin file contoh:

   ```bash
   cp .env.example .env
   ```

   PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

2. Generate application key:

   ```bash
   php artisan key:generate
   ```

3. Sesuaikan nilai penting di `.env`:

   | Variabel | Nilai lokal | Keterangan |
   |---|---|---|
   | `APP_NAME` | `"Avenma Property"` | Nama aplikasi |
   | `APP_ENV` | `local` | Gunakan `production` saat deploy |
   | `APP_DEBUG` | `true` | Wajib `false` di production |
   | `APP_URL` | `http://localhost:8000` | URL aplikasi |
   | `APP_LOCALE` | `en` | Bilingual ID/EN menyusul di Phase 4 |
   | `DB_CONNECTION` | `sqlite` | Ganti ke `mysql` untuk MariaDB |
   | `SESSION_DRIVER` | `database` | Tabel session dibuat oleh migrasi |
   | `CACHE_STORE` | `database` | Tabel cache dibuat oleh migrasi |
   | `QUEUE_CONNECTION` | `database` | Tabel jobs dibuat oleh migrasi |
   | `MAIL_MAILER` | `log` | Email ditulis ke log saat development |

4. Untuk SQLite, pastikan file database tersedia:

   ```bash
   touch database/database.sqlite
   ```

   PowerShell:

   ```powershell
   New-Item database/database.sqlite
   ```

5. Untuk MariaDB, buat database lalu isi `DB_*`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=avenma_property
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Jalankan migrasi:

   ```bash
   php artisan migrate
   ```

   Untuk data contoh lokal:

   ```bash
   php artisan db:seed
   ```

   Seeder membuat dua akun: `fellixwl@gmail.com` (role `admin`) dan `agenbudi@gmail.com` (role `agen`). Password dibaca dari `ADMIN_PASSWORD` dan `AGEN_PASSWORD` di `.env`; seeder berhenti bila kosong atau tidak memenuhi aturan (min 8 karakter, huruf besar, angka, simbol). Registrasi publik dimatikan, jadi akun baru dibuat lewat panel admin.

## Menjalankan Proyek Secara Lokal

1. Pasang dependensi:

   ```bash
   composer install
   npm install
   ```

2. Siapkan `.env` seperti pada bagian **Setup Environment**.

3. Jalankan migrasi:

   ```bash
   php artisan migrate
   ```

   Jika `php` tidak dikenali di Windows, lihat bagian **Windows (XAMPP)**.

4. Jalankan server development. Pilih salah satu:

   **Dua terminal terpisah:**

   ```bash
   php artisan serve
   ```

   ```bash
   npm run dev
   ```

   **Satu perintah (server, queue, log, dan Vite sekaligus):**

   ```bash
   composer run dev
   ```

5. Buka `http://localhost:8000`.

6. Login CMS di `http://localhost:8000/login`.

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

4. Arahkan document root Hostinger ke folder `public/`.
