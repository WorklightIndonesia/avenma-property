@extends('layouts.public')

@section('meta')
    <x-meta
        title="Properti"
        description="Katalog properti primer dan sekunder di Indonesia. Saring berdasarkan lokasi resmi, tipe, status, harga, jumlah kamar, dan developer."
    />
@endsection

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h1 class="text-3xl font-semibold tracking-tight">Properti</h1>
            <p class="mt-1 text-sm text-ink-500">{{ $listings->total() }} properti ditemukan</p>

            <div class="mt-8 grid gap-8 lg:grid-cols-[15rem_1fr] lg:items-start lg:gap-10">
                <div>
                    @include('public.listings._filters', ['action' => route('public.listings.index')])
                </div>

                <div>
                    @if ($listings->isEmpty())
                        <div class="border border-line p-10 text-center">
                            <p class="font-semibold">Belum ada properti yang cocok.</p>
                            <p class="mt-2 text-sm text-ink-600">Ubah atau hapus filter, lalu coba lagi.</p>
                            <a href="{{ route('public.listings.index') }}" class="mt-6 inline-flex items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 hover:bg-sand">Hapus semua filter</a>
                        </div>
                    @else
                        <h2 class="sr-only">Hasil pencarian properti</h2>

                        <div class="mb-5 flex items-center justify-end gap-2 text-sm">
                            <span class="text-ink-500">Urutkan</span>

                            <form method="GET" action="{{ route('public.listings.index') }}">
                                @foreach (Arr::except(request()->query(), ['urut', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach

                                <x-select
                                    name="urut"
                                    :options="App\Models\Listing::SORTS"
                                    :value="$sort"
                                    aria-label="Urutkan properti"
                                    :submit="true"
                                    class="w-44"
                                />
                            </form>
                        </div>

                        <div class="grid gap-x-6 gap-y-8 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($listings as $listing)
                                <x-property-card :listing="$listing" />
                            @endforeach
                        </div>

                        <div class="mt-10 flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-line pt-5 text-sm">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span class="text-ink-500">
                                    Menampilkan {{ $listings->firstItem() }}–{{ $listings->lastItem() }} dari {{ $listings->total() }}
                                </span>

                                <form method="GET" action="{{ route('public.listings.index') }}" class="flex items-center gap-2">
                                    @foreach (Arr::except(request()->query(), ['per', 'page']) as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach

                                    <label for="per" class="text-ink-500">Per halaman</label>
                                    <x-select
                                        id="per"
                                        name="per"
                                        :options="collect(App\Models\Listing::PER_PAGE_OPTIONS)->mapWithKeys(fn (int $option) => [$option => (string) $option])->all()"
                                        :value="$perPage"
                                        aria-label="Jumlah properti per halaman"
                                        :submit="true"
                                        class="w-24"
                                    />

                                    <noscript>
                                        <button type="submit" class="min-h-11 rounded border border-line px-3 text-sm font-semibold">Terapkan</button>
                                    </noscript>
                                </form>
                            </div>

                            <x-pagination :paginator="$listings" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
