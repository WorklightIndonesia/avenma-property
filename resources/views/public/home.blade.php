@extends('layouts.public')

@section('title', 'Avenma Properti — Katalog Properti Pilihan')

@section('content')
    <section class="bg-ink-950 text-paper">
        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
            <h1 class="max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl">
                Temukan properti yang tepat untuk Anda.
            </h1>
            <p class="mt-6 max-w-xl text-lg text-paper/70">
                Katalog properti primer dan sekunder pilihan, lengkap dengan informasi lokasi dan simulasi KPR.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ url('/properti') }}" class="inline-flex items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                    Lihat Properti
                </a>
                <a href="{{ url('/kontak') }}" class="inline-flex items-center rounded border border-paper/25 px-5 py-3 text-sm font-semibold text-paper transition-colors duration-150 hover:bg-paper/10">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>
@endsection
