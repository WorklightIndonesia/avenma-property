# Task List: Avenma Property Website

Detail plan: `tasks/plan.md`

## Phase 1: Foundation

- [x] **Task 1: Install Laravel 12 + Breeze**
  - Acceptance: `php artisan serve` jalan, `npm run build` sukses, Breeze auth route terpasang.
  - Verification: buka `/login` dan `/register`, build tanpa error.
  - Files: `composer.json`, `package.json`, `vite.config.js`, `.env.example`
  - Scope: Medium

- [x] **Task 2: Custom role middleware + redirect per role**
  - Acceptance: kolom `role` di `users`; user `super_admin` dan `admin` diarahkan ke `/admin/dashboard`; user biasa tidak bisa akses admin.
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

- [ ] **Task 5: Seeder master data + sample listings**
  - Acceptance: seeder membuat 1 super admin, 1 admin, kategori, lokasi, developer, dan 5–10 listing contoh.
  - Verification: `php artisan migrate:fresh --seed`; cek data di database.
  - Files: `database/seeders/*`
  - Depends: Task 4
  - Scope: Small

- [ ] **Task 6: Public listing index + filter**
  - Acceptance: `/properti` menampilkan listing; filter kategori, lokasi, developer, tipe, harga, kamar, status berfungsi; pagination.
  - Verification: feature test filter; manual cek kombinasi filter.
  - Files: `app/Http/Controllers/Public/ListingController.php`, `resources/views/public/listings/index.blade.php`, `routes/web.php`
  - Depends: Task 5
  - Scope: Medium

- [ ] **Task 7: Public listing detail**
  - Acceptance: `/properti/{slug}` menampilkan info, galeri, Google Maps embed, tombol WhatsApp dengan pesan otomatis berisi nama listing.
  - Verification: feature test route; manual cek link wa.me.
  - Files: `app/Http/Controllers/Public/ListingController.php`, `resources/views/public/listings/show.blade.php`
  - Depends: Task 6
  - Scope: Medium

- [ ] **Task 8: Admin listing CRUD + upload foto**
  - Acceptance: admin bisa create/edit/delete listing; foto ter-upload ke `public/uploads`; validasi input.
  - Verification: feature test CRUD; manual upload foto.
  - Files: `app/Http/Controllers/Admin/ListingController.php`, `app/Http/Requests/*`, `resources/views/admin/listings/*`
  - Depends: Task 5
  - Scope: Medium

- [ ] **Task 9: Admin CRUD master data**
  - Acceptance: CRUD categories, locations, developers berfungsi.
  - Verification: feature test tiap CRUD; manual cek.
  - Files: `app/Http/Controllers/Admin/CategoryController.php`, `LocationController.php`, `DeveloperController.php`, views
  - Depends: Task 5
  - Scope: Medium

### Checkpoint: Core Features
- [ ] Filter listing berfungsi
- [ ] Detail listing tampil lengkap
- [ ] Admin CRUD berfungsi
- [ ] `php artisan test` lulus

## Phase 3: Content Features

- [ ] **Task 10: Blog public + admin CRUD**
  - Acceptance: `/blog` dan `/blog/{slug}` tampil; admin CRUD artikel; 10 artikel placeholder ter-seed.
  - Verification: feature test route dan CRUD.
  - Files: `app/Http/Controllers/Public/BlogController.php`, `app/Http/Controllers/Admin/BlogPostController.php`, views, `database/seeders/BlogPostSeeder.php`
  - Depends: Task 4
  - Scope: Medium

- [ ] **Task 11: KPR simulation**
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
