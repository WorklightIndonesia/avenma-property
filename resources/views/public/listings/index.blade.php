@extends('layouts.public')

@section('title', 'Properti — Avenma Properti')

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h1 class="text-3xl font-semibold tracking-tight">Properti</h1>
            <p class="mt-1 text-sm text-ink-500">{{ $listings->total() }} properti ditemukan</p>

            @include('public.listings._filters', ['action' => route('public.listings.index')])

            @if ($listings->isEmpty())
                <div class="mt-8 border border-line p-10 text-center">
                    <p class="font-semibold">Belum ada properti yang cocok.</p>
                    <p class="mt-2 text-sm text-ink-600">Ubah atau hapus filter, lalu coba lagi.</p>
                    <a href="{{ route('public.listings.index') }}" class="mt-6 inline-flex items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 hover:bg-sand">Hapus semua filter</a>
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($listings as $listing)
                        <x-property-card :listing="$listing" />
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5 text-sm">
                    <span class="text-ink-500">
                        Menampilkan {{ $listings->firstItem() }}–{{ $listings->lastItem() }} dari {{ $listings->total() }}
                    </span>

                    <div class="flex items-center gap-1">
                        @if ($listings->onFirstPage())
                            <span class="px-3 py-1.5 text-ink-500">Sebelumnya</span>
                        @else
                            <a href="{{ $listings->previousPageUrl() }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">Sebelumnya</a>
                        @endif

                        @foreach ($listings->getUrlRange(max(1, $listings->currentPage() - 2), min($listings->lastPage(), $listings->currentPage() + 2)) as $page => $url)
                            @if ($page === $listings->currentPage())
                                <span aria-current="page" class="border border-ink-950 px-3 py-1.5 font-semibold">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($listings->hasMorePages())
                            <a href="{{ $listings->nextPageUrl() }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">Berikutnya</a>
                        @else
                            <span class="px-3 py-1.5 text-ink-500">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
