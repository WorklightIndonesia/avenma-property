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
    {{-- The chip controls are links, so without JavaScript every section stays
         visible instead of leaving the page blank under the chips. --}}
    <noscript><style>.layanan-section[x-cloak]{display:block !important}</style></noscript>

    <div
        class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8"
        x-data="{
            active: @js($sections[0]['key'] ?? ''),
            keys: @js(array_column($sections, 'key')),
            choose(key) {
                this.active = key;
                history.replaceState(null, '', '#' + key);
            },
            sync() {
                const id = decodeURIComponent(window.location.hash.slice(1));
                if (! id) return;

                // A section anchor switches the chip; a row anchor also opens
                // the category that owns the row before scrolling to it.
                if (this.keys.includes(id)) {
                    this.active = id;
                    this.reveal(id);
                    return;
                }

                const owner = document.getElementById(id)?.closest('[data-category]');
                if (owner) {
                    this.active = owner.dataset.category;
                    this.reveal(id);
                }
            },
            reveal(id) {
                // The section is still display:none when sync() runs, so wait
                // for Alpine to apply x-show before asking the browser to scroll.
                this.$nextTick(() => document.getElementById(id)?.scrollIntoView({ block: 'start' }));
            },
            init() { this.sync(); },
        }"
        @hashchange.window="sync()"
    >
        <section class="pt-10 lg:pt-12">
            <div class="min-w-0">
                <h1 class="text-5xl font-semibold leading-[1.05] tracking-[-0.03em] sm:text-6xl xl:text-7xl">
                    {{ $hero?->heading ?: 'Dari Properti Impian hingga Hunian Idaman, Semua dalam Satu Layanan' }}
                </h1>

                @if (filled($hero?->body))
                    <div class="rich-text layanan-hero mt-5 max-w-prose text-lg leading-8 text-ink-600">{!! $hero->body !!}</div>
                @endif

                <a href="{{ $heroCta }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-14 items-center rounded bg-gold-500 px-6 text-base font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                    {{ $hero?->cta_label ?: 'Diskusikan via WhatsApp' }}
                    <span class="sr-only">(tab baru)</span>
                </a>
            </div>
        </section>

        <h2 class="mt-10 text-4xl font-semibold leading-[1.05] tracking-[-0.03em] text-ink-950 sm:text-5xl lg:mt-14 xl:text-6xl">Layanan Kami</h2>

        <nav class="mt-4 flex flex-wrap gap-2 lg:mt-6" aria-label="Kategori layanan">
            @foreach ($sections as $section)
                <a
                    href="#{{ $section['key'] }}"
                    @click.prevent="choose('{{ $section['key'] }}')"
                    :aria-current="active === '{{ $section['key'] }}' ? 'page' : 'false'"
                    :class="active === '{{ $section['key'] }}' ? 'border-gold-500 bg-gold-500 text-ink-950' : 'border-line bg-white text-ink-600 hover:bg-sand hover:text-ink-950'"
                    class="inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-medium transition-colors duration-150"
                >{{ Illuminate\Support\Str::after($section['label'], 'Layanan ') }}</a>
            @endforeach
        </nav>

        {{-- Each section owns its accordion, so opening a row in one group
             leaves the others alone. The panel is a one-row grid whose track
             goes 0fr -> 1fr; that animates the real height without measuring
             it. `inert` keeps a closed row out of the tab order. Row ids are
             the service slugs, so /layanan#property-management is a shareable
             anchor; the section id is the category key, which is what the
             chips and the navbar link to. --}}
        @foreach ($sections as $section)
            <section
                id="{{ $section['key'] }}"
                data-category="{{ $section['key'] }}"
                x-show="active === '{{ $section['key'] }}'"
                x-cloak
                aria-labelledby="layanan-{{ $section['key'] }}"
                class="layanan-section mt-6 lg:mt-8"
            >
                <h3 id="layanan-{{ $section['key'] }}" class="text-2xl font-semibold leading-[1.05] tracking-[-0.02em] text-ink-950 sm:text-3xl">{{ $section['label'] }}</h3>

                <div
                    class="mt-4 border border-line bg-white"
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
                    @foreach ($section['rows'] as $slug => $service)
                        <div
                            id="{{ $slug }}"
                            data-layanan-row
                            class="border-t border-line transition-colors duration-200 first:border-t-0"
                            :class="active === {{ $loop->index }} ? 'bg-gold-300/25' : ''"
                        >
                            <h4>
                                <button
                                    type="button"
                                    id="layanan-trigger-{{ $section['key'] }}-{{ $loop->index }}"
                                    class="grid w-full cursor-pointer grid-cols-[3.75rem_1fr_auto] items-start gap-4 px-5 py-6 text-left sm:grid-cols-[7rem_1fr_auto] sm:gap-6 sm:px-6 sm:py-7"
                                    aria-controls="layanan-panel-{{ $section['key'] }}-{{ $loop->index }}"
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
                            </h4>

                            <div
                                id="layanan-panel-{{ $section['key'] }}-{{ $loop->index }}"
                                role="region"
                                aria-labelledby="layanan-trigger-{{ $section['key'] }}-{{ $loop->index }}"
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
                </div>
            </section>
        @endforeach
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
