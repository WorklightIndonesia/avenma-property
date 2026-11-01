# Avenma Property

Website company profile dan katalog properti untuk Avenma Property. Website menampilkan katalog properti primer dan sekunder, detail properti, artikel blog, dan informasi perusahaan. Tidak ada transaksi, booking, atau pembayaran.

## Overview

- **Pengunjung:** melihat katalog properti, memfilter berdasarkan wilayah, tipe, harga, kamar, dan status; membaca detail properti dan artikel blog; menghubungi tim lewat tautan WhatsApp.
- **Admin:** semua akses Agen, ditambah pengelolaan akun, konten halaman, dan pengaturan situs.
- **Agen:** mengelola listing miliknya sendiri, artikel blog, dan developer.

Status saat ini: Phase 1–3 selesai dan Phase 4 siap deploy. Katalog properti, detail listing, blog, dan CMS admin sudah berjalan. Task 17 (bilingual ID/EN) ditunda dan di luar cakupan peluncuran. Lihat `tasks/todo.md` untuk acceptance criteria per task dan `tasks/plan.md` untuk rencana implementasi.

## Untuk Reviewer

Bagian ini untuk yang baru pertama kali memegang repo. Perintah lengkapnya ada di **Menjalankan Lokal dari Nol**; di sini jalur terpendek dan apa yang perlu dicek.

**Branch:** pekerjaan ada di `development`. `main` hanya berisi commit awal, jadi clone tanpa `-b development` memberi skeleton kosong.

### Lima menit pertama

Prasyarat: PHP 8.3 (atau minimal 8.2) dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd` dengan WebP, dan `exif`; Composer 2; Node.js 20.19 atau lebih baru.

```bash
git clone -b development https://github.com/WorklightIndonesia/avenma-property.git
cd avenma-property
composer install
npm install
cp .env.example .env        # PowerShell: Copy-Item .env.example .env
```

`.env.example` berisi nilai production. Timpa baris berikut agar lokal jalan:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
SESSION_SECURE_COOKIE=false
MAIL_MAILER=log
ADMIN_PASSWORD=GantiDengan1!
```

```bash
php artisan key:generate
touch database/database.sqlite   # PowerShell: New-Item database/database.sqlite
php artisan migrate --seed
composer run dev
```

Buka `http://127.0.0.1:8000`; CMS ada di `/login`.

### Akun uji

| Hal | Nilai |
|---|---|
| Email | `avenma.properti@gmail.com` |
| Password | Nilai `ADMIN_PASSWORD` yang Anda isi sendiri |
| Role | `admin` |

Registrasi publik dimatikan. Akun agen dibuat dari panel admin, dan role `agen` wajib punya nomor telepon.

### Yang perlu dicek

- `/properti`: 20 listing contoh, tombol filter yang membuka panel, dan chip filter setelah memilih satu opsi.
- Satu detail listing: galeri, peta, chip status dan `Baru`, tombol WhatsApp, dan Simulasi KPR (hanya listing `dijual`).
- `/blog`: 10 artikel bertanggal hari seeder dijalankan.
- `/kontak`, halaman 404, dan `/hubungi-kami` yang langsung mengarah ke WhatsApp nomor kantor.
- Login, lalu `/admin/listings`, `/admin/blog`, `/admin/pages`, `/admin/users`, `/admin/settings`, dan `/admin/pengaturan-listing` (developer serta opsi listing dikelola di sini).
- Buat satu listing memakai tiga picker wilayah; unggah satu foto. Bila `gd` dengan WebP aktif, muncul tiga berkas (asli, `-medium.webp`, `-thumb.webp`).
- Keluar, lalu buka `/admin/listings` lagi: harus diarahkan ke halaman login.
- `php artisan test`: 312 test lulus.

### Yang memang belum ada

- Bilingual ID/EN (Task 17). Seluruh antarmuka berbahasa Indonesia; kolom `_en` ada di database tetapi belum dipakai.
- Environment staging. Uji di lokal atau langsung di produksi.
- Dokumen audit di `.scratch/` tidak ikut repo.
- Lima developer dan 20 listing adalah data contoh, bukan isi katalog asli. Kembalikan dengan `php artisan migrate:fresh --seed`.

`composer run setup` bukan jalur lokal: skrip itu menyalin `.env.example` apa adanya (nilai production) lalu menjalankan migrasi ke MySQL.

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
│   ├── Console/Commands/           # images:variants (varian 480 dan 960 px)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/               # Controller auth dari Breeze
│   │   │   ├── Public/             # Beranda, katalog, detail, blog, sitemap
│   │   │   └── Admin/              # CMS: listing, blog, halaman, developer, akun, pengaturan
│   │   ├── Middleware/             # Role.php (admin/agen), SecurityHeaders.php
│   │   └── Requests/               # Validasi form admin
│   ├── Models/
│   ├── Providers/
│   └── Services/                   # ImageService: WebP, varian, orientasi EXIF
├── bootstrap/
│   └── app.php                     # Middleware, alias 'role', health check /up
├── config/
├── database/
│   ├── data/                       # wilayah.csv (7.285 kecamatan) dan generatornya
│   ├── migrations/                 # Schema users, listing, blog, halaman, KPR, opsi
│   ├── seeders/
│   └── factories/
├── public/
│   ├── build/                      # Hasil Vite, tidak di-commit
│   └── images/                     # Foto contoh dan unggahan admin (WebP)
├── resources/
│   ├── css/                        # Tailwind directives
│   ├── js/                         # Alpine.js bootstrap
│   └── views/
│       ├── layouts/                # Layout publik, admin, app, guest
│       ├── public/                 # Halaman publik (beranda, katalog, blog)
│       ├── admin/                  # Halaman CMS
│       ├── components/             # Komponen Blade reusable
│       ├── auth/                   # Login, reset password, verifikasi email
│       └── profile/                # Halaman profil admin
├── routes/
│   ├── web.php                     # Route publik, admin, sitemap, robots
│   └── auth.php                    # Route autentikasi
├── tests/
│   ├── Feature/                    # Admin/, Auth/, Public/, aksesibilitas
│   └── Unit/
├── tasks/                          # plan.md (rencana) dan todo.md (task list)
└── .scratch/                       # Catatan lokal, hanya di mesin pengembang
```

## Dependency Checklist

Pastikan semua dependensi berikut tersedia sebelum menjalankan proyek.

### Tools

- [ ] PHP 8.3 (minimal 8.2) dengan ekstensi `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd` dengan dukungan WebP, dan `exif`
- [ ] Composer 2.x
- [ ] Node.js >= 20.19 dan npm
- [ ] SQLite (lokal) atau MariaDB 10.5 (production)
- [ ] Git

`gd` mengubah unggahan menjadi WebP dan membuat varian 480/960 px; tanpa dukungan WebP, foto disimpan apa adanya. `exif` dibaca untuk memutar foto ponsel sesuai orientasinya. Cek keduanya dengan `php -m`.

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

1. Clone branch `development`:

   ```bash
   git clone -b development https://github.com/WorklightIndonesia/avenma-property.git
   cd avenma-property
   ```

   Branch `main` hanya berisi commit awal dan tidak berisi aplikasi ini.

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

4. Sesuaikan `.env` untuk lokal. File contoh berisi nilai production, jadi timpa:

   ```dotenv
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=sqlite
   SESSION_SECURE_COOKIE=false
   MAIL_MAILER=log
   ADMIN_PASSWORD=GantiDengan1!
   ```

   `ADMIN_PASSWORD` wajib diisi dengan aturan password akun: minimal 8 karakter, ada huruf besar dan kecil, angka, dan simbol. Tanpa nilai itu `AdminSeeder` berhenti dan data contoh tidak masuk. Variabel lain dijelaskan di **Setup Environment**.

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

   Hasilnya: 7.285 kecamatan resmi Kemendagri, pengaturan situs dan default KPR, halaman Beranda dan Kontak, 5 developer contoh, 20 listing contoh, 10 artikel blog bertanggal hari itu, dan satu akun admin `avenma.properti@gmail.com` dengan password dari `ADMIN_PASSWORD`.

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

8. Buka `http://127.0.0.1:8000`. CMS ada di `http://127.0.0.1:8000/login`.

Perintah PHP di atas mengasumsikan `php` sudah ada di PATH. Jika belum, lihat **Windows (XAMPP)**.

### Bila Ada yang Gagal

| Gejala | Solusi |
|---|---|
| `php` tidak dikenali | PHP belum ada di PATH. Lihat **Windows (XAMPP)**. |
| `Vite manifest not found` | Vite belum jalan. Jalankan `npm run dev` atau `npm run build`. |
| `ADMIN_PASSWORD belum diisi di .env` | Isi `ADMIN_PASSWORD` di `.env` (langkah 4), lalu ulangi langkah 6. |
| Database SQLite tidak ditemukan | File belum dibuat. Ulangi langkah 5. |
| `no such table: users` | Migrasi belum jalan. Ulangi langkah 6. |
| `Call to undefined function exif_read_data()` saat unggah foto | Ekstensi `exif` belum aktif. Aktifkan di `php.ini` lalu ulangi unggahan. |
| Hasil clone hanya skeleton Laravel, tanpa aplikasi | Clone memakai branch `main`. Ulangi dengan `git clone -b development`. |

## Setup Environment

Semua konfigurasi disalin dari `.env.example`. File `.env` tidak pernah di-commit. Langkah hariannya ada di **Menjalankan Lokal dari Nol**; bagian ini merinci variabel dan opsi database.

| Variabel | Nilai lokal | Keterangan |
|---|---|---|
| `APP_NAME` | `"Avenma Property"` | Nama aplikasi |
| `APP_ENV` | `local` | Gunakan `production` saat deploy |
| `APP_DEBUG` | `true` | Wajib `false` di production |
| `APP_URL` | `http://127.0.0.1:8000` | URL aplikasi; samakan dengan alamat yang dibuka |
| `APP_LOCALE` | `id` | Bahasa antarmuka dan pesan validasi |
| `DB_CONNECTION` | `sqlite` | Ganti ke `mysql` untuk MariaDB |
| `SESSION_DRIVER` | `database` | Tabel session dibuat oleh migrasi |
| `CACHE_STORE` | `database` | Tabel cache dibuat oleh migrasi |
| `QUEUE_CONNECTION` | `sync` | Tidak ada worker di shared hosting |
| `MAIL_MAILER` | `log` | Email ditulis ke log saat development; `smtp` di production |

Untuk MariaDB, buat database lalu isi `DB_*`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avenma_property
DB_USERNAME=root
DB_PASSWORD=
```

Seeder data contoh berjalan dengan `php artisan db:seed` setelah migrasi. Seeder membuat satu akun: `avenma.properti@gmail.com` (role `admin`, nama `Avenma Properti`). Password dibaca dari `ADMIN_PASSWORD` di `.env`; seeder berhenti bila kosong atau tidak memenuhi aturan (min 8 karakter, huruf besar dan kecil, angka, simbol). Registrasi publik dimatikan, jadi akun agen berikutnya dibuat lewat panel admin.

## Testing dan Build

```bash
# Test (312 test, 1816 assertion)
php artisan test

# Sama, lewat skrip Composer yang membersihkan cache config dulu
composer test

# Format kode
./vendor/bin/pint

# Build asset production
npm run build
```

## Deploy ke Hostinger

Semua perintah dijalankan lewat SSH, di dalam folder aplikasi di server.

### Sebelum deploy

- PHP 8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd` (dengan WebP), dan `exif`. Tanpa `exif` unggahan foto langsung gagal; tanpa WebP di `gd` foto disimpan apa adanya.
- Node.js 20.19 atau lebih baru, dipakai `npm run build` di server.
- Document root domain diarahkan ke folder `public/`.
- SSL aktif dan Force HTTPS menyala.

### Langkah deploy

1. Clone dan pasang dependensi tanpa paket development:

   ```bash
   git clone <url-repo> avenma-property
   cd avenma-property
   composer install --no-dev --optimize-autoloader
   ```

2. Siapkan `.env`: `cp .env.example .env`, lalu `php artisan key:generate`. Isi `APP_URL`, empat `DB_*`, `MAIL_PASSWORD`, dan `ADMIN_PASSWORD`. File contoh sudah memakai nilai production, termasuk `SESSION_SECURE_COOKIE=true`.

3. Build aset di server:

   ```bash
   npm ci
   npm run build
   ```

   Pastikan `public/build/manifest.json` ada. Folder ini tidak masuk git, jadi hasil clone selalu kosong.

4. Migrasi dan isi data:

   ```bash
   php artisan migrate --force
   php artisan db:seed --class=MasterDataSeeder --force
   php artisan db:seed --class=WilayahSeeder --force
   php artisan db:seed --class=PageContentSeeder --force
   php artisan db:seed --class=AdminSeeder --force
   php artisan db:seed --class=BlogPostSeeder --force
   ```

   Jangan `php artisan db:seed` tanpa `--class`: `DatabaseSeeder` ikut memasukkan lima developer dan 20 listing contoh. Hasil yang benar: 7.285 kecamatan, pengaturan situs, halaman Beranda dan Kontak, satu akun `avenma.properti@gmail.com`, dan sepuluh artikel blog bertanggal hari seed.

5. Izin folder dan cache:

   ```bash
   chmod -R 775 storage bootstrap/cache
   php artisan optimize
   ```

   `storage:link` tidak perlu: unggahan disimpan langsung di `public/` lewat disk `uploads`. `php artisan optimize` menjalankan `config:cache`, `route:cache`, dan `view:cache` sekaligus; jalankan di server, bukan di lokal, karena cache rute lokal membawa `/design-system`.

6. Uji SMTP sebelum menyerahkan akun:

   ```bash
   php artisan tinker --execute="Mail::raw('tes', fn(\$m) => \$m->to('avenma.properti@gmail.com')->subject('SMTP Avenma'));"
   ```

   Halaman Lupa Password tetap menampilkan "tautan sudah dikirim" walau SMTP salah, jadi uji ini satu-satunya bukti email benar-benar keluar. Setelah akun dibuat, kosongkan `ADMIN_PASSWORD` di `.env`.

7. Cek setelah deploy:

   - `/up` mengembalikan 200.
   - `/`, `/properti`, satu detail listing, `/blog`, dan `/kontak` tampil.
   - Login admin berhasil; unggah satu foto menghasilkan tiga berkas (asli, `-medium.webp`, `-thumb.webp`) dan tidak miring.
   - `/sitemap.xml` dan `/robots.txt` memakai domain asli.
   - Header `Cache-Control` aset mengikuti aturan di `public/.htaccess`.

### Rilis berikutnya

```bash
php artisan down --retry=60
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan up
```

Unggahan tinggal di `public/` sebagai berkas untracked, jadi `git pull` tidak menyentuhnya. Cadangkan database dari hPanel sebelum migrasi. Rollback berarti memulihkan berkas dan snapshot database, bukan `migrate:rollback` di data hidup.

### Antrean

`QUEUE_CONNECTION=sync` karena shared hosting tidak punya worker dan aplikasi ini belum memakai job. Bila suatu saat ada pekerjaan lambat, ubah ke `database` dan tambahkan cron:

```
*/5 * * * * cd ~/avenma-property && php artisan queue:work --stop-when-empty --max-time=250
```

