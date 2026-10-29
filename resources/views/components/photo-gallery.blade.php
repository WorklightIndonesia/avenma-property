@props(['photos'])

@php
    $total = count($photos);
@endphp

@if ($total > 0)
    <div
        x-data="photoGallery(@js($photos))"
        x-on:keydown.escape.window="isOpen && close()"
        x-on:keydown.arrow-right.window.prevent="isOpen && next()"
        x-on:keydown.arrow-left.window.prevent="isOpen && prev()"
        x-on:keydown.tab="isOpen && trap($event)"
    >
        <div class="relative">
            <img
                src="{{ $photos[0]['src'] }}"
                :src="photos[active].src"
                alt="{{ $photos[0]['alt'] }}"
                :alt="photos[active].alt"
                class="aspect-[4/3] w-full cursor-zoom-in object-cover"
                x-on:click="open(active)"
            >

            {{ $slot }}

            <span x-show="photos[active].label" x-text="photos[active].label" class="absolute bottom-3 left-3 rounded bg-ink-950/90 px-2.5 py-1 text-xs font-semibold text-paper">{{ $photos[0]['label'] }}</span>

            @if ($total > 1)
                <button type="button" x-on:click="prev()" aria-label="Foto sebelumnya" class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-line bg-paper/90 text-ink-950 transition-colors duration-150 hover:bg-paper focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <button type="button" x-on:click="next()" aria-label="Foto berikutnya" class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-line bg-paper/90 text-ink-950 transition-colors duration-150 hover:bg-paper focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <p class="absolute bottom-3 right-3 rounded bg-ink-950/70 px-2 py-0.5 text-xs font-semibold text-paper" aria-live="polite">
                    <span x-text="active + 1">{{ 1 }}</span> / {{ $total }}
                </p>
            @endif
        </div>

        @if ($total > 1)
            <div class="mt-2 flex snap-x snap-mandatory gap-2 overflow-x-auto scroll-smooth pb-1 [scrollbar-width:thin]">
                @foreach ($photos as $index => $photo)
                    <button
                        type="button"
                        x-on:click="active = {{ $index }}"
                        :class="active === {{ $index }} ? 'border-ink-950' : 'border-line'"
                        class="relative w-[calc(25%-6px)] flex-none snap-start overflow-hidden border p-0.5 hover:border-ink-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700"
                        aria-label="Tampilkan foto {{ $index + 1 }}{{ $photo['label'] ? ': '.$photo['label'] : '' }}"
                    >
                        <img src="{{ $photo['src'] }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        @if ($photo['label'])
                            <span class="absolute bottom-1.5 left-1.5 hidden max-w-[calc(100%-0.75rem)] truncate rounded bg-ink-950/90 px-2 py-0.5 text-xs font-semibold text-paper sm:block">{{ $photo['label'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        <div class="mt-2 flex justify-end">
            <button type="button" x-ref="trigger" x-on:click="open(active)" class="inline-flex min-h-11 items-center gap-2 rounded px-3 text-sm font-semibold text-ink-600 transition-colors duration-150 hover:text-ink-950">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4"><path d="M8 3H5a2 2 0 0 0-2 2v3m0 8v3a2 2 0 0 0 2 2h3m8 0h3a2 2 0 0 0 2-2v-3m0-8V5a2 2 0 0 0-2-2h-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Lihat semua foto
            </button>
        </div>

        {{-- Fullscreen viewer. The page keeps its own gallery; only this layer reads the keyboard. --}}
        <div
            x-ref="overlay"
            x-cloak
            x-show="isOpen"
            role="dialog"
            aria-modal="true"
            aria-label="Foto"
            :aria-label="'Foto ' + (active + 1) + ' dari ' + photos.length"
            x-on:click.self="close()"
            x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[70] flex flex-col bg-ink-950"
        >
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <p class="text-sm font-semibold text-paper/70">
                    <span x-text="active + 1">{{ 1 }}</span> / {{ $total }}
                </p>

                <button type="button" x-ref="close" x-on:click="close()" aria-label="Tutup foto" class="inline-flex h-11 w-11 items-center justify-center rounded border border-paper/25 text-paper transition-colors duration-150 hover:bg-paper/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-300">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 pb-4 sm:px-16">
                <img :src="photos[active].src" :alt="photos[active].alt" alt="{{ $photos[0]['alt'] }}" class="max-h-full max-w-full object-contain">

                @if ($total > 1)
                    <button type="button" x-on:click="prev()" aria-label="Foto sebelumnya" class="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-paper/25 text-paper transition-colors duration-150 hover:bg-paper/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-300 sm:left-4">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>

                    <button type="button" x-on:click="next()" aria-label="Foto berikutnya" class="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-paper/25 text-paper transition-colors duration-150 hover:bg-paper/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-300 sm:right-4">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                @endif
            </div>

            <p x-show="photos[active].label" x-text="photos[active].label" class="px-4 pb-5 text-center text-sm font-semibold text-paper/80">{{ $photos[0]['label'] }}</p>
        </div>
    </div>
@endif
