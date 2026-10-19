@extends('layouts.public')

@section('title', 'Avenma Property — Katalog Properti Pilihan')

@section('content')
    <section class="bg-slate-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-400">Avenma Property</p>
            <h1 class="mt-4 max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl">
                Temukan properti yang tepat untuk Anda.
            </h1>
            <p class="mt-6 max-w-xl text-lg text-slate-300">
                Katalog properti primer dan sekunder pilihan, lengkap dengan informasi lokasi dan simulasi KPR.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ url('/properti') }}" class="rounded-lg bg-emerald-600 px-6 py-3 font-semibold text-white hover:bg-emerald-700">
                    Lihat Properti
                </a>
                <a href="{{ url('/kontak') }}" class="rounded-lg border border-white/20 px-6 py-3 font-semibold text-white hover:bg-white/10">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>
@endsection
