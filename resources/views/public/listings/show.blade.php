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
                        <div class="relative">
                            @if ($cover)
                                <img src="{{ Storage::url($cover->path) }}" alt="{{ $cover->alt ?: $photoAlt }}" class="aspect-[4/3] w-full object-cover">
                            @else
                                {{-- shortcut: foto properti belum ada, ganti blok sand dengan gambar listing saat file tersedia (Task 14) --}}
                                <div class="flex aspect-[4/3] items-center justify-center bg-sand">
                                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="80" height="56" class="h-14 w-auto" onerror="this.remove()">
                                </div>
                            @endif

                            <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($listing->status) }}</span>
                        </div>

                        @if ($listing->images->count() > 1)
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($listing->images->skip(1)->take(4) as $image)
                                    <img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt ?: $photoAlt }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                @endforeach
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
