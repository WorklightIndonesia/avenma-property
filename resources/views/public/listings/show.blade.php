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

            <div class="mt-6">
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

                <div class="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="min-w-0">
                        <div class="space-y-2">
                            @if ($cover)
                                <div x-data="{ active: 0, photos: @js($gallery), next() { this.active = (this.active + 1) % this.photos.length }, prev() { this.active = (this.active - 1 + this.photos.length) % this.photos.length } }">
                                    <div class="relative">
                                        <img src="{{ asset($cover->path) }}" :src="photos[active].src" alt="{{ $cover->alt ?: $photoAlt }}" :alt="photos[active].alt" class="aspect-[4/3] w-full object-cover">
                                        <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($listing->status) }}</span>

                                        @if ($listing->images->count() > 1)
                                            <button type="button" @click="prev()" aria-label="Foto sebelumnya" class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-line bg-paper/90 text-ink-950 transition-colors duration-150 hover:bg-paper focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <button type="button" @click="next()" aria-label="Foto berikutnya" class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-line bg-paper/90 text-ink-950 transition-colors duration-150 hover:bg-paper focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <p class="absolute bottom-3 right-3 rounded bg-ink-950/70 px-2 py-0.5 text-xs font-semibold text-paper" aria-live="polite">
                                                <span x-text="active + 1">{{ 1 }}</span> / {{ $listing->images->count() }}
                                            </p>
                                        @endif
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

                        <x-kpr-simulation
                            class="mt-4"
                            :price="$kpr['price']"
                            :interest="$kpr['rate']"
                            :min-dp="$kpr['minDp']"
                            :max-dp="$kpr['maxDp']"
                            :max-tenor="$kpr['maxTenor']"
                            :cta-url="$whatsappUrl"
                            cta-label="Konsultasi KPR via WhatsApp"
                        />
                    </section>
                </div>

                <aside class="hidden lg:sticky lg:top-24 lg:block lg:self-start">
                    <div class="border border-line bg-white p-5">
                        <p class="text-xs font-medium text-ink-500">Harga</p>
                        <p class="tabular mt-1 text-2xl font-semibold">{{ $price }}</p>

                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                {{ $contactLabel }}
                            </a>
                        @endif

                        <a href="#kpr" class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Simulasi KPR</a>

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
