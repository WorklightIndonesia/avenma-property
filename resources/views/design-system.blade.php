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
@endphp

@section('content')
    <div class="mx-auto max-w-7xl space-y-20 px-4 py-16 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Design System</h1>
            <p class="mt-3 max-w-xl text-ink-600">
                Halaman hidup untuk token dan komponen. Hanya tersedia di luar produksi. Kontrak lengkap ada di <code class="text-sm">DESIGN.md</code>.
            </p>
        </header>

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
                <span class="rounded bg-ink-950 px-2 py-0.5 text-xs font-semibold text-paper">Dijual</span>
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
                        <img src="{{ asset('images/brand/avenma-logo-wordmark.png') }}" alt="" class="w-32">
                        <span class="absolute right-3 top-3 rounded bg-ink-950 px-2 py-0.5 text-xs font-semibold text-paper">Dijual</span>
                    </div>
                    <div class="space-y-3 p-4">
                        <h3 class="text-lg font-semibold leading-snug">Rumah Cluster Bintaro</h3>
                        <p class="text-sm text-ink-500">Bintaro, Tangerang Selatan</p>
                        <p class="tabular text-lg font-semibold">Rp 1.250.000.000</p>
                        <p class="text-sm text-ink-600">3 KT · 2 KM · 1 carport · LT 120 m²</p>
                    </div>
                </article>

                <article class="border border-line bg-white">
                    <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                        <img src="{{ asset('images/brand/avenma-wordmark-ink.png') }}" alt="Avenma Properti" class="w-28">
                    </div>
                    <div class="space-y-3 p-4">
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
