<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>Perbandingan Tipografi — Avenma Properti</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600|syne:600|cormorant-garamond:600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])

        <style>
            .spec h1, .spec h2, .spec h3, .spec .quote { text-wrap: balance; }

            .spec h1 { font-weight: 600; line-height: 1.1; letter-spacing: -0.02em; font-size: 30px; }
            .spec h2 { font-weight: 600; line-height: 1.2; letter-spacing: -0.02em; font-size: 20px; }
            .spec h3 { font-weight: 600; line-height: 1.3; letter-spacing: -0.01em; font-size: 17px; }

            .v-syne h1, .v-syne h2, .v-syne h3 { font-family: Syne, ui-sans-serif, sans-serif; }

            .spec .label { font-family: "Plus Jakarta Sans", ui-sans-serif, sans-serif; font-size: 14px; line-height: 1.4; letter-spacing: normal; }

            .v-corm h1, .v-corm h2, .v-corm h3, .v-corm .quote {
                font-family: "Cormorant Garamond", Georgia, serif;
                letter-spacing: 0;
            }
            .v-corm h1 { font-size: 35px; line-height: 1.02; }
            .v-corm h2 { font-size: 23px; line-height: 1.1; }
            .v-corm h3 { font-size: 19.5px; line-height: 1.2; }
        </style>
    </head>
    <body class="bg-paper font-sans text-ink-950 antialiased">
        @php
            $variants = [
                ['key' => 'base', 'name' => 'Sekarang', 'note' => 'Plus Jakarta Sans 400/500/600, satu keluarga'],
                ['key' => 'syne', 'name' => 'Syne', 'note' => 'Syne 600 untuk display, Plus Jakarta Sans untuk teks'],
                ['key' => 'corm', 'name' => 'Cormorant Garamond', 'note' => 'Cormorant 600 untuk display, ukuran dikompensasi +15%'],
            ];
        @endphp

        <header class="border-b border-line">
            <div class="mx-auto flex max-w-7xl flex-wrap items-baseline justify-between gap-2 px-4 py-4 sm:px-6 lg:px-8">
                <h1 class="text-sm font-semibold">Perbandingan tipografi</h1>
                <p class="text-sm text-ink-500">Isi dan ukuran sama di ketiga kolom; harga dan label tetap Plus Jakarta Sans.</p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid gap-px border border-line bg-line lg:grid-cols-3">
                @foreach ($variants as $variant)
                    <section class="spec v-{{ $variant['key'] }} space-y-8 bg-paper p-6" aria-labelledby="varian-{{ $variant['key'] }}">
                        <div>
                            <h2 id="varian-{{ $variant['key'] }}" class="label font-semibold">{{ $variant['name'] }}</h2>
                            <p class="mt-1 text-xs text-ink-500">{{ $variant['note'] }}</p>
                        </div>

                        <div class="bg-ink-950 p-6 text-paper">
                            <h1>Temukan properti yang tepat untuk Anda.</h1>
                            <p class="mt-4 text-[15px] leading-relaxed text-paper/70">
                                Katalog properti primer dan sekunder pilihan, lengkap dengan informasi lokasi dan simulasi KPR.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <span class="rounded bg-gold-500 px-4 py-2.5 text-sm font-semibold text-ink-950">Lihat Properti</span>
                                <span class="rounded border border-paper/25 px-4 py-2.5 text-sm font-semibold text-paper">Hubungi Kami</span>
                            </div>
                        </div>

                        <div>
                            <h2>Properti pilihan</h2>
                            <article class="mt-4 border border-line bg-white">
                                <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                                </div>
                                <div class="space-y-2 p-4">
                                    <h3>Rumah Cluster Bintaro</h3>
                                    <p class="text-sm text-ink-500">Bintaro, Tangerang Selatan</p>
                                    <p class="tabular text-[17px] font-semibold">Rp 1.250.000.000</p>
                                    <p class="text-sm text-ink-600">3 KT · 2 KM · 1 carport · LT 120 m²</p>
                                </div>
                            </article>
                        </div>

                        <div>
                            <h3>Panduan membeli rumah pertama</h3>
                            <p class="mt-2 text-[15px] leading-relaxed text-ink-600">
                                Sebelum akad, pastikan sertifikat, pajak, dan biaya notaris sudah masuk hitungan. Tiga dokumen ini yang paling sering tertinggal.
                            </p>
                            <p class="quote mt-4 border-l border-line pl-4 text-[19px] leading-snug text-ink-950">
                                “Kami memilih rumah ini karena cahaya paginya.”
                            </p>
                        </div>

                        <div class="border-t border-line pt-4">
                            <p class="text-xs text-ink-500">Label, harga, dan angka tetap Plus Jakarta Sans di semua varian.</p>
                            <p class="tabular mt-2 text-sm font-semibold">Rp 850.000.000 · 2 KT · 2 KM</p>
                        </div>
                    </section>
                @endforeach
            </div>

            <p class="mt-6 text-sm text-ink-500">
                Halaman ini hanya ada di luar produksi. <a href="{{ url('/') }}" class="underline hover:text-ink-950">Kembali ke beranda</a>.
            </p>
        </main>
    </body>
</html>
