@extends('layouts.public')

@section('title', $listing->title.' — Avenma Properti')
@section('meta_description', Str::limit(strip_tags($listing->description), 155))

@php
    $type = ['primary' => 'Primer', 'secondary' => 'Sekunder'][$listing->listing_type] ?? $listing->listing_type;
    $badge = [
        'dijual' => 'border border-ink-950 bg-paper text-ink-950',
        'disewa' => 'bg-gold-700 text-white',
        'baru' => 'bg-gold-300 text-ink-950',
    ][$listing->status] ?? 'border border-ink-950 bg-paper text-ink-950';

    $specs = array_filter([
        'Kamar tidur' => $listing->bedrooms,
        'Kamar mandi' => $listing->bathrooms,
        'Carport' => $listing->carport,
        'Luas tanah' => $listing->land_area ? $listing->land_area.' m²' : null,
        'Luas bangunan' => $listing->building_area ? $listing->building_area.' m²' : null,
    ], fn ($value) => $value !== null);

    $specCols = [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-2 sm:grid-cols-3', 4 => 'grid-cols-2 sm:grid-cols-4', 5 => 'grid-cols-2 sm:grid-cols-5'][count($specs)] ?? 'grid-cols-2';

    $cover = $listing->images->first();
    $photoAlt = $listing->title.($listing->location ? ' di '.$listing->location->name : '');
    $gallery = $listing->images->map(fn ($image) => [
        'src' => asset($image->path),
        'alt' => $image->alt ?: $photoAlt,
    ])->values();
    $mapSrc = $listing->mapEmbedSrc();
    $price = 'Rp '.number_format($listing->price, 0, ',', '.');
    $rupiah = fn (float $amount) => 'Rp '.number_format($amount, 0, ',', '.');
    $percent = fn (float $value) => rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    $inputClass = 'mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
@endphp

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-ink-500">
                <nav aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('public.listings.index') }}" class="hover:text-ink-950">Properti</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page" class="text-ink-700">{{ $listing->title }}</li>
                    </ol>
                </nav>

                <p>Diperbarui {{ $listing->updated_at->locale('id')->translatedFormat('j F Y') }}</p>
            </div>

            <div class="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div>
                    <div>
                        <p class="text-xs">
                            <span class="font-semibold text-ink-950">{{ $type }}</span>
                            <span class="text-ink-500">· {{ ucfirst($listing->property_type) }}</span>
                            @if ($listing->developer)
                                <span class="text-ink-500">· {{ $listing->developer->name }}</span>
                            @endif
                        </p>

                        <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $listing->title }}</h1>

                        @if ($listing->address || $listing->location)
                            <p class="mt-2 text-sm text-ink-500">{{ $listing->address ?: $listing->location->name }}</p>
                        @endif

                        <p class="tabular mt-4 text-2xl font-semibold lg:hidden">{{ $price }}</p>

                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white lg:hidden">
                                {{ $contactLabel }}
                            </a>
                        @endif
                    </div>

                    <div class="mt-6 space-y-2">
                        @if ($cover)
                            <div x-data="{ active: 0, photos: @js($gallery) }">
                                <div class="relative">
                                    <img src="{{ asset($cover->path) }}" :src="photos[active].src" alt="{{ $cover->alt ?: $photoAlt }}" :alt="photos[active].alt" class="aspect-[4/3] w-full object-cover">
                                    <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($listing->status) }}</span>
                                </div>

                                @if ($listing->images->count() > 1)
                                    <div class="mt-2 grid grid-cols-4 gap-2">
                                        @foreach ($listing->images as $image)
                                            <button
                                                type="button"
                                                @click="active = {{ $loop->index }}"
                                                :class="active === {{ $loop->index }} ? 'border-ink-950' : 'border-line'"
                                                class="border p-0.5 hover:border-ink-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700"
                                                aria-label="Tampilkan foto {{ $loop->iteration }}"
                                            >
                                                <img src="{{ asset($image->path) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="relative flex aspect-[4/3] items-center justify-center bg-sand">
                                <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="80" height="56" class="h-14 w-auto" onerror="this.remove()">
                                <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($listing->status) }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($specs !== [])
                        <dl class="mt-6 grid gap-px border border-line bg-line {{ $specCols }}">
                            @foreach ($specs as $label => $value)
                                <div class="bg-paper p-4">
                                    <dt class="text-xs text-ink-500">{{ $label }}</dt>
                                    <dd class="mt-1 text-base font-semibold">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif

                    <section class="mt-10 border-t border-line pt-8">
                        <h2 class="text-lg font-semibold">Deskripsi</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink-700">{{ $listing->description }}</p>
                    </section>

                    @if ($mapSrc)
                        <section class="mt-10 border-t border-line pt-8">
                            <h2 class="text-lg font-semibold">Lokasi</h2>

                            @if ($listing->address)
                                <p class="mt-2 text-sm text-ink-600">{{ $listing->address }}</p>
                            @endif

                            <iframe src="{{ $mapSrc }}" title="Peta lokasi {{ $listing->title }}" class="mt-4 h-80 w-full border border-line" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </section>
                    @endif

                    <section id="kpr" class="mt-10 scroll-mt-24 border-t border-line pt-8">
                        <h2 class="text-lg font-semibold">Simulasi KPR</h2>
                        <p class="mt-1 text-sm text-ink-500">Perkiraan cicilan dengan bunga tetap. Bukan penawaran resmi bank.</p>

                        <form method="GET" action="{{ route('public.listings.show', $listing) }}#kpr" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <input type="hidden" name="hitung" value="1">

                            <div>
                                <label for="kpr-harga" class="block text-xs font-medium text-ink-500">Harga properti (Rp)</label>
                                <input id="kpr-harga" name="harga" type="number" inputmode="numeric" min="1" step="1000000" value="{{ $kpr['price'] }}" class="{{ $inputClass }}">
                                <p class="mt-1 text-xs text-ink-500">{{ $rupiah($kpr['price']) }}</p>
                            </div>

                            <div>
                                <label for="kpr-dp" class="block text-xs font-medium text-ink-500">Uang muka (%)</label>
                                <input id="kpr-dp" name="dp" type="number" inputmode="decimal" min="0" max="90" step="1" value="{{ $kpr['dpPercent'] }}" class="{{ $inputClass }}">
                                <p class="mt-1 text-xs text-ink-500">{{ $rupiah($kpr['dpAmount']) }}</p>
                            </div>

                            <div>
                                <label for="kpr-tenor" class="block text-xs font-medium text-ink-500">Tenor</label>
                                <select id="kpr-tenor" name="tenor" class="{{ $inputClass }}">
                                    @foreach ($kpr['tenorOptions'] as $years)
                                        <option value="{{ $years }}" @selected($kpr['tenor'] === $years)>{{ $years }} tahun</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="kpr-bunga" class="block text-xs font-medium text-ink-500">Bunga per tahun (%)</label>
                                <input id="kpr-bunga" name="bunga" type="number" inputmode="decimal" min="0" max="100" step="0.1" value="{{ $kpr['rate'] }}" class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2 lg:col-span-4">
                                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded bg-ink-950 px-5 text-sm font-semibold text-paper hover:bg-ink-700">Hitung cicilan</button>
                            </div>
                        </form>

                        @if ($kpr['submitted'])
                            <div class="mt-6 border border-line bg-white p-5">
                                <p class="text-xs font-medium text-ink-500">Cicilan per bulan</p>
                                <p class="tabular mt-1 text-2xl font-semibold">{{ $rupiah($kpr['monthly']) }}</p>

                                <dl class="mt-4 grid gap-x-8 gap-y-2 border-t border-line pt-4 text-sm sm:grid-cols-2">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-500">Harga properti</dt>
                                        <dd class="tabular font-medium">{{ $rupiah($kpr['price']) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-500">Uang muka ({{ $percent($kpr['dpPercent']) }}%)</dt>
                                        <dd class="tabular font-medium">{{ $rupiah($kpr['dpAmount']) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-500">Pokok pinjaman</dt>
                                        <dd class="tabular font-medium">{{ $rupiah($kpr['principal']) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-500">Total pembayaran</dt>
                                        <dd class="tabular font-medium">{{ $rupiah($kpr['total']) }}</dd>
                                    </div>
                                </dl>

                                <p class="mt-4 text-xs text-ink-500">Bunga tetap {{ $percent($kpr['rate']) }}% per tahun, tenor {{ $kpr['tenor'] }} tahun ({{ $kpr['months'] }} bulan). Angka ini perkiraan, bukan penawaran resmi bank.</p>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="border border-line bg-white p-5">
                        <p class="hidden text-xs font-medium text-ink-500 lg:block">Harga</p>
                        <p class="tabular mt-1 hidden text-2xl font-semibold lg:block">{{ $price }}</p>

                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="mt-1 hidden min-h-12 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white lg:mt-5 lg:inline-flex">
                                {{ $contactLabel }}
                            </a>
                        @endif

                        <a href="#kpr" class="mt-3 hidden min-h-11 w-full items-center justify-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand lg:inline-flex">Simulasi KPR</a>

                        @if ($listing->developer || $listing->creator)
                            <dl class="mt-5 space-y-4 border-t border-line pt-5 text-sm">
                                @if ($listing->developer)
                                    <div>
                                        <dt class="text-ink-500">Developer</dt>
                                        <dd class="mt-0.5 font-medium">{{ $listing->developer->name }}</dd>
                                    </div>
                                @endif

                                @if ($listing->creator)
                                    <div>
                                        <dt class="text-ink-500">Agen penjual</dt>
                                        <dd class="mt-0.5 font-medium">{{ $listing->creator->name }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
