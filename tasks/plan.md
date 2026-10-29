# Implementation Plan: Avenma Property Website

## Overview

Website company profile dan katalog properti Avenma Property. Laravel 12 + Blade + TailwindCSS + Alpine.js, deploy di Hostinger Business. Empat fase vertical slice: Foundation → Property Catalog → Content Features → Polish & Deploy.

Spec: `.scratch/specs/Avenma_Property_SPEC.md`
Basic Memory: `Avenma_Implementation_Plan` (project `Avenma_Memory`)

## Architecture Decisions

- Custom `role` column di `users`, bukan Spatie — hanya 2 role, hemat dependency di shared hosting.
- Role `admin` dan `agen`: agen mengelola listing miliknya sendiri, artikel blog, serta kategori/lokasi/developer. Halaman, pengaturan situs, dan akun hanya untuk `admin`. Default kolom `agen`.
- GD bawaan PHP untuk konversi WebP (max 1920 px, upload max 5 MB); tanpa package tambahan.
- Google Maps embed iframe tanpa API key.
- wa.me deep link tanpa API.
- Sitemap manual (`/sitemap.xml`), bukan package — cukup untuk basic SEO.
- Bilingual pakai kolom `_id`/`_en`; MariaDB 10.5 tidak ideal untuk JSON column.

## Task List

### Phase 1: Foundation
- [ ] Task 1: Install Laravel 12 + Breeze, Vite + Tailwind
- [ ] Task 2: Custom role middleware + redirect per role
- [ ] Task 3: Base layout public & admin
- [ ] Task 4: Migrations master data

### Checkpoint: Foundation
- [ ] `php artisan migrate:fresh` sukses
- [ ] Login/logout berfungsi, redirect per role benar
- [ ] Layout public dan admin render tanpa error

### Phase 2: Property Catalog
- [ ] Task 5: Seeder master data + sample listings
- [ ] Task 6: Public listing index + filter + pagination
- [ ] Task 7: Public listing detail (info, galeri, map, WhatsApp CTA)
- [ ] Task 8: Admin listing CRUD + upload foto
- [ ] Task 9: Admin CRUD kategori, lokasi, developer, akun, dan pengaturan situs

### Checkpoint: Core Features
- [ ] Filter listing berfungsi
- [ ] Detail listing tampil lengkap
- [ ] Admin CRUD berfungsi
- [ ] `php artisan test` lulus untuk route listing

### Phase 3: Content Features
- [ ] Task 10: Blog public + admin CRUD, seed 10 artikel
- [ ] Task 11: KPR simulation
- [ ] Task 12: Static pages (Home, Tentang Kami, Kontak, S&K)
- [ ] Task 13: Admin page content CRUD

### Checkpoint: Content
- [ ] Blog tampil dan bisa dibaca
- [ ] KPR calculator benar
- [ ] Static pages dari database
- [ ] Admin edit konten berfungsi

### Phase 4: Polish & Deploy
- [x] Task 14: WebP image conversion
- [x] Task 15: Photo gallery lightbox
- [x] Task 16: SEO meta tags + sitemap + robots.txt
- [ ] Task 17: Bilingual ID/EN
- [ ] Task 18: Responsive & accessibility review
- [ ] Task 19: Hostinger deployment prep

### Checkpoint: Complete
- [x] Semua foto WebP
- [x] Sitemap accessible
- [ ] Switch bahasa berfungsi
- [ ] Lighthouse > 80
- [ ] Build production sukses
- [ ] Ready deploy Hostinger

## Risks and Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Hostinger 3GB RAM / 2 CPU | High | WebP + lazy load, tanpa package berat, cache config/view |
| Bilingual banyak kolom | Medium | Suffix `_id`/`_en`; hindari JSON column |
| 10 artikel blog belum ada | Medium | Placeholder terstruktur, ganti tanpa ubah schema |
| Tidak ada mockup UI | Medium | Referensi Tailwind UI/Flowbite; approval per fase |

## Open Questions

1. Nomor WhatsApp tujuan CTA — sementara placeholder `087736786969` di `site_settings`, ganti lewat panel pengaturan sebelum hosting.
2. Daftar developer yang akan dimasukkan.
3. Ketersediaan 10 artikel blog.
4. Domain final untuk `APP_URL` dan sitemap.
5. Brand color/kit Avenma.
6. Keyword SEO prioritas.
