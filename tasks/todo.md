# Task List: Avenma Property Website

Detail plan: `tasks/plan.md`

## Phase 1: Foundation

- [x] **Task 1: Install Laravel 12 + Breeze**
  - Acceptance: `php artisan serve` jalan, `npm run build` sukses, Breeze auth route terpasang.
  - Verification: buka `/login` dan `/register`, build tanpa error.
  - Files: `composer.json`, `package.json`, `vite.config.js`, `.env.example`
  - Scope: Medium

- [x] **Task 2: Custom role middleware + redirect per role**
  - Acceptance: kolom `role` di `users` (`admin`, `agen`, default `agen`); kedua role diarahkan ke `/admin/dashboard`; role lain tidak bisa akses admin.
  - Verification: feature test login per role; manual login dua akun.
  - Files: `database/migrations/*_add_role_to_users.php`, `app/Http/Middleware/Role.php`, `app/Providers/AppServiceProvider.php`, `routes/web.php`
  - Depends: Task 1
  - Scope: Small

- [x] **Task 3: Base layout public & admin**
  - Acceptance: layout public (header, footer, nav responsive) dan admin (sidebar) render; Tailwind + Alpine ter-load.
  - Verification: buka satu halaman public dan satu halaman admin di mobile + desktop.
  - Files: `resources/views/layouts/public.blade.php`, `resources/views/layouts/admin.blade.php`, `resources/css/app.css`, `resources/js/app.js`
  - Depends: Task 1
  - Scope: Medium

- [x] **Task 4: Migrations master data**
  - Acceptance: tabel `categories`, `locations`, `developers`, `listings`, `blog_posts`, `page_contents`, `kpr_settings` terbuat dengan FK benar.
  - Verification: `php artisan migrate:fresh` sukses; cek schema di database.
  - Files: `database/migrations/*`
  - Depends: Task 1
  - Scope: Medium

### Checkpoint: Foundation
- [x] `php artisan migrate:fresh` sukses
- [x] Login/logout berfungsi, redirect per role benar
- [x] Layout public dan admin render tanpa error

## Phase 2: Property Catalog

- [x] **Task 5: Seeder master data + sample listings**
  - Acceptance: seeder membuat akun `admin` (Fellix) dan `agen` (Budi, nomor `089630999996`), data wilayah resmi (7.285 kecamatan Kemendagri), developer, satu baris `site_settings`, dan 20 listing contoh dengan foto. Password dibaca dari `ADMIN_PASSWORD` / `AGEN_PASSWORD` di `.env`; seeder gagal bila kosong atau tidak memenuhi aturan password.
  - Verification: `php artisan migrate:fresh --seed`; cek dua akun, 20 listing, data wilayah, dan `site_settings` di database.
  - Files: `database/seeders/*`
  - Depends: Task 4
  - Scope: Small

- [x] **Task 6: Public listing index + filter**
  - Acceptance: `/properti` menampilkan listing terbit; filter provinsi, kota/kabupaten, dan kecamatan (boleh hanya sebagian tingkat), tipe, harga (bucket), dan status; pilihan wilayah hanya yang punya listing terbit, lengkap dengan jumlah; urutan Terbaru, Harga terendah, Harga tertinggi; jumlah per halaman 6, 12, atau 24 (default 6). Panel filter tertutup sampai diklik; perubahan hanya berlaku setelah Terapkan; filter aktif tampil sebagai chip, tombol hapus chip langsung berlaku.
  - Verification: feature test tiap filter, kombinasi, sort, chip, dan pagination; manual cek di 390 px dan 1440 px.
  - Files: `app/Http/Controllers/Public/ListingController.php`, `app/Models/Listing.php`, `resources/views/public/listings/index.blade.php`, `_filters.blade.php`, `resources/views/components/property-card.blade.php`, `routes/web.php`
  - Depends: Task 5
  - Scope: Medium

- [x] **Task 7: Public listing detail**
  - Acceptance: `/properti/{slug}` menampilkan info, galeri, Google Maps embed, tombol WhatsApp dengan pesan otomatis berisi nama listing.
  - Verification: feature test route; manual cek link wa.me.
  - Files: `app/Http/Controllers/Public/ListingController.php`, `resources/views/public/listings/show.blade.php`
  - Depends: Task 6
  - Scope: Medium

- [x] **Task 8: Admin listing CRUD + upload foto**
  - Acceptance: `admin` dan `agen` bisa create listing (termasuk unggah foto dan label tiap foto), edit/delete; `agen` hanya listing miliknya sendiri, `admin` semua. `contact_phone` opsional dan otomatis terisi nomor agen pembuat. Validasi input. Konversi WebP menyusul di Task 14.
  - Verification: feature test CRUD dan upload; test `agen` tidak bisa mengubah listing milik agen lain.
  - Files: `app/Http/Controllers/Admin/ListingController.php`, `app/Http/Requests/ListingRequest.php`, `app/Policies/ListingPolicy.php`, `resources/views/admin/listings/*`
  - Depends: Task 5
  - Scope: Medium

- [x] **Task 9: Admin CRUD master data, akun, dan pengaturan situs**
  - Acceptance: CRUD developer (kedua role, delete ditolak selama masih dipakai listing). CRUD akun (`admin` saja): nama, email, telepon wajib untuk role `agen`, role, password sesuai aturan. Halaman pengaturan situs (`admin` saja) untuk nomor WhatsApp kantor. Wilayah dan kategori tidak lagi di-CRUD: `locations` berisi data resmi Kemendagri, kategori dihapus karena duplikat `property_type`.
  - Verification: feature test tiap CRUD; test `agen` ditolak di `/admin/users` dan `/admin/settings`; manual cek.
  - Files: `app/Http/Controllers/Admin/DeveloperController.php`, `UserController.php`, `SiteSettingController.php`, views
  - Depends: Task 5
  - Scope: Medium

### Checkpoint: Core Features
- [x] Filter listing berfungsi
- [x] Detail listing tampil lengkap
- [x] Admin CRUD berfungsi
- [x] `php artisan test` lulus

## Phase 3: Content Features

- [ ] **Task 10: Blog public + admin CRUD**
  - Acceptance: `/blog` dan `/blog/{slug}` tampil; `admin` dan `agen` CRUD artikel; `agen` hanya artikel miliknya sendiri; 10 artikel placeholder ter-seed.
  - Verification: feature test route dan CRUD; test `agen` tidak bisa mengubah artikel agen lain.
  - Files: `app/Http/Controllers/Public/BlogController.php`, `app/Http/Controllers/Admin/BlogPostController.php`, views, `database/seeders/BlogPostSeeder.php`
  - Depends: Task 4
  - Scope: Medium

- [x] **Task 11: KPR simulation**
  - Acceptance: form harga, DP, tenor, bunga; hasil cicilan per bulan benar (unit test).
  - Verification: unit test rumus anuitas; manual cek di halaman detail.
  - Files: `app/Support/KprCalculator.php`, view section di listing detail
  - Depends: Task 7
  - Scope: Small

- [ ] **Task 12: Static pages**
  - Acceptance: Home, Tentang Kami, Kontak, Syarat & Ketentuan tampil; konten dari `page_contents`.
  - Verification: feature test tiap route; manual cek.
  - Files: `app/Http/Controllers/Public/PageController.php`, `resources/views/public/pages/*`
  - Depends: Task 4
  - Scope: Medium

- [ ] **Task 13: Admin page content CRUD**
  - Acceptance: admin edit title, content, meta title, meta description per halaman.
  - Verification: feature test update; manual cek perubahan tampil di public.
  - Files: `app/Http/Controllers/Admin/PageContentController.php`, views
  - Depends: Task 12
  - Scope: Small

### Checkpoint: Content
- [ ] Blog tampil dan bisa dibaca
- [ ] KPR calculator benar
- [ ] Static pages dari database
- [ ] Admin edit konten berfungsi

## Phase 4: Polish & Deploy

- [ ] **Task 14: WebP image conversion**
  - Acceptance: upload foto dikonversi ke WebP, max 1920 px, max 5 MB.
  - Verification: feature test upload; cek file output.
  - Files: `app/Services/ImageService.php`, update `ListingController`
  - Depends: Task 8
  - Scope: Small

- [ ] **Task 15: Photo gallery lightbox**
  - Acceptance: galeri bisa dibuka fullscreen, navigasi next/prev, tutup dengan Esc.
  - Verification: manual cek di mobile + desktop.
  - Files: `resources/views/components/photo-gallery.blade.php`, `resources/js/app.js`
  - Depends: Task 7, 14
  - Scope: Small

- [ ] **Task 16: SEO meta tags + sitemap + robots.txt**
  - Acceptance: meta title/description dinamis per halaman; `/sitemap.xml` berisi listing, blog, static pages; `robots.txt` benar.
  - Verification: cek view-source, akses sitemap, validasi XML.
  - Files: `resources/views/components/meta.blade.php`, `app/Http/Controllers/Public/SitemapController.php`, `public/robots.txt`
  - Depends: Task 6, 10, 12
  - Scope: Small

- [ ] **Task 17: Bilingual ID/EN**
  - Acceptance: kolom `_id`/`_en`; language switcher; locale middleware; konten tampil sesuai bahasa.
  - Verification: feature test switch locale; manual cek dua bahasa.
  - Files: migrations update, `app/Http/Middleware/SetLocale.php`, `resources/views/components/language-switcher.blade.php`, lang files
  - Depends: Task 6, 10, 12
  - Scope: Large — pecah per resource saat implementasi

- [ ] **Task 18: Responsive & accessibility review**
  - Acceptance: semua halaman oke di 375/768/1440 px; alt text, label form, kontras.
  - Verification: DevTools device mode; Lighthouse accessibility.
  - Files: semua view
  - Depends: Task 6–17
  - Scope: Medium

- [ ] **Task 19: Hostinger deployment prep**
  - Acceptance: `.env.example` production-ready; checklist deploy; `npm run build` sukses.
  - Verification: build local, review checklist.
  - Files: `.env.example`, `AGENTS.md`
  - Depends: Task 1–18
  - Scope: Small

### Checkpoint: Complete
- [ ] Semua foto WebP
- [ ] Sitemap accessible
- [ ] Switch bahasa berfungsi
- [ ] Lighthouse > 80
- [ ] Build production sukses
- [ ] Ready deploy Hostinger
