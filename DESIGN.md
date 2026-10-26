# Design System — Avenma Property

Dokumen ini adalah kontrak visual untuk situs Avenma Property. Nilai di sini harus sama dengan `tailwind.config.js`; bila berbeda, config yang menang dan dokumen ini yang diperbaiki.

Pemakaian: setiap halaman dan komponen baru mengacu ke token dan aturan di bawah. Aturan tanpa angka bukan aturan.

## 1. Ide brand

Tiga atribut yang diturunkan dari logo: **mapan, hangat, presisi**.

- **Mapan** — properti primer dan sekunder untuk pembeli serius. Tata letak tenang, tanpa gimmick.
- **Hangat** — emas dan foto properti memberi kehangatan; sisanya netral.
- **Presisi** — sudut tajam, garis tipis, angka rata. Tidak ada bentuk lunak.

Konsekuensi: emas adalah satu-satunya elemen hangat di halaman. Bila sebuah layar terasa "ramai", biasanya karena emas dipakai di lebih dari tiga tempat.

## 2. Logo

Aset di `public/images/brand/`. Sumber asli (JPEG 1600×900) di `resources/brand/avenma-logo-source.jpg` dan tidak disajikan ke web.

| File | Isi | Pakai untuk |
|---|---|---|
| `avenma-logo-full.png` | lockup penuh, transparan, 696×593 | hero, halaman tentang, OG image, cetak |
| `avenma-logo-mark.png` | mark tanpa wordmark, 696×499 | kartu besar, OG image |
| `avenma-logo-wordmark.png` | wordmark saja, 696×92 | footer sempit, dokumen |
| `avenma-mark-ink.png` / `avenma-mark-paper.png` | mark satu warna, 696×499 | watermark, latar gelap, kartu besar |
| `avenma-logo-mark-112.png` / `avenma-mark-paper-112.png` | mark 112×80 | header, footer, sidebar (tampil 40–56 px) |
| `avenma-wordmark-ink.png` / `avenma-wordmark-paper.png` | wordmark satu warna | teks merek di latar gelap |
| `favicon-512.png` | mark penuh di tile `paper` | favicon, apple-touch-icon |

Aturan:

- Lockup penuh hanya di atas `paper`, `sand`, atau foto terang. Jangan di atas `ink`.
- Di latar `ink`, pakai varian `-paper` (satu warna) atau tile `paper`.
- Header, footer, dan sidebar memakai mark + nama bertipe Plus Jakarta Sans, bukan wordmark PNG, agar tetap tajam di 18 px.
- Wordmark PNG hanya untuk lebar ≥ 240 px (tinggi ≥ 32 px). Di bawah itu detail atap menyatu dengan huruf dan terbaca tebal atau kabur; pakai mark + teks.
- Lebar minimum lockup penuh 160 px; mark minimum 32 px; wordmark minimum 120 px.
- Ruang kosong di sekeliling logo minimal setinggi x-height wordmark.
- Jangan memotong, memutar, memberi bayangan, atau mengubah warna logo.

Batasan yang diketahui: favicon berasal dari raster mark, sehingga pada 16–32 px bentuknya menjadi gumpalan. Bila klien menyerahkan file vector, ganti dengan monogram AV yang digambar ulang.

## 3. Warna

| Token | Nilai | Peran | Kontras |
|---|---|---|---|
| `ink-950` | `#14110E` | teks utama, latar footer | 18.8:1 di `paper` |
| `ink-700` | `#3A342D` | heading sekunder | 11.4:1 |
| `ink-600` | `#4A443C` | teks sekunder | 9.0:1 |
| `ink-500` | `#6B635A` | caption, metadata | 6.2:1 |
| `gold-500` | `#DFA73A` | fill CTA, state aktif | 2.15:1 — bukan untuk teks |
| `gold-700` | `#8A6A1F` | teks emas, border aktif | 5.05:1 |
| `gold-300` | `#F0D06B` | sorotan, seleksi | hanya di dalam artwork |
| `paper` | `#FAFAF9` | latar halaman | — |
| `sand` | `#F2F0EC` | pita section | — |
| `line` | `#E4E1DB` | garis pemisah | 1.3:1 (dekoratif) |
| `success` | `#2E6B4F` | status terverifikasi | 6.4:1 |
| `danger` | `#A33B2A` / hover `#8F3224` | error, hapus | 6.3:1 |

Aturan emas:

1. Emas hanya muncul di tiga tempat: fill CTA utama, state aktif atau terpilih, dan logo.
2. Teks emas selalu `gold-700`. `gold-500` dan `gold-300` tidak pernah menjadi warna teks.
3. Tidak ada gradien di UI. Gradien hanya ada di dalam artwork logo.
4. CTA memakai `bg-gold-500` dengan teks `ink-950`; hover memakai `gold-700` dengan teks putih.

## 4. Tipografi

Satu keluarga: **Plus Jakarta Sans**, bobot 400, 500, 600. Dimuat dari `fonts.bunny.net` (pola yang sudah dipakai proyek). Self-hosting ditunda sampai ada kebutuhan performa yang terukur.

| Peran | Ukuran | Bobot | Line height |
|---|---|---|---|
| display | 48–64 px, `clamp()` | 600 | 1.05 |
| h1 | 36 px | 600 | 1.15 |
| h2 | 28 px | 600 | 1.2 |
| h3 | 20 px | 600 | 1.3 |
| body | 16 px | 400 | 1.6 |
| small | 14 px | 400 | 1.5 |
| caption | 12 px | 500 | 1.4 |

- Panjang baris 45–75 karakter; paragraf `max-w-prose`.
- Angka harga memakai kelas `.tabular` dan format `Rp 1.250.000.000`; beri `whitespace-nowrap` agar harga tidak pecah baris.
- Maksimal dua ukuran menonjol per layar. Hirarki dari ukuran, bukan dari warna.

## 5. Ruang dan layout

- Skala kelipatan 4 px. Jarak yang dipakai: 4, 8, 12, 16, 24, 32, 48, 64, 96, 128.
- Container `max-w-7xl` (1280 px) dengan gutter 16 / 24 / 32 px.
- Jarak antar-section 96 px (mobile) dan 128 px (desktop) — selalu lebih besar dari jarak di dalam section.
- Radius: `2px` default, `1px` untuk elemen kecil, maksimum `4px`. Tidak ada `rounded-full` kecuali untuk avatar.
- Pemisah memakai `border-line` 1 px atau langkah warna `paper` → `sand`. Bukan bayangan.
- Satu-satunya bayangan: `shadow-float` untuk elemen yang benar-benar mengapung (dropdown, dialog).

## 6. Komponen

**Button**

- Primer: `bg-gold-500 text-ink-950 hover:bg-gold-700 hover:text-white`, padding `px-5 py-2.5`, radius 2 px, teks 14 px bobot 600.
- Sekunder: border `line`, teks `ink-950`, latar transparan, hover latar `sand`.
- Ghost: teks `ink-600`, hover teks `ink-950`.
- Destruktif: teks putih di atas `danger`.
- Fokus: outline 2 px `gold-700`, offset 2 px. Disabled: opacity 50, tanpa hover.
- Tinggi minimum target sentuh 44 px.

**Input**

- Border `line`, latar putih, radius 2 px, padding `px-3 py-2`.
- Fokus: border `gold-700` + ring `gold-500/30`.
- Error: border `danger`, pesan error 14 px `danger` di bawah field, dihubungkan lewat `aria-describedby`. Tambahkan `border-danger focus:border-danger focus:ring-danger/30` pada input yang error.
- Label selalu terlihat; placeholder bukan pengganti label.

**Badge status**

- `Dijual` — latar `ink-950`, teks `paper`.
- `Disewa` — latar `gold-700`, teks putih.
- `Baru` — latar `gold-300`, teks `ink-950`.
- Radius 2 px, teks 12 px bobot 600, padding `px-2 py-0.5`.

**Kartu properti**

- Foto rasio 4:3, sudut tajam, `object-cover`.
- Badge di atas foto, kanan atas.
- Judul 18 px bobot 600, lokasi 14 px `ink-500`.
- Harga 18 px bobot 600 `.tabular`.
- Spesifikasi: ikon garis 16 px + angka, satu baris, `ink-600`.
- Hover: border `ink-950`, tanpa lift dan tanpa bayangan.
- Tanpa foto: blok `sand` dengan wordmark `-ink` di tengah, bukan placeholder abu-abu.

**Tabel (admin)**

- Header `sand`, teks 12 px bobot 600 `ink-600`.
- Baris 48 px, border bawah `line`, hover `paper`.
- Angka rata kanan dan `.tabular`; harga `whitespace-nowrap`.
- Di layar sempit, bungkus tabel dengan `overflow-x-auto`, jangan `overflow-hidden`.

**Navigasi**

- Tinggi 64 px, latar `paper`, border bawah `line`. Tanpa blur, tanpa bayangan.
- Link 14 px bobot 500 `ink-600`, aktif `ink-950`.
- Satu aksi di kanan: CTA primer.

**Footer**

- Latar `ink-950`, teks `paper/70`, judul kolom `paper`.
- Logo varian `-paper` atau tile `paper`.
- Baris legal 12 px `paper/50`.

**Empty state**

- Satu kalimat penjelas, satu aksi. Tanpa ilustrasi dekoratif.

## 7. Motion

- Durasi 150 ms untuk hover dan fokus, 200 ms untuk panel.
- Easing `ease-out`.
- Hanya `transform` dan `opacity`. Tidak ada animasi pada warna latar halaman.
- `prefers-reduced-motion: reduce` mematikan semua transisi non-esensial.
- Tidak ada konten yang bergantung pada animasi untuk terbaca.

## 8. Accessibility

- Teks body minimal 4.5:1; elemen UI minimal 3:1.
- Fokus selalu terlihat; jangan pernah `outline: none` tanpa pengganti.
- Target sentuh 44×44 px untuk aksi utama; aksi di bilah alat (header, admin) minimal 40×40 px.
- Setiap input punya `<label>`; error diumumkan sebagai teks, bukan hanya warna.
- Alt foto: `Rumah 3 kamar tidur di {lokasi}`; foto dekoratif `alt=""`.
- Skip link ke `#main` di setiap layout publik.

## 9. Konten

- Bahasa Indonesia baku, sapaan "Anda" untuk halaman publik, "kamu" tidak dipakai.
- Harga: `Rp 1.250.000.000` (titik sebagai pemisah ribuan, tanpa desimal).
- Label status: `Dijual`, `Disewa`, `Baru`.
- Tombol menyebut aksinya: `Lihat detail`, `Hubungi via WhatsApp`, `Simpan properti`.
- Empty state menjelaskan langkah berikutnya, bukan meminta maaf.
- Tanggal: `26 Oktober 2026`.

## 10. Anti-pattern

| Jangan | Alasan |
|---|---|
| Teks emas `gold-500` di atas putih | 2.15:1, gagal AA |
| Gradien di tombol atau latar section | menyaingi logo dan terlihat murah |
| Radius 16 px atau lebih | bertabrakan dengan geometri logo |
| Bayangan lembut di kartu diam | memisahkan permukaan dengan garis, bukan bayangan |
| Ikon emas di setiap kartu | emas kehilangan perannya |
| Carousel otomatis | mengganggu, buruk untuk aksesibilitas |
| Field krem hangat di seluruh halaman | jatuh ke klise krem-plus-emas |
| Placeholder abu-abu untuk foto kosong | pakai blok `sand` dengan wordmark |

## 11. Peta token

| Dokumen | Tailwind | Nilai |
|---|---|---|
| `ink-950` | `text-ink-950`, `bg-ink-950` | `#14110E` |
| `ink-600` | `text-ink-600` | `#4A443C` |
| `ink-500` | `text-ink-500` | `#6B635A` |
| `gold-500` | `bg-gold-500` | `#DFA73A` |
| `gold-700` | `text-gold-700`, `border-gold-700` | `#8A6A1F` |
| `gold-300` | `bg-gold-300` | `#F0D06B` |
| `paper` | `bg-paper` | `#FAFAF9` |
| `sand` | `bg-sand` | `#F2F0EC` |
| `line` | `border-line` | `#E4E1DB` |
| radius default | `rounded` | `2px` |
| bayangan | `shadow-float` | `0 12px 32px -8px rgba(20,17,14,.18)` |
| font | `font-sans` | Plus Jakarta Sans |

Contoh halaman hidup: route `/design-system` (hanya tersedia di luar produksi).
