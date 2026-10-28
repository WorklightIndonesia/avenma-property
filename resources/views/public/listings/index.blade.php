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

                <div class="mt-8 flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-line pt-5 text-sm">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <span class="text-ink-500">
                            Menampilkan {{ $listings->firstItem() }}–{{ $listings->lastItem() }} dari {{ $listings->total() }}
                        </span>

                        <form method="GET" action="{{ route('public.listings.index') }}" class="flex items-center gap-2">
                            @foreach (Arr::except(request()->query(), ['per', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <label for="per" class="text-ink-500">Per halaman</label>
                            <select
                                id="per"
                                name="per"
                                onchange="this.form.submit()"
                                class="min-h-11 w-20 rounded border border-line bg-white px-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30"
                            >
                                @foreach (App\Models\Listing::PER_PAGE_OPTIONS as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>

                            <noscript>
                                <button type="submit" class="min-h-11 rounded border border-line px-3 text-sm font-semibold">Terapkan</button>
                            </noscript>
                        </form>
                    </div>

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
