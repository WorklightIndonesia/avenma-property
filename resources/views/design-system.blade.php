@extends('layouts.public')

@section('title', 'Design System — Avenma Properti')

@php
    $swatches = [
        ['ink-950', '#14110E', 'bg-ink-950', '18.8:1 di paper'],
        ['ink-700', '#3A342D', 'bg-ink-700', '11.4:1'],
        ['ink-600', '#4A443C', 'bg-ink-600', '9.0:1'],
        ['ink-500', '#6B635A', 'bg-ink-500', '6.2:1'],
        ['gold-500', '#DFA73A', 'bg-gold-500', '2.15:1 — fill saja'],
        ['gold-700', '#8A6A1F', 'bg-gold-700', '5.05:1'],
        ['gold-300', '#F0D06B', 'bg-gold-300', 'artwork saja'],
        ['paper', '#FAFAF9', 'bg-paper', 'latar'],
        ['sand', '#F2F0EC', 'bg-sand', 'pita'],
        ['line', '#E4E1DB', 'bg-line', 'garis'],
        ['success', '#2E6B4F', 'bg-success', '6.4:1'],
        ['danger', '#A33B2A', 'bg-danger', '6.3:1'],
    ];

    $statusBadge = [
        'Dijual' => 'border border-ink-950 bg-paper text-ink-950',
        'Disewa' => 'bg-gold-700 text-white',
        'Baru' => 'bg-gold-300 text-ink-950',
    ];

    $listings = [
        [
            'title' => 'Rumah Cluster Bintaro',
            'location' => 'Bintaro, Tangerang Selatan',
            'price' => 'Rp 1.250.000.000',
            'specs' => '3 KT · 2 KM · 1 carport · LT 120 m²',
            'status' => 'Dijual',
            'type' => 'Primer',
            'developer' => 'Bintaro Development',
        ],
        [
            'title' => 'Apartemen Sudirman Park',
            'location' => 'Sudirman, Jakarta Pusat',
            'price' => 'Rp 850.000.000',
            'specs' => '2 KT · 2 KM · 1 carport · LT 68 m²',
            'status' => 'Dijual',
            'type' => 'Sekunder',
            'developer' => null,
        ],
        [
            'title' => 'Townhouse Serpong Garden',
            'location' => 'Serpong, Tangerang Selatan',
            'price' => 'Rp 1.480.000.000',
            'specs' => '4 KT · 3 KM · 2 carport · LT 150 m²',
            'status' => 'Baru',
            'type' => 'Primer',
            'developer' => 'Graha Nusantara',
        ],
        [
            'title' => 'Rumah Minimalis Cipete',
            'location' => 'Cipete, Jakarta Selatan',
            'price' => 'Rp 2.350.000.000',
            'specs' => '3 KT · 3 KM · 2 carport · LT 180 m²',
            'status' => 'Dijual',
            'type' => 'Sekunder',
            'developer' => 'Cipete Properti',
        ],
        [
            'title' => 'Apartemen Kemang Village',
            'location' => 'Kemang, Jakarta Selatan',
            'price' => 'Rp 1.750.000.000',
            'specs' => '2 KT · 2 KM · 1 carport · LT 85 m²',
            'status' => 'Dijual',
            'type' => 'Sekunder',
            'developer' => 'Kemang Properti',
        ],
        [
            'title' => 'Rumah Taman Beverly',
            'location' => 'BSD, Tangerang Selatan',
            'price' => 'Rp 1.950.000.000',
            'specs' => '4 KT · 3 KM · 2 carport · LT 165 m²',
            'status' => 'Baru',
            'type' => 'Primer',
            'developer' => 'Beverly Group',
        ],
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-7xl space-y-20 px-4 py-16 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Design System</h1>
            <p class="mt-3 max-w-xl text-ink-600">
                Prototipe untuk persetujuan klien, lalu token dan komponen yang menyusunnya. Hanya tersedia di luar produksi. Kontrak lengkap ada di <code class="text-sm">DESIGN.md</code>.
            </p>
        </header>

        <section aria-labelledby="prototipe">
            <div class="border border-line bg-ink-950 px-6 py-10 text-paper sm:px-10">
                <p class="text-sm text-paper/60">Untuk persetujuan klien</p>
                <h2 id="prototipe" class="mt-2 max-w-2xl text-3xl font-semibold leading-tight tracking-tight">Lima layar, keputusan lengkap.</h2>
                <p class="mt-4 max-w-2xl text-paper/70">
                    Prototipe di bawah memakai token dan komponen yang sama dengan situs nanti. Nada hero, huruf, dan sudut komponen sudah diputuskan; halaman ini menjadi acuan saat katalog dibangun.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <p class="border border-paper/20 px-4 py-3 text-sm">
                        <span class="font-semibold">Diputuskan.</span> Tema terang, hero dengan foto, satu keluarga huruf, kontrol bersudut 6 px.
                    </p>
                    <p class="border border-paper/20 px-4 py-3 text-sm">
                        <span class="font-semibold">Menunggu klien.</span> Foto properti dan file vektor logo.
                    </p>
                </div>
                <p class="mt-4 text-xs text-paper/50">Tautan dan formulir belum aktif, kecuali simulasi KPR yang bisa dicoba.</p>
            </div>
        </section>

        <section aria-labelledby="beranda">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="beranda" class="scroll-mt-24 text-xl font-semibold">Beranda</h2>
                <p class="text-sm text-ink-500">Hero terang dengan foto — sudah diputuskan.</p>
            </div>

            <div class="mt-6 space-y-4">
                <div class="border border-line">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                        <p class="text-xs font-semibold">Hero <span class="ml-2 border border-ink-950 px-1.5 py-0.5 text-[11px]">Diputuskan</span></p>
                        <p class="text-xs text-ink-500">Beranda — bagian atas</p>
                    </div>
                    <div class="bg-paper px-6 py-12 sm:px-10 lg:px-12 lg:py-16">
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <p class="text-sm text-ink-500">Properti primer dan sekunder</p>
                                <p class="mt-4 max-w-xl text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl">
                                    Temukan properti yang tepat untuk Anda.
                                </p>
                                <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-600">
                                    Katalog properti pilihan di Jabodetabek, lengkap dengan informasi lokasi dan simulasi KPR.
                                </p>
                                <div class="mt-8 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950">Lihat Properti</span>
                                    <span class="inline-flex items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950">Hubungi Kami</span>
                                </div>
                            </div>
                            <div class="flex aspect-[4/3] flex-col items-center justify-center bg-sand">
                                <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                                <p class="mt-3 text-xs text-ink-500">Foto properti utama</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-line">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                        <p class="text-xs font-semibold">Properti pilihan</p>
                        <p class="text-xs text-ink-500">Blok di bawah hero</p>
                    </div>
                    <div class="bg-paper px-6 py-10 sm:px-10">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p class="text-2xl font-semibold tracking-tight">Properti pilihan</p>
                                <p class="mt-2 text-sm text-ink-500">Dipilih manual setiap minggu.</p>
                            </div>
                            <span class="text-sm font-semibold">Lihat semua</span>
                        </div>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach (array_slice($listings, 0, 3) as $listing)
                                <article class="border border-line bg-white">
                                    <div class="relative flex aspect-[4/3] items-center justify-center bg-sand">
                                        <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                                        <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$listing['status']] }}">{{ $listing['status'] }}</span>
                                    </div>
                                    <div class="space-y-3 p-4">
                                        <p class="text-xs">
                                            <span class="font-semibold text-ink-950">{{ $listing['type'] }}</span>
                                            @if ($listing['developer'])
                                                <span class="text-ink-500">· {{ $listing['developer'] }}</span>
                                            @endif
                                        </p>
                                        <p class="text-lg font-semibold leading-snug">{{ $listing['title'] }}</p>
                                        <p class="text-sm text-ink-500">{{ $listing['location'] }}</p>
                                        <p class="tabular text-lg font-semibold">{{ $listing['price'] }}</p>
                                        <p class="text-sm text-ink-600">{{ $listing['specs'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="katalog">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="katalog" class="text-xl font-semibold">Katalog properti</h2>
                <p class="text-sm text-ink-500">Filter, kartu, dan pagination di satu layar.</p>
            </div>

            <div class="mt-6 border border-line">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                    <p class="text-xs font-semibold">Katalog</p>
                    <p class="text-xs text-ink-500">/properti</p>
                </div>
                <div class="bg-paper px-4 py-6 sm:px-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-2xl font-semibold tracking-tight">Properti</p>
                            <p class="mt-1 text-sm text-ink-500">24 properti ditemukan</p>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-ink-500">Urutkan</span>
                            <span class="border border-line px-3 py-1.5 font-medium">Terbaru</span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ([['Lokasi', 'Semua lokasi'], ['Tipe', 'Semua tipe'], ['Harga', 'Semua harga'], ['Status', 'Semua status']] as [$label, $value])
                            <div class="border border-line bg-white px-3 py-2">
                                <p class="text-[11px] text-ink-500">{{ $label }}</p>
                                <p class="mt-0.5 text-sm font-medium">{{ $value }}</p>
                            </div>
                        @endforeach
                        <span class="inline-flex items-center justify-center self-end justify-self-end rounded bg-gold-500 px-6 py-2.5 text-sm font-semibold text-ink-950">Terapkan</span>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($listings as $listing)
                            <article class="border border-line bg-white">
                                <div class="relative flex aspect-[4/3] items-center justify-center bg-sand">
                                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                                    <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$listing['status']] }}">{{ $listing['status'] }}</span>
                                </div>
                                <div class="space-y-3 p-4">
                                    <p class="text-xs">
                                        <span class="font-semibold text-ink-950">{{ $listing['type'] }}</span>
                                        @if ($listing['developer'])
                                            <span class="text-ink-500">· {{ $listing['developer'] }}</span>
                                        @endif
                                    </p>
                                    <p class="text-lg font-semibold leading-snug">{{ $listing['title'] }}</p>
                                    <p class="text-sm text-ink-500">{{ $listing['location'] }}</p>
                                    <p class="tabular text-lg font-semibold">{{ $listing['price'] }}</p>
                                    <p class="text-sm text-ink-600">{{ $listing['specs'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5 text-sm">
                        <span class="text-ink-500">Menampilkan 1–6 dari 24</span>
                        <div class="flex items-center gap-1">
                            <span class="border border-ink-950 px-3 py-1.5 font-semibold">1</span>
                            <span class="px-3 py-1.5 text-ink-600">2</span>
                            <span class="px-3 py-1.5 text-ink-600">3</span>
                            <span class="px-3 py-1.5 text-ink-600">Berikutnya</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="filter-panel">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="filter-panel" class="text-xl font-semibold">Panel filter</h2>
                <p class="text-sm text-ink-500">Tertutup saat belum ada filter. Terbuka dan menampilkan chip saat filter aktif.</p>
            </div>

            <div class="mt-6 space-y-8">
                <div>
                    <p class="text-xs font-semibold text-ink-500">Keadaan 1: belum ada filter</p>
                    <div class="mt-2 border border-line px-4 py-4">
                        @include('public.listings._filters', [
                            'idPrefix' => 'preview-a-',
                            'action' => '#',
                            'filters' => ['lokasi' => '', 'tipe' => '', 'harga' => '', 'status' => ''],
                            'sort' => 'terbaru',
                            'locations' => ['bintaro' => 'Bintaro', 'cipete' => 'Cipete'],
                            'typeOptions' => ['rumah' => 'Rumah', 'apartemen' => 'Apartemen'],
                            'statusOptions' => ['dijual' => 'Dijual', 'baru' => 'Baru'],
                            'chips' => [],
                        ])
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-ink-500">Keadaan 2: dua filter aktif, panel terbuka</p>
                    <div class="mt-2 border border-line px-4 py-4">
                        @include('public.listings._filters', [
                            'idPrefix' => 'preview-b-',
                            'action' => '#',
                            'filters' => ['lokasi' => 'bintaro', 'tipe' => '', 'harga' => '1000-2000', 'status' => ''],
                            'sort' => 'terbaru',
                            'locations' => ['bintaro' => 'Bintaro', 'cipete' => 'Cipete'],
                            'typeOptions' => ['rumah' => 'Rumah', 'apartemen' => 'Apartemen'],
                            'statusOptions' => ['dijual' => 'Dijual', 'baru' => 'Baru'],
                            'chips' => [
                                ['key' => 'lokasi', 'label' => 'Bintaro'],
                                ['key' => 'harga', 'label' => 'Rp 1 – 2 miliar'],
                            ],
                        ])
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-ink-500">Keadaan 3: pilihan kontrol urutan</p>
                    <p class="mt-1 text-sm text-ink-600">Dua bentuk untuk kolom "Urutkan". Yang dipakai sekarang adalah radio bertumpuk di dalam panel.</p>

                    <div class="mt-3 grid gap-4 lg:grid-cols-2">
                        <div class="border border-line px-4 py-4">
                            <p class="text-xs font-semibold text-ink-500">Pilihan A: dropdown, sejajar dengan filter lain</p>
                            <div class="mt-3 max-w-xs">
                                <label for="preview-sort-select" class="block text-xs font-medium text-ink-500">Urutkan</label>
                                <select id="preview-sort-select" class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30">
                                    <option>Terbaru</option>
                                    <option>Harga terendah</option>
                                    <option>Harga tertinggi</option>
                                </select>
                            </div>
                            <p class="mt-3 text-xs text-ink-500">Satu baris, tidak menambah tinggi panel.</p>
                        </div>

                        <div class="border border-line px-4 py-4">
                            <p class="text-xs font-semibold text-ink-500">Pilihan B: tombol berjajar, pilihan aktif berisi warna</p>
                            <fieldset class="mt-3">
                                <legend class="block text-xs font-medium text-ink-500">Urutkan</legend>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach (['Terbaru', 'Harga terendah', 'Harga tertinggi'] as $index => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="preview-sort" value="{{ $label }}" @checked($index === 0) class="peer sr-only">
                                            <span class="inline-flex items-center rounded border border-line px-3 py-2 text-sm font-medium text-ink-600 hover:border-ink-950 peer-checked:border-ink-950 peer-checked:bg-ink-950 peer-checked:text-paper peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-gold-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <p class="mt-3 text-xs text-ink-500">Lebih mudah dipindai, tetapi memakan satu baris penuh di layar sempit.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="detail">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="detail" class="text-xl font-semibold">Detail properti</h2>
                <p class="text-sm text-ink-500">Galeri, spesifikasi, dan CTA dalam satu layar.</p>
            </div>

            <div class="mt-6 border border-line">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                    <p class="text-xs font-semibold">Detail</p>
                    <p class="text-xs text-ink-500">/properti/rumah-cluster-bintaro</p>
                </div>
                <div class="bg-paper px-4 py-6 sm:px-6">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-xs text-ink-500">Beranda / Properti / Rumah Cluster Bintaro</p>
                        <p class="text-xs text-ink-500">Diperbarui 26 Oktober 2026</p>
                    </div>

                    <div class="mt-4">
                        <div class="relative flex h-72 flex-col items-center justify-center bg-sand sm:h-96">
                            <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                            <p class="mt-3 text-xs text-ink-500">Foto utama</p>
                            <span class="absolute left-3 top-3 rounded border border-ink-950 bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Dijual</span>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            @foreach ([1, 2, 3] as $thumbnail)
                                <div class="flex h-24 items-center justify-center bg-sand">
                                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-8 w-auto" onerror="this.remove()">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_300px]">
                        <div>
                            <div>
                                <p class="text-xs">
                                    <span class="font-semibold text-ink-950">Primer</span>
                                    <span class="text-ink-500">· Rumah · Bintaro Development</span>
                                </p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight">Rumah Cluster Bintaro</p>
                                <p class="mt-1 text-sm text-ink-500">Jl. Bintaro Utama IV No. 12, Bintaro, Tangerang Selatan</p>
                            </div>
                            <p class="tabular mt-4 text-2xl font-semibold">Rp 1.250.000.000</p>

                            <div class="mt-6 grid grid-cols-2 gap-4 border-y border-line py-5 sm:grid-cols-3 lg:grid-cols-5">
                                @foreach ([['Kamar tidur', '3'], ['Kamar mandi', '2'], ['Carport', '1'], ['Luas tanah', '120 m²'], ['Luas bangunan', '96 m²']] as [$label, $value])
                                    <div>
                                        <p class="text-xs text-ink-500">{{ $label }}</p>
                                        <p class="mt-1 text-sm font-semibold">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <p class="mt-6 text-base leading-relaxed text-ink-600">
                                Rumah dua lantai di dalam cluster dengan akses satu gerbang. Pasar modern dan stasiun Bintaro berada dalam 10 menit berkendara. Sertifikat SHM, hadap utara.
                            </p>

                            <p class="mt-8 text-base font-semibold">Fasilitas</p>
                            <ul class="mt-3 grid grid-cols-1 gap-2 text-sm text-ink-600 sm:grid-cols-2">
                                @foreach (['Keamanan 24 jam', 'Kolam renang', 'Taman bermain', 'Akses gerbang tunggal', 'Parkir tamu', 'Jalur jogging'] as $facility)
                                    <li>{{ $facility }}</li>
                                @endforeach
                            </ul>

                            <div class="mt-8 flex h-56 flex-col items-center justify-center border border-line bg-sand">
                                <p class="text-sm font-semibold">Peta lokasi</p>
                                <p class="mt-1 text-xs text-ink-500">Jl. Bintaro Utama IV No. 12, Bintaro, Tangerang Selatan</p>
                            </div>
                        </div>

                        <aside class="h-fit border border-line bg-white p-5">
                            <p class="text-xs text-ink-500">Harga</p>
                            <p class="tabular mt-1 text-2xl font-semibold">Rp 1.250.000.000</p>
                            <p class="mt-1 text-xs text-ink-500">LT 120 m² · LB 96 m²</p>
                            <div class="mt-5 space-y-2">
                                <span class="flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950">Hubungi via WhatsApp</span>
                                <span class="flex items-center justify-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950">Simpan properti</span>
                            </div>
                            <a href="#kpr" class="mt-3 block text-center text-sm font-semibold underline underline-offset-4 hover:text-ink-600">Simulasi KPR</a>
                            <div class="mt-5 space-y-4 border-t border-line pt-4">
                                <div>
                                    <p class="text-xs text-ink-500">Developer</p>
                                    <p class="mt-1 text-sm font-semibold">Bintaro Development</p>
                                </div>
                                <div>
                                    <p class="text-xs text-ink-500">Dijual oleh</p>
                                    <p class="mt-1 text-sm font-semibold">Avenma Properti</p>
                                    <p class="mt-1 text-sm leading-relaxed text-ink-600">Pendampingan dari survei sampai akad.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="kpr">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="kpr" class="text-xl font-semibold">Simulasi KPR</h2>
                <p class="text-sm text-ink-500">Bagian dari halaman detail properti.</p>
            </div>

            <div class="mt-6 border border-line">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                    <p class="text-xs font-semibold">Simulasi KPR</p>
                    <p class="text-xs text-ink-500">/properti/rumah-cluster-bintaro</p>
                </div>
                <div
                    class="bg-paper px-4 py-6 sm:px-6"
                    x-data="{
                        price: 1250000000,
                        rate: 7.5,
                        rateType: 'fixed',
                        dp: 20,
                        tenor: 15,
                        get dpAmount() { return this.price * this.dp / 100 },
                        get principal() { return this.price - this.dpAmount },
                        get rateLabel() { return this.rate.toLocaleString('id-ID', { maximumFractionDigits: 2 }) },
                        get monthly() {
                            const r = this.rate / 100 / 12, n = this.tenor * 12;
                            return this.principal * r / (1 - Math.pow(1 + r, -n));
                        },
                        get totalInterest() { return this.monthly * this.tenor * 12 - this.principal },
                        get totalPayment() { return this.dpAmount + this.monthly * this.tenor * 12 },
                        rp: function (value) { return 'Rp ' + Math.round(value).toLocaleString('id-ID') },
                        setPrice: function (event) {
                            const el = event.target;
                            const caretDigits = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
                            const digits = el.value.replace(/\D/g, '');
                            this.price = digits === '' ? 0 : Math.min(parseInt(digits, 10), 999999999999);
                            const text = this.price === 0 ? '' : this.price.toLocaleString('id-ID');
                            el.value = text;
                            let pos = 0, seen = 0;
                            while (pos < text.length && seen < caretDigits) {
                                if (/\d/.test(text[pos])) seen++;
                                pos++;
                            }
                            el.setSelectionRange(pos, pos);
                        }
                    }"
                >
                    <p class="text-2xl font-semibold tracking-tight">Hitung angsuran Anda</p>
                    <p class="mt-1 max-w-xl text-sm text-ink-500">Perkiraan angsuran bulanan untuk Rumah Cluster Bintaro, Rp 1.250.000.000.</p>

                    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
                        <div class="border border-line bg-white p-5 sm:p-6">
                            <div>
                                <label for="kpr-price" class="text-sm font-semibold">Harga properti</label>
                                <div class="mt-1 flex items-baseline gap-2 rounded border border-line bg-white px-3 py-2 focus-within:border-gold-700 focus-within:ring-2 focus-within:ring-gold-500/30">
                                    <span class="text-sm text-ink-500">Rp</span>
                                    <input
                                        id="kpr-price"
                                        type="text"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        value="1.250.000.000"
                                        @input="setPrice($event)"
                                        class="tabular w-full min-w-0 border-0 bg-transparent p-0 text-lg font-semibold text-ink-950 focus:outline-none focus:ring-0"
                                        aria-describedby="kpr-price-help"
                                    >
                                </div>
                                <p id="kpr-price-help" class="mt-1 text-xs text-ink-500">Terisi dari listing. Ubah untuk mencoba harga lain.</p>
                            </div>

                            <div class="mt-6">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <label for="kpr-dp" class="text-sm font-semibold">Uang muka (DP)</label>
                                    <p class="tabular text-sm font-semibold"><span x-text="dp">20</span>% · <span x-text="rp(dpAmount)">Rp 250.000.000</span></p>
                                </div>
                                <input id="kpr-dp" type="range" min="20" max="70" step="5" class="range mt-2 w-full" x-model.number="dp" aria-describedby="kpr-dp-help">
                                <p id="kpr-dp-help" class="mt-1 text-xs text-ink-500">Minimum 20%. Maksimum 70%.</p>
                            </div>

                            <div class="mt-6">
                                <p id="kpr-tenor-label" class="text-sm font-semibold">Tenor (tahun)</p>
                                <div class="mt-2 grid grid-cols-4 gap-2" role="group" aria-labelledby="kpr-tenor-label">
                                    @foreach ([5, 10, 15, 20] as $years)
                                        <button
                                            type="button"
                                            class="h-11 rounded border text-sm font-semibold transition-colors duration-150"
                                            :class="tenor === {{ $years }} ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                                            :aria-pressed="tenor === {{ $years }}"
                                            @click="tenor = {{ $years }}"
                                        >{{ $years }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 border-t border-line pt-5">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <label for="kpr-rate" class="text-sm font-semibold">Suku bunga</label>
                                    <p class="tabular text-sm font-semibold"><span x-text="rateLabel">7,5</span>% per tahun</p>
                                </div>
                                <input id="kpr-rate" type="range" min="3" max="15" step="0.25" class="range mt-2 w-full" x-model.number="rate" aria-describedby="kpr-rate-help">
                                <div class="mt-2 flex flex-wrap gap-2" role="group" aria-label="Tipe suku bunga">
                                    <button
                                        type="button"
                                        class="h-11 rounded border px-4 text-sm font-semibold transition-colors duration-150"
                                        :class="rateType === 'fixed' ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                                        :aria-pressed="rateType === 'fixed'"
                                        @click="rateType = 'fixed'"
                                    >Tetap</button>
                                    <button
                                        type="button"
                                        class="h-11 rounded border px-4 text-sm font-semibold transition-colors duration-150"
                                        :class="rateType === 'floating' ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                                        :aria-pressed="rateType === 'floating'"
                                        @click="rateType = 'floating'"
                                    >Mengambang</button>
                                </div>
                                <p id="kpr-rate-help" class="mt-2 text-xs text-ink-500" x-text="rateType === 'fixed' ? 'Bunga tetap selama tenor. Angka asumsi internal, bukan penawaran bank.' : 'Bunga mengambang: angsuran bisa berubah bila suku bunga pasar naik.'">Bunga tetap selama tenor. Angka asumsi internal, bukan penawaran bank.</p>
                            </div>
                        </div>

                        <div class="bg-ink-950 p-5 text-paper sm:p-6">
                            <p class="text-sm text-paper/60">Angsuran per bulan</p>
                            <p class="tabular mt-2 text-4xl font-semibold leading-none" x-text="rp(monthly)">Rp 9.270.124</p>
                            <p class="mt-2 text-sm text-paper/60"><span x-text="tenor">15</span> tahun · <span x-text="rateType === 'fixed' ? 'bunga tetap' : 'bunga mengambang'">bunga tetap</span></p>

                            <dl class="mt-6 space-y-3 border-t border-paper/15 pt-5 text-sm">
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-paper/60">Uang muka</dt>
                                    <dd class="tabular font-semibold" x-text="rp(dpAmount)">Rp 250.000.000</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-paper/60">Pokok pinjaman</dt>
                                    <dd class="tabular font-semibold" x-text="rp(principal)">Rp 1.000.000.000</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-paper/60">Total bunga</dt>
                                    <dd class="tabular font-semibold" x-text="rp(totalInterest)">Rp 668.622.248</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3 border-t border-paper/15 pt-3">
                                    <dt class="font-semibold">Total pembayaran</dt>
                                    <dd class="tabular font-semibold" x-text="rp(totalPayment)">Rp 1.918.622.248</dd>
                                </div>
                            </dl>

                            <span class="mt-6 flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-center text-sm font-semibold text-ink-950">Konsultasi KPR via WhatsApp</span>

                            <p class="mt-3 text-xs leading-relaxed text-paper/50">Perkiraan saja. Angsuran sebenarnya ditentukan bank dan mencakup provisi, asuransi, serta pajak.</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-ink-500">Metode anuitas: angsuran tetap tiap bulan, bunga dihitung dari sisa pokok. Angka awal bunga dan DP minimum mengikuti <code class="text-xs">kpr_settings</code>.</p>
                </div>
            </div>
        </section>

        <section aria-labelledby="artikel">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="artikel" class="scroll-mt-24 text-xl font-semibold">Artikel blog</h2>
                <p class="text-sm text-ink-500">Satu keluarga huruf — sudah diputuskan.</p>
            </div>

            <div class="mt-6 border border-line">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                    <p class="text-xs font-semibold">Artikel</p>
                    <p class="text-xs text-ink-500">/blog/membeli-rumah-pertama</p>
                </div>
                <div class="bg-paper px-5 py-10 sm:px-8">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold text-gold-700">Panduan</p>
                        <p class="mt-3 text-4xl font-semibold leading-[1.05] tracking-tight lg:text-5xl">
                            Membeli rumah pertama: lima biaya yang sering terlewat
                        </p>
                        <p class="mt-3 text-sm text-ink-500">26 Oktober 2026 · 6 menit baca</p>
                        <p class="mt-5 text-base leading-relaxed text-ink-600">
                            DP bukan satu-satunya angka di depan. Biaya akta, pajak pembeli, dan provisi bank sering baru muncul setelah harga disepakati.
                        </p>
                        <p class="mt-3 text-base leading-relaxed text-ink-600">
                            Sebelum akad, minta rincian tertulis dari developer atau agen. Angka yang tidak masuk brosur tetap harus masuk hitungan Anda.
                        </p>
                        <p class="mt-6 border-l border-line pl-4 text-lg leading-snug">
                            “Rumah yang tepat bukan yang termurah, tetapi yang biayanya bisa Anda jelaskan sampai rupiah terakhir.”
                        </p>
                        <p class="mt-6 text-xl font-semibold">Biaya yang tidak masuk brosur</p>
                        <p class="mt-2 text-base leading-relaxed text-ink-600">
                            Pajak pembeli, biaya notaris, dan provisi KPR biasanya tiga pos terbesar. Sisihkan anggaran terpisah di luar DP.
                        </p>
                    </div>
                </div>
            </div>

            <p class="mt-4 text-sm text-ink-500">Tidak ada huruf kedua di web. Judul, isi, harga, dan label semuanya Plus Jakarta Sans.</p>
        </section>

        <section aria-labelledby="radius">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="radius" class="scroll-mt-24 text-xl font-semibold">Sudut komponen</h2>
                <p class="text-sm text-ink-500">Opsi B dipilih: kontrol 6 px, kartu tetap siku.</p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([['A', '2 px', 'Ditolak', '', 'rounded-[2px]'], ['B', '6 px', 'Diputuskan', 'border-ink-950', 'rounded-[6px]'], ['C', '10 px', 'Ditolak', '', 'rounded-[10px]']] as [$label, $size, $note, $frame, $radius])
                    <div class="border {{ $frame ?: 'border-line' }}">
                        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                            <p class="text-xs font-semibold">Opsi {{ $label }} · {{ $size }}</p>
                            <p class="text-xs text-ink-500">{{ $note }}</p>
                        </div>
                        <div class="space-y-3 bg-paper p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $radius }} bg-gold-300 px-2 py-0.5 text-xs font-semibold text-ink-950">Baru</span>
                                <span class="{{ $radius }} border border-ink-950 bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Dijual</span>
                            </div>
                            <span class="flex items-center justify-center {{ $radius }} bg-gold-500 px-4 py-2.5 text-sm font-semibold text-ink-950">Lihat detail</span>
                            <span class="flex items-center justify-center {{ $radius }} border border-line px-4 py-2.5 text-sm font-semibold text-ink-950">Simpan properti</span>
                            <div class="{{ $radius }} border border-line bg-white px-3 py-2">
                                <p class="text-[11px] text-ink-500">Judul properti</p>
                                <p class="mt-0.5 text-sm">Rumah Cluster Bintaro</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 text-sm text-ink-500">Pembulatan hanya untuk kontrol yang disentuh: tombol, input, badge. Kartu, panel, dan bingkai foto tetap siku di ketiga opsi, supaya grid tidak melembut dan emas tetap satu-satunya elemen hangat.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="border border-line">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                        <p class="text-xs font-semibold">Kartu siku</p>
                        <p class="text-xs text-ink-500">Dipakai</p>
                    </div>
                    <div class="flex justify-center bg-paper p-5">
                        <article class="w-full max-w-xs border border-line bg-white">
                            <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                                <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                            </div>
                            <div class="space-y-1 p-3">
                                <p class="text-lg font-semibold leading-snug">Rumah Cluster Bintaro</p>
                                <p class="tabular text-sm font-semibold">Rp 1.250.000.000</p>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="border border-line">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-line bg-sand px-4 py-2.5">
                        <p class="text-xs font-semibold">Kartu bulat 10 px</p>
                        <p class="text-xs text-ink-500">Tidak disarankan</p>
                    </div>
                    <div class="flex justify-center bg-paper p-5">
                        <article class="w-full max-w-xs overflow-hidden rounded-[10px] border border-line bg-white">
                            <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                                <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                            </div>
                            <div class="space-y-1 p-3">
                                <p class="text-lg font-semibold leading-snug">Rumah Cluster Bintaro</p>
                                <p class="tabular text-sm font-semibold">Rp 1.250.000.000</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <div class="mt-8 overflow-x-auto border border-line">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Keputusan desain</caption>
                    <thead class="bg-sand text-xs font-semibold text-ink-600">
                        <tr>
                            <th class="px-4 py-3">Keputusan</th>
                            <th class="px-4 py-3">Hasil</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-line">
                            <td class="px-4 py-3 font-semibold">Nada beranda</td>
                            <td class="px-4 py-3">Hero terang dengan foto, tema terang di seluruh situs</td>
                            <td class="px-4 py-3">Diputuskan</td>
                        </tr>
                        <tr class="border-t border-line">
                            <td class="px-4 py-3 font-semibold">Huruf</td>
                            <td class="px-4 py-3">Satu keluarga: Plus Jakarta Sans</td>
                            <td class="px-4 py-3">Diputuskan</td>
                        </tr>
                        <tr class="border-t border-line">
                            <td class="px-4 py-3 font-semibold">Sudut komponen</td>
                            <td class="px-4 py-3">Kontrol 6 px pada tombol, input, dan badge; kartu, panel, dan foto tetap siku; thumb slider bulat penuh</td>
                            <td class="px-4 py-3">Diputuskan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="border-t border-line pt-12">
            <p class="text-sm text-ink-500">Untuk tim teknis</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight">Token dan komponen</h2>
        </div>

        <section aria-labelledby="warna">
            <h2 id="warna" class="text-xl font-semibold">Warna</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($swatches as [$name, $hex, $class, $note])
                    <div class="border border-line">
                        <div class="h-20 {{ $class }}"></div>
                        <div class="space-y-1 p-3 text-sm">
                            <p class="font-semibold">{{ $name }}</p>
                            <p class="tabular text-ink-500">{{ $hex }}</p>
                            <p class="text-ink-500">{{ $note }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="tipografi">
            <h2 id="tipografi" class="text-xl font-semibold">Tipografi</h2>
            <p class="mt-2 text-sm text-ink-500">Huruf untuk artikel blog masih dibuka; lihat <a href="#artikel" class="underline underline-offset-4 hover:text-ink-950">bagian Artikel</a>.</p>
            <div class="mt-6 space-y-4 border border-line p-6">
                <p class="text-5xl font-semibold tracking-tight">Properti pilihan</p>
                <p class="text-3xl font-semibold tracking-tight">Rumah tiga kamar</p>
                <p class="text-xl font-semibold">Detail properti</p>
                <p class="max-w-prose text-base leading-relaxed text-ink-600">
                    Body 16 px, line height 1.6, maksimum 75 karakter per baris. Angka harga memakai tabular figures: <span class="tabular whitespace-nowrap font-semibold text-ink-950">Rp 1.250.000.000</span>.
                </p>
                <p class="text-sm text-ink-500">Metadata 14 px — lokasi, luas, tanggal.</p>
                <p class="text-xs font-medium text-ink-500">Caption 12 px.</p>
            </div>
        </section>

        <section aria-labelledby="tombol">
            <h2 id="tombol" class="text-xl font-semibold">Tombol</h2>
            <div class="mt-6 flex flex-wrap items-center gap-4 border border-line p-6">
                <x-primary-button>Lihat detail</x-primary-button>
                <x-secondary-button>Simpan</x-secondary-button>
                <button class="inline-flex items-center rounded px-5 py-2.5 text-sm font-semibold text-ink-600 hover:text-ink-950">Batal</button>
                <x-danger-button>Hapus</x-danger-button>
                <x-primary-button disabled>Nonaktif</x-primary-button>
            </div>
        </section>

        <section aria-labelledby="badge">
            <h2 id="badge" class="text-xl font-semibold">Badge status</h2>
            <div class="mt-6 flex flex-wrap gap-3 border border-line p-6">
                <span class="rounded border border-ink-950 bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Dijual</span>
                <span class="rounded bg-gold-700 px-2 py-0.5 text-xs font-semibold text-white">Disewa</span>
                <span class="rounded bg-gold-300 px-2 py-0.5 text-xs font-semibold text-ink-950">Baru</span>
            </div>
        </section>

        <section aria-labelledby="form">
            <h2 id="form" class="text-xl font-semibold">Form</h2>
            <div class="mt-6 grid gap-6 border border-line p-6 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-input-label for="demo-judul" value="Judul properti" />
                    <x-text-input id="demo-judul" class="block w-full" placeholder="Contoh: Rumah Cluster Bintaro" />
                    <p class="text-sm text-ink-500">Teks bantuan 14 px.</p>
                </div>
                <div class="space-y-2">
                    <x-input-label for="demo-harga" value="Harga" />
                    <x-text-input id="demo-harga" class="block w-full border-danger focus:border-danger focus:ring-danger/30" value="1250000000" aria-invalid="true" aria-describedby="demo-harga-error" />
                    <x-input-error id="demo-harga-error" :messages="['Harga wajib diisi.']" />
                </div>
            </div>
        </section>

        <section aria-labelledby="kartu">
            <h2 id="kartu" class="text-xl font-semibold">Kartu properti</h2>
            <div class="mt-6 grid gap-6 border border-line p-6 sm:grid-cols-2 lg:grid-cols-3">
                <article class="border border-line bg-white transition-colors duration-150 hover:border-ink-950">
                    <div class="relative flex aspect-[4/3] items-center justify-center bg-sand">
                        <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto">
                        <span class="absolute right-3 top-3 rounded border border-ink-950 bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Dijual</span>
                    </div>
                    <div class="space-y-3 p-4">
                        <p class="text-xs">
                            <span class="font-semibold text-ink-950">Primer</span>
                            <span class="text-ink-500">· Bintaro Development</span>
                        </p>
                        <h3 class="text-lg font-semibold leading-snug">Rumah Cluster Bintaro</h3>
                        <p class="text-sm text-ink-500">Bintaro, Tangerang Selatan</p>
                        <p class="tabular text-lg font-semibold">Rp 1.250.000.000</p>
                        <p class="text-sm text-ink-600">3 KT · 2 KM · 1 carport · LT 120 m²</p>
                    </div>
                </article>

                <article class="border border-line bg-white">
                    <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                        <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto">
                    </div>
                    <div class="space-y-3 p-4">
                        <p class="text-xs">
                            <span class="font-semibold text-ink-950">Sekunder</span>
                        </p>
                        <h3 class="text-lg font-semibold leading-snug">Apartemen Sudirman Park</h3>
                        <p class="text-sm text-ink-500">Sudirman, Jakarta Pusat</p>
                        <p class="tabular text-lg font-semibold">Rp 850.000.000</p>
                        <p class="text-sm text-ink-600">2 KT · 2 KM · 1 carport</p>
                    </div>
                </article>
            </div>
        </section>

        <section aria-labelledby="tabel">
            <h2 id="tabel" class="text-xl font-semibold">Tabel admin</h2>
            <div class="mt-6 overflow-x-auto border border-line">
                <table class="w-full text-left text-sm">
                    <thead class="bg-sand text-xs font-semibold text-ink-600">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-line hover:bg-paper">
                            <td class="whitespace-nowrap px-4 py-3">Rumah Cluster Bintaro</td>
                            <td class="whitespace-nowrap px-4 py-3">Bintaro</td>
                            <td class="tabular whitespace-nowrap px-4 py-3 text-right">Rp 1.250.000.000</td>
                            <td class="px-4 py-3">Dijual</td>
                        </tr>
                        <tr class="border-t border-line hover:bg-paper">
                            <td class="whitespace-nowrap px-4 py-3">Apartemen Sudirman Park</td>
                            <td class="whitespace-nowrap px-4 py-3">Sudirman</td>
                            <td class="tabular whitespace-nowrap px-4 py-3 text-right">Rp 850.000.000</td>
                            <td class="px-4 py-3">Dijual</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="kosong">
            <h2 id="kosong" class="text-xl font-semibold">Empty state</h2>
            <div class="mt-6 border border-line p-10 text-center">
                <p class="font-semibold">Belum ada properti yang cocok.</p>
                <p class="mt-2 text-sm text-ink-600">Ubah filter lokasi atau harga, lalu coba lagi.</p>
                <x-secondary-button class="mt-6">Reset filter</x-secondary-button>
            </div>
        </section>
    </div>
@endsection
