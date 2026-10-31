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
        // The contact page renders its copy inside the contact layout below.
        $rest = collect($sections)
            ->except('hero')
            ->when($pageKey === 'kontak', fn ($sections) => $sections->except('kontak'))
            ->map(fn ($meta, $key) => $page->section($key))
            ->filter(fn ($section) => $section && ! $section->isEmpty());

        // A CTA saved as the /hubungi-kami redirect links straight to WhatsApp,
        // so the visitor does not pass through the site first.
        $whatsapp = $settings->whatsappUrl() ?? route('public.pages.contact');
        $ctaUrl = fn (?string $url) => $url === '/hubungi-kami' ? $whatsapp : $url;
    @endphp

    @if ($pageKey !== 'kontak')
        <section class="bg-paper">
            <div class="mx-auto max-w-[90rem] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="grid items-center gap-10 lg:grid-cols-[1.4fr_1fr] lg:gap-16">
                <div>
                    <p class="text-sm text-ink-500">{{ $isHome ? 'Properti primer dan sekunder' : $page->title }}</p>

                    <h1 class="mt-4 max-w-2xl text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
                        {{ $hero?->heading ?: $page->title }}
                    </h1>

                    @if (filled($hero?->body))
                        <div class="rich-text mt-5 max-w-xl text-lg">{!! $hero->body !!}</div>
                    @endif

                    @if ((filled($hero?->cta_label) && filled($hero->cta_url)) || (filled($hero?->cta_label_2) && filled($hero->cta_url_2)))
                        <div class="mt-8 flex flex-wrap gap-4">
                            @if (filled($hero->cta_label) && filled($hero->cta_url))
                                <a href="{{ $ctaUrl($hero->cta_url) }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                    {{ $hero->cta_label }}
                                </a>
                            @endif

                            @if (filled($hero->cta_label_2) && filled($hero->cta_url_2))
                                <a href="{{ $ctaUrl($hero->cta_url_2) }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:border-ink-950">
                                    {{ $hero->cta_label_2 }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($isHome && $heroSlides->isNotEmpty())
                    <div
                        class="min-w-0"
                        x-data="{
                            i: 0,
                            n: {{ $heroSlides->count() }},
                            timer: null,
                            // Signed distance from the active slide, shortest way round,
                            // so a dot press rolls the gallery in that direction.
                            pos(index) {
                                let d = ((index - this.i) % this.n + this.n) % this.n;
                                if (d > this.n / 2) d -= this.n;
                                return Math.max(-2, Math.min(2, d));
                            },
                            styleFor(index) {
                                const p = this.pos(index);
                                return {
                                    left: (14 + p * 72) + '%',
                                    opacity: Math.abs(p) > 1 ? '0' : (p === 0 ? '1' : '0.6'),
                                    transform: p === 0 ? 'scale(1)' : 'scale(0.86)',
                                    zIndex: p === 0 ? 10 : 1,
                                    visibility: Math.abs(p) > 1 ? 'hidden' : 'visible',
                                };
                            },
                            start() { this.stop(); if (this.n > 1 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) { this.timer = setInterval(() => this.i = (this.i + 1) % this.n, 5000); } },
                            stop() { clearInterval(this.timer); },
                            go(index) { this.i = index; }
                        }"
                        x-init="start()"
                        @mouseenter="stop()"
                        @mouseleave="start()"
                        @focusin="stop()"
                        @focusout="start()"
                    >
                        <div class="relative aspect-[100/60] overflow-hidden">
                            @foreach ($heroSlides as $index => $slide)
                                <a
                                    href="{{ $slide['url'] }}"
                                    aria-label="{{ $slide['label'] }}"
                                    class="absolute top-0 block w-[72%] px-2 transition-all duration-500 ease-out motion-reduce:transition-none"
                                    :style="styleFor({{ $index }})"
                                >
                                    <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" class="aspect-[4/3] w-full border border-line object-cover" :class="pos({{ $index }}) === 0 ? 'shadow-[0_12px_26px_-10px_rgba(20,17,14,0.40)]' : ''" @if ($index > 0) loading="lazy" @endif>
                                </a>
                            @endforeach

                            {{-- The side photos fade into the hero colour instead of
                                 being cut off at the wrapper edge. --}}
                            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 left-0 z-20 w-16 bg-gradient-to-r from-paper to-transparent"></div>
                            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 z-20 w-16 bg-gradient-to-l from-paper to-transparent"></div>
                        </div>

                        @if ($heroSlides->count() > 1)
                            <div class="-mt-4 flex items-center justify-center gap-1">
                                @foreach ($heroSlides as $index => $slide)
                                    <button
                                        type="button"
                                        @click="go({{ $index }})"
                                        class="inline-flex items-center justify-center p-1"
                                        :aria-current="i === {{ $index }} ? 'true' : 'false'"
                                        aria-label="Foto {{ $index + 1 }} dari {{ $heroSlides->count() }}"
                                    >
                                        <span class="h-1 rounded-full transition-all duration-300 motion-reduce:transition-none" :class="i === {{ $index }} ? 'w-5 bg-ink-950' : 'w-2 bg-line'"></span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif ($hero?->image)
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
    @endif

    @foreach ($rest as $section)
        <section class="border-t border-line bg-paper">
            <div class="mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                @if (filled($section->heading))
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $section->heading }}</h2>
                @endif

                @if (filled($section->body))
                    <div class="rich-text mt-4">{!! $section->body !!}</div>
                @endif

                @if (filled($section->cta_label) && filled($section->cta_url))
                    <a href="{{ $ctaUrl($section->cta_url) }}" class="mt-6 inline-flex min-h-11 items-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                        {{ $section->cta_label }}
                    </a>
                @endif
            </div>
        </section>
    @endforeach

    @if ($pageKey === 'kontak')
        @php
            $contactCopy = $page->section('kontak');
            $whatsappUrl = $settings->whatsappUrl();
            $email = filled($settings->email) ? $settings->email : null;
            $instagramUrl = $settings->instagramUrl();
            $mapsUrl = $settings->maps_url ?: (filled($settings->address) ? 'https://maps.google.com/maps?q='.rawurlencode($settings->address) : null);

            // A share link cannot be iframed; the coordinates saved with it can.
            $mapSrc = match (true) {
                $settings->maps_lat !== null && $settings->maps_lng !== null => 'https://maps.google.com/maps?q='.$settings->maps_lat.','.$settings->maps_lng.'&z=17&output=embed',
                filled($settings->address) => 'https://maps.google.com/maps?q='.rawurlencode($settings->address).'&output=embed',
                default => null,
            };
        @endphp

        <section class="bg-paper">
            <div class="mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                    <div>
                        <h1 class="text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl">{{ $contactCopy?->heading ?: 'Hubungi Kami' }}</h1>

                        @if (filled($contactCopy?->body))
                            <div class="rich-text mt-4 max-w-prose text-sm leading-7 text-ink-600">{!! $contactCopy->body !!}</div>
                        @endif

                        <dl class="mt-8 space-y-6">
                            @if ($whatsappUrl)
                                <div class="flex gap-4">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                    </svg>
                                    <div>
                                        <dt class="text-sm font-semibold text-ink-950">WhatsApp</dt>
                                        <dd class="mt-0.5 text-sm">
                                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="text-ink-600 underline decoration-line underline-offset-4 transition-colors duration-150 hover:text-ink-950">{{ $settings->whatsapp }}</a>
                                        </dd>
                                    </div>
                                </div>
                            @endif

                            @if ($email)
                                <div class="flex gap-4">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                    <div>
                                        <dt class="text-sm font-semibold text-ink-950">Email</dt>
                                        <dd class="mt-0.5 text-sm">
                                            <a href="mailto:{{ $email }}" class="text-ink-600 underline decoration-line underline-offset-4 transition-colors duration-150 hover:text-ink-950">{{ $email }}</a>
                                        </dd>
                                    </div>
                                </div>
                            @endif

                            @if (filled($settings->address))
                                <div class="flex gap-4">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <div>
                                        <dt class="text-sm font-semibold text-ink-950">Alamat</dt>
                                        <dd class="mt-0.5 max-w-xs text-sm leading-6 text-ink-600">{{ $settings->address }}</dd>
                                    </div>
                                </div>
                            @endif

                            @if ($instagramUrl)
                                <div class="flex gap-4">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <rect x="2.25" y="2.25" width="19.5" height="19.5" rx="5.25" />
                                        <circle cx="12" cy="12" r="4.5" />
                                        <path stroke-linecap="round" d="M17.25 6.75h.01" />
                                    </svg>
                                    <div>
                                        <dt class="text-sm font-semibold text-ink-950">Instagram</dt>
                                        <dd class="mt-0.5 text-sm">
                                            <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="text-ink-600 underline decoration-line underline-offset-4 transition-colors duration-150 hover:text-ink-950">{{ '@'.$settings->instagram }}</a>
                                        </dd>
                                    </div>
                                </div>
                            @endif
                        </dl>

                        @if ($mapSrc)
                            <div class="relative mt-8">
                                <iframe
                                    title="Peta lokasi kantor Avenma"
                                    src="{{ $mapSrc }}"
                                    class="h-64 w-full border border-line"
                                    loading="lazy"
                                ></iframe>

                                @if ($mapsUrl)
                                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="absolute left-2 top-2 z-10 inline-flex min-h-10 items-center gap-1.5 rounded border border-line bg-white px-3 text-xs font-semibold text-ink-950 shadow-float transition-colors duration-150 hover:bg-sand">
                                        <svg class="h-4 w-4 text-ink-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                        Buka di Google Maps
                                    </a>
                                @endif
                            </div>
                        @elseif ($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="mt-8 inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand">
                                Buka di Google Maps
                            </a>
                        @endif
                    </div>

                    @if ($whatsappUrl)
                        <div class="border border-line bg-white p-6 sm:p-8">
                            <h2 class="text-xl font-semibold tracking-tight">Kirim pesan</h2>
                            <p class="mt-2 text-sm leading-6 text-ink-600">Isi formulir, lalu kirim pesannya lewat WhatsApp. Tim kami membalas pada hari kerja.</p>

                            <form
                                class="mt-6 space-y-4"
                                x-data="{ name: '', phone: '', email: '', message: '', sent: false }"
                                x-on:submit.prevent="
                                    const text = ['Nama: ' + name, 'WhatsApp: ' + phone, email ? 'Email: ' + email : null, '', message].filter((line) => line !== null).join('\n');
                                    window.open('{{ $whatsappUrl }}?text=' + encodeURIComponent(text), '_blank', 'noopener');
                                    sent = true;
                                "
                            >
                                <div>
                                    <x-input-label for="contact-name" value="Nama" />
                                    <x-text-input id="contact-name" name="name" type="text" x-model="name" class="mt-1 block w-full" autocomplete="name" required />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="contact-phone" value="Nomor WhatsApp" />
                                        <x-text-input id="contact-phone" name="phone" type="tel" inputmode="tel" x-model="phone" class="mt-1 block w-full" autocomplete="tel" required />
                                    </div>

                                    <div>
                                        <x-input-label for="contact-email" value="Email (opsional)" />
                                        <x-text-input id="contact-email" name="email" type="email" x-model="email" class="mt-1 block w-full" autocomplete="email" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="contact-message" value="Pesan" />
                                    <textarea id="contact-message" name="message" rows="5" x-model="message" class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30" required></textarea>
                                </div>

                                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">Kirim pesan</button>

                                <p class="text-xs leading-5 text-ink-500" x-show="sent" x-cloak>Pesan dibuka di WhatsApp. Kirim di sana agar sampai ke tim kami.</p>
                            </form>

                            <noscript>
                                <p class="mt-4 text-sm text-ink-600">Formulir butuh JavaScript. <a href="{{ $whatsappUrl }}" class="underline">Hubungi kami lewat WhatsApp</a>.</p>
                            </noscript>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($isHome && $listings->isNotEmpty())
        <section class="relative bg-sand">
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-paper to-sand"></div>
            <div class="relative mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">Properti pilihan</h2>
                        <p class="mt-1 text-sm text-ink-500">Lima properti terbaru, unggulan lebih dulu.</p>
                    </div>

                    <a href="{{ route('public.listings.index') }}" class="text-sm font-semibold text-ink-950 underline hover:text-gold-700">
                        Lihat semua properti →
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

    @if ($isHome && $recentPosts->isNotEmpty())
        <section class="relative bg-paper">
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-sand to-paper"></div>
            <div class="relative mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">Artikel Terkini</h2>

                    <a href="{{ route('public.blog.index') }}" class="text-sm font-semibold text-ink-950 underline hover:text-gold-700">
                        Lihat artikel lainnya →
                    </a>
                </div>

                {{-- One row that scrolls sideways; the scrollbar stays hidden. --}}
                <div
                    class="mt-8 flex cursor-grab gap-6 overflow-x-auto pb-2 select-none active:cursor-grabbing [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    x-data="dragScroll"
                    @pointerdown="begin($event)"
                    @pointermove="move($event)"
                    @pointerup="end()"
                    @pointerleave="end()"
                    @click.capture="swallowClick($event)"
                >
                    @foreach ($recentPosts as $post)
                        @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

                        <article class="flex w-72 shrink-0 flex-col border border-line bg-white">
                            @if ($post->cover_image)
                                <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="aspect-[16/9] w-full object-cover" loading="lazy">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-xs text-ink-500">{{ $date }}@if ($post->creator) · {{ $post->creator->name }}@endif</p>

                                <h3 class="mt-2 text-lg font-semibold leading-snug">
                                    <a href="{{ route('public.blog.show', $post) }}">{{ $post->title }}</a>
                                </h3>

                                @if (filled($post->excerpt))
                                    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>
                                @endif

                                <a href="{{ route('public.blog.show', $post) }}" class="mt-auto inline-block self-start pt-4 text-sm font-semibold text-ink-950 transition duration-150 hover:scale-105 hover:underline motion-reduce:transition-none motion-reduce:hover:scale-100">Baca Artikel →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($isHome)
        <section class="relative bg-sand">
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-paper to-sand"></div>
            <div class="relative mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <h2 class="text-2xl font-semibold tracking-tight">Simulasi KPR</h2>

                <div class="mt-6">
                    <x-kpr-simulation
                        :price="$kpr['price']"
                        :interest="$kpr['rate']"
                        :min-dp="$kpr['minDp']"
                        :max-dp="$kpr['maxDp']"
                        :max-tenor="$kpr['maxTenor']"
                        heading="Hitung angsuran Anda"
                        :cta-url="$whatsapp"
                        cta-label="Konsultasi KPR via WhatsApp"
                    />
                </div>
            </div>
        </section>
    @endif
@endsection
