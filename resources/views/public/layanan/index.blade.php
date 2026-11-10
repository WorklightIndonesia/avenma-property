@extends('layouts.public')

@section('meta')
    <x-meta
        :title="$page?->meta_title ?: 'Layanan Properti'"
        :description="$page?->meta_description ?: 'Jual beli, sewa, pemasaran, konsultasi, dan pengelolaan properti. Satu tim Avenma Property untuk semuanya.'"
    />
@endsection

@php
    $sisters = App\Support\SisterService::links();
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <section class="pt-10 lg:pt-12">
            <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,29rem)] lg:items-center lg:gap-10">
                <div class="min-w-0">
                    <h1 class="text-5xl font-semibold leading-[1.05] tracking-[-0.03em] sm:text-6xl xl:text-7xl">
                        {{ $hero?->heading ?: 'Dari menawarkan sampai mengurus properti.' }}
                    </h1>

                    @if (filled($hero?->body))
                        <div class="rich-text mt-5 max-w-prose text-lg leading-8 text-ink-600">{!! $hero->body !!}</div>
                    @endif

                    <a href="{{ $heroCta }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-14 items-center rounded bg-gold-500 px-6 text-base font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                        {{ $hero?->cta_label ?: 'Diskusikan via WhatsApp' }}
                        <span class="sr-only">(tab baru)</span>
                    </a>
                </div>

                {{-- The one asset this page can honestly own: a drawing sheet,
                     because the drawings are the product. No stock photo. --}}
                <figure class="mt-8 lg:mt-0">
                    {{-- Cropped tight to the walls: the sheet margin was eating a
                         tenth of the drawing's width. --}}
                    <svg viewBox="48 28 384 252" class="w-full text-ink-700" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Contoh denah lantai: ruang tamu, kamar tidur, dan area kerja">
                        {{-- feDisplacementMap wobbles the straight lines into a
                             hand-drawn stroke; the labels stay outside it so they
                             never wobble into illegibility. --}}
                        <defs>
                            <filter id="denah-sketch" x="-8%" y="-8%" width="116%" height="116%">
                                <feTurbulence type="fractalNoise" baseFrequency="0.02" numOctaves="2" seed="7" result="noise" />
                                <feDisplacementMap in="SourceGraphic" in2="noise" scale="3" xChannelSelector="R" yChannelSelector="G" />
                            </filter>
                        </defs>

                        <g filter="url(#denah-sketch)">
                            <rect x="60" y="40" width="360" height="220" />
                            <rect x="68" y="48" width="344" height="204" />

                            <path d="M250 48v68M250 160v92" />
                            <path d="M68 160h62M172 160h78" />

                            <path d="M292 160a42 42 0 0 0-42-42" />
                            <path d="M130 160v-42M130 118a42 42 0 0 1 42 42" />

                            <path d="M300 40v10M350 40v10M300 45h50" />
                            <path d="M60 84h10M60 124h10M65 84v40" />
                        </g>

                        <g fill="currentColor" stroke="none" font-size="17" letter-spacing="1" text-anchor="middle">
                            <text x="170" y="96">RUANG TAMU</text>
                            <text x="159" y="220">K. TIDUR</text>
                            <text x="336" y="206">AREA KERJA</text>
                        </g>
                    </svg>

                    <figcaption class="mt-4 flex items-center justify-between border-t border-line pt-3 text-xs uppercase tracking-[0.12em] text-ink-500">
                        <span>Denah</span>
                        <span>Skala 1:100</span>
                        <span>A-01</span>
                    </figcaption>
                </figure>
            </div>
        </section>

        {{-- One Alpine component owns the open row, so closing the previous
             row can animate instead of snapping. The panel is a one-row grid
             whose track goes 0fr -> 1fr; that animates the real height without
             measuring it. `inert` keeps a closed row out of the tab order. The
             row ids are the config slugs, so /layanan#property-management is a
             shareable anchor; openHash opens whichever row the hash names. --}}
        <section
            class="mt-8 border border-line bg-white lg:mt-10"
            aria-label="Layanan"
            x-data="{
                active: 0,
                toggle(index) { this.active = this.active === index ? null : index; },
                openHash() {
                    const slug = window.location.hash.slice(1);
                    if (! slug) return;
                    const rows = [...this.$el.querySelectorAll('[data-layanan-row]')];
                    const index = rows.findIndex((row) => row.id === slug);
                    if (index !== -1) this.active = index;
                },
                init() { this.openHash(); },
            }"
            @hashchange.window="openHash()"
        >
            @foreach ($services as $slug => $service)
                <div
                    id="{{ $slug }}"
                    data-layanan-row
                    class="border-t border-line transition-colors duration-200 first:border-t-0"
                    :class="active === {{ $loop->index }} ? 'bg-gold-300/25' : ''"
                >
                    <h2>
                        <button
                            type="button"
                            id="layanan-trigger-{{ $loop->index }}"
                            class="grid w-full cursor-pointer grid-cols-[3.75rem_1fr_auto] items-start gap-4 px-5 py-6 text-left sm:grid-cols-[7rem_1fr_auto] sm:gap-6 sm:px-6 sm:py-7"
                            aria-controls="layanan-panel-{{ $loop->index }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                            :aria-expanded="active === {{ $loop->index }}"
                            @click="toggle({{ $loop->index }})"
                        >
                            <span class="text-4xl font-semibold leading-none tracking-[-0.04em] tabular-nums transition-colors duration-200 sm:text-5xl lg:text-6xl" :class="active === {{ $loop->index }} ? 'text-gold-700' : 'text-ink-500'">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>

                            <span class="min-w-0 pt-2">
                                <span class="block text-2xl font-semibold leading-[1.05] tracking-[-0.02em] text-ink-950 sm:text-3xl lg:text-4xl">{{ $service['title'] }}</span>

                                <span class="mt-2.5 block text-xs leading-5 text-ink-500">{{ implode(' • ', $service['tags']) }}</span>
                            </span>

                            <svg class="mt-1 h-5 w-5 shrink-0 transition-transform duration-300 ease-out sm:mt-2 sm:h-6 sm:w-6" :class="active === {{ $loop->index }} ? 'rotate-180 text-gold-700' : 'text-ink-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                    </h2>

                    <div
                        id="layanan-panel-{{ $loop->index }}"
                        role="region"
                        aria-labelledby="layanan-trigger-{{ $loop->index }}"
                        class="grid transition-[grid-template-rows] duration-300 ease-out"
                        style="grid-template-rows: {{ $loop->first ? '1fr' : '0fr' }}"
                        :style="'grid-template-rows: ' + (active === {{ $loop->index }} ? '1fr' : '0fr')"
                        :inert="active !== {{ $loop->index }}"
                    >
                        <div class="overflow-hidden">
                            <div class="grid grid-cols-[3.75rem_1fr] gap-4 px-5 pb-8 sm:grid-cols-[7rem_1fr] sm:gap-6 sm:px-6 sm:pb-9">
                                <span aria-hidden="true"></span>

                                <div class="min-w-0">
                                    <p class="max-w-xl text-base leading-7 text-ink-600 sm:text-lg sm:leading-8">{{ $service['description'] }}</p>

                                    <a href="{{ $service['ctaUrl'] }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-12 items-center gap-2 rounded bg-gold-500 px-6 text-xs font-semibold uppercase tracking-[0.12em] text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                        {{ $service['cta'] }}
                                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M8 16L16 8M9 8h7v7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        <span class="sr-only">(tab baru)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <section class="py-6 lg:py-8" aria-labelledby="layanan-lain">
            <h2 id="layanan-lain" class="text-lg font-semibold">Layanan lain</h2>

            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($sisters as $sister)
                    <a href="{{ $sister['url'] }}" target="_blank" rel="noopener" class="inline-flex min-h-11 flex-col justify-center gap-0.5 border border-line px-4 py-2.5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand">
                        {{ $sister['title'] }}
                        <span class="text-xs font-normal leading-5 text-ink-500">{{ $sister['description'] }}</span>
                        <span class="sr-only">(tab baru)</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
