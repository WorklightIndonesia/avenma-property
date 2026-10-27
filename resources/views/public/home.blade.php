@extends('layouts.public')

@section('title', 'Avenma Properti — Katalog Properti Pilihan')

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="text-sm text-ink-500">Properti primer dan sekunder</p>
                    <h1 class="mt-4 max-w-xl text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl">
                        Temukan properti yang tepat untuk Anda.
                    </h1>
                    <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-600">
                        Katalog properti pilihan di Jabodetabek, lengkap dengan informasi lokasi dan simulasi KPR.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ url('/properti') }}" class="inline-flex items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                            Lihat Properti
                        </a>
                        <a href="{{ url('/kontak') }}" class="inline-flex items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:border-ink-950">
                            Hubungi Kami
                        </a>
                    </div>
                </div>

                {{-- shortcut: foto asli belum ada dari klien, ganti blok ini dengan tag gambar saat file tersedia --}}
                <div class="flex aspect-[16/9] flex-col items-center justify-center bg-sand lg:aspect-[4/3]">
                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                    <p class="mt-3 text-xs text-ink-500">Foto properti utama</p>
                </div>
            </div>
        </div>
    </section>
@endsection
