@extends('layouts.public')

@section('meta')
    @php
        $heroImage = $page->section('hero')?->image;
    @endphp

    <x-meta
        :title="$page->meta_title ?: $page->title"
        :description="$page->meta_description ?: 'Avenma Properti - katalog properti primer dan sekunder.'"
        :image="$heroImage ? asset($heroImage) : null"
    />
@endsection

@section('content')
    @php
        $hero = $page->section('hero');
        $isHome = $pageKey === 'home';

        // Every section after the hero is rendered in the order the skeleton
        // defines, so an empty one simply disappears instead of leaving a gap.
        $rest = collect($sections)
            ->except('hero')
            ->map(fn ($meta, $key) => $page->section($key))
            ->filter(fn ($section) => $section && ! $section->isEmpty());
    @endphp

    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="text-sm text-ink-500">{{ $isHome ? 'Properti primer dan sekunder' : $page->title }}</p>

                    <h1 class="mt-4 max-w-xl text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl">
                        {{ $hero?->heading ?: $page->title }}
                    </h1>

                    @if (filled($hero?->body))
                        <div class="rich-text mt-5 max-w-lg text-lg">{!! $hero->body !!}</div>
                    @endif

                    @if ((filled($hero?->cta_label) && filled($hero->cta_url)) || (filled($hero?->cta_label_2) && filled($hero->cta_url_2)))
                        <div class="mt-8 flex flex-wrap gap-4">
                            @if (filled($hero->cta_label) && filled($hero->cta_url))
                                <a href="{{ $hero->cta_url }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                    {{ $hero->cta_label }}
                                </a>
                            @endif

                            @if (filled($hero->cta_label_2) && filled($hero->cta_url_2))
                                <a href="{{ $hero->cta_url_2 }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:border-ink-950">
                                    {{ $hero->cta_label_2 }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($hero?->image)
                    <img
                        src="{{ asset($hero->image) }}"
                        alt="{{ $hero->image_alt }}"
                        class="aspect-[4/3] w-full object-cover lg:aspect-[4/3]"
                    >
                @else
                    <div class="flex aspect-[16/9] flex-col items-center justify-center bg-sand lg:aspect-[4/3]">
                        <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                        <p class="mt-3 text-xs text-ink-500">{{ $isHome ? 'Foto properti utama' : 'Foto halaman' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @foreach ($rest as $section)
        <section class="border-t border-line bg-paper">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                @if (filled($section->heading))
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $section->heading }}</h2>
                @endif

                @if (filled($section->body))
                    <div class="rich-text mt-4">{!! $section->body !!}</div>
                @endif

                @if (filled($section->cta_label) && filled($section->cta_url))
                    <a href="{{ $section->cta_url }}" class="mt-6 inline-flex min-h-11 items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                        {{ $section->cta_label }}
                    </a>
                @endif
            </div>
        </section>
    @endforeach

    @if ($pageKey === 'kontak')
        <section class="border-t border-line bg-paper">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <h2 class="text-2xl font-semibold tracking-tight">Hubungi Avenma</h2>

                <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                    @if ($settings->whatsappUrl())
                        <div>
                            <dt class="text-sm font-semibold text-ink-950">WhatsApp</dt>
                            <dd class="mt-1">
                                <a href="{{ $settings->whatsappUrl() }}" class="inline-flex min-h-11 items-center text-ink-600 underline hover:text-ink-950">
                                    {{ $settings->whatsapp }}
                                </a>
                            </dd>
                        </div>
                    @endif

                    @if (filled($settings->email))
                        <div>
                            <dt class="text-sm font-semibold text-ink-950">Email</dt>
                            <dd class="mt-1">
                                <a href="mailto:{{ $settings->email }}" class="inline-flex min-h-11 items-center text-ink-600 underline hover:text-ink-950">
                                    {{ $settings->email }}
                                </a>
                            </dd>
                        </div>
                    @endif

                    @if (filled($settings->address))
                        <div>
                            <dt class="text-sm font-semibold text-ink-950">Alamat</dt>
                            <dd class="mt-1 text-ink-600">{{ $settings->address }}</dd>
                        </div>
                    @endif

                    @if ($settings->instagramUrl())
                        <div>
                            <dt class="text-sm font-semibold text-ink-950">Instagram</dt>
                            <dd class="mt-1">
                                <a href="{{ $settings->instagramUrl() }}" rel="noopener" target="_blank" class="inline-flex min-h-11 items-center text-ink-600 underline hover:text-ink-950">
                                    {{ '@'.$settings->instagram }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if (filled($settings->address))
                    <iframe
                        title="Peta lokasi kantor Avenma"
                        src="https://maps.google.com/maps?q={{ rawurlencode($settings->address) }}&output=embed"
                        class="mt-8 h-72 w-full border border-line"
                        loading="lazy"
                    ></iframe>
                @endif
            </div>
        </section>
    @endif

    @if ($isHome && $listings->isNotEmpty())
        <section class="border-t border-line bg-sand">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">Properti pilihan</h2>
                        <p class="mt-1 text-sm text-ink-500">Lima properti terbaru, unggulan lebih dulu.</p>
                    </div>

                    <a href="{{ route('public.listings.index') }}" class="text-sm font-semibold text-ink-950 underline hover:text-gold-700">
                        Lihat semua properti
                    </a>
                </div>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($listings as $listing)
                        <x-property-card :listing="$listing" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
