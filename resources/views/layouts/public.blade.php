@php
    $navCategories = App\Models\Service::CATEGORIES;
    $navSisters = App\Support\SisterService::links();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth [scroll-padding-top:5rem]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @hasSection('meta')
            @yield('meta')
        @else
            <x-meta />
        @endif

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/brand/favicon-512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-512.png') }}">

        {{-- Loaded off the critical path: a blocking third-party stylesheet cost
             about 0.8s of mobile LCP. media="print" fetches without blocking,
             then flips to all; the noscript copy covers JavaScript being off. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        {{-- The stylesheet loads async, so without this the 400 and 600 faces
             land around 3s and swap under the text already on screen. Preloading
             them starts the download at parse time and removes that reflow. --}}
        <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.bunny.net/plus-jakarta-sans/files/plus-jakarta-sans-latin-400-normal.woff2">
        <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.bunny.net/plus-jakarta-sans/files/plus-jakarta-sans-latin-600-normal.woff2">
        <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet">
        <noscript><link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet"></noscript>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="font-sans antialiased bg-paper text-ink-950">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:bg-ink-950 focus:px-4 focus:py-3 focus:text-paper">
            Lewati ke konten
        </a>

        <header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-line bg-paper">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-3">
                    <x-brand />

                    <div class="flex items-center gap-3 sm:gap-4 lg:gap-8">
                        <nav class="hidden lg:flex items-center gap-8 whitespace-nowrap text-sm font-medium text-ink-600">
                            <a href="{{ url('/') }}" class="hover:text-ink-950">Beranda</a>
                            <a href="{{ url('/properti') }}" class="hover:text-ink-950">Properti</a>

                            {{-- Two of the three service lines are other sites, so they
                                 sit behind one label instead of widening the bar. --}}
                            <div
                                class="relative flex h-16 items-center"
                                x-data="{ open: false }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                            >
                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open"
                                    aria-haspopup="true"
                                    aria-controls="nav-layanan"
                                    @if (request()->is('layanan')) aria-current="page" @endif
                                    class="inline-flex items-center gap-1.5 transition-colors duration-150 hover:text-ink-950 aria-[current=page]:text-ink-950"
                                    :class="open ? 'text-ink-950' : ''"
                                >
                                    Layanan
                                    <svg class="h-3 w-3 transition-transform duration-150" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </button>

                                <div id="nav-layanan" x-show="open" x-cloak class="absolute left-0 top-full z-50 mt-3 w-72 border border-line bg-paper shadow-float">
                                    @foreach ($navCategories as $category => $label)
                                        <a href="{{ url('/layanan') }}#{{ $category }}" class="flex min-h-11 items-center border-t border-line px-4 py-2.5 transition-colors duration-150 first:border-t-0 hover:bg-sand hover:text-ink-950">
                                            {{ $label }}
                                        </a>
                                    @endforeach

                                    @foreach ($navSisters as $sister)
                                        <a href="{{ $sister['url'] }}" target="_blank" rel="noopener" class="block border-t border-line px-4 py-3 transition-colors duration-150 hover:bg-sand hover:text-ink-950">
                                            {{ $sister['title'] }}
                                            <span class="mt-0.5 block text-xs font-normal leading-5 text-ink-500">{{ $sister['description'] }}</span>
                                            <span class="sr-only">(tab baru)</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <a href="{{ url('/blog') }}" class="hover:text-ink-950">Blog</a>
                            <a href="{{ url('/kontak') }}" class="hover:text-ink-950">Kontak</a>
                        </nav>

                        {{-- Stays in the bar at every width: below lg it sits left of
                             the menu button instead of inside the menu. --}}
                        <a href="{{ route('public.hubungi-kami') }}" target="_blank" rel="noopener" class="inline-flex min-h-10 shrink-0 items-center whitespace-nowrap rounded bg-gold-500 px-3 py-2 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white sm:px-4 sm:py-2.5">
                            Hubungi Kami
                        </a>

                        <button @click="open = !open" class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded text-ink-600 hover:bg-sand" :aria-expanded="open" aria-controls="mobile-menu" aria-label="Buka menu" :aria-label="open ? 'Tutup menu' : 'Buka menu'">
                            <span class="relative block h-5 w-6" aria-hidden="true">
                                <span class="absolute left-0 top-0 h-0.5 w-6 rounded bg-current transition-transform duration-300" :class="open ? 'translate-y-[9px] rotate-45' : ''"></span>
                                <span class="absolute left-0 top-[9px] h-0.5 w-6 rounded bg-current transition-opacity duration-300" :class="open ? 'opacity-0' : ''"></span>
                                <span class="absolute left-0 top-[18px] h-0.5 w-6 rounded bg-current transition-transform duration-300" :class="open ? '-translate-y-[9px] -rotate-45' : ''"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div
                x-show="open"
                x-cloak
                id="mobile-menu"
                class="lg:hidden border-t border-line bg-paper"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
            >
                <div class="space-y-1 px-4 py-4 text-sm font-medium text-ink-600">
                    <a href="{{ url('/') }}" class="block rounded px-3 py-3 hover:bg-sand">Beranda</a>
                    <a href="{{ url('/properti') }}" class="block rounded px-3 py-3 hover:bg-sand">Properti</a>
                    @foreach ($navCategories as $category => $label)
                        <a href="{{ url('/layanan') }}#{{ $category }}" class="block rounded px-3 py-3 hover:bg-sand">{{ $label }}</a>
                    @endforeach
                    @foreach ($navSisters as $sister)
                        <a href="{{ $sister['url'] }}" target="_blank" rel="noopener" class="block rounded px-3 py-3 hover:bg-sand">
                            {{ $sister['title'] }}
                            <span class="mt-0.5 block text-xs font-normal leading-5 text-ink-500">{{ $sister['description'] }}</span>
                            <span class="sr-only">(tab baru)</span>
                        </a>
                    @endforeach
                    <a href="{{ url('/blog') }}" class="block rounded px-3 py-3 hover:bg-sand">Blog</a>
                    <a href="{{ url('/kontak') }}" class="block rounded px-3 py-3 hover:bg-sand">Kontak</a>
                </div>
            </div>
        </header>

        <main id="main">
            @yield('content')
        </main>

        <footer class="border-t border-paper/15 bg-ink-950 text-paper/70">
            <div class="mx-auto max-w-[90rem] px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-10 md:flex-row md:items-start md:justify-between md:gap-16">
                    @php($contact = App\Models\SiteSetting::current())

                    <div>
                        <x-brand variant="dark" />
                        <p class="mt-4 max-w-xs text-sm leading-6">
                            Jual beli, sewa, dan pengelolaan properti di Jabodetabek.
                        </p>

                        <div class="mt-6 flex items-center gap-3">
                            <a href="{{ $contact->whatsappUrl() ?? url('/kontak') }}" target="_blank" rel="noopener" aria-label="WhatsApp Avenma" class="inline-flex h-11 w-11 items-center justify-center rounded text-paper/70 transition-colors duration-150 hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </a>

                            @if ($contact->instagramUrl())
                                <a href="{{ $contact->instagramUrl() }}" target="_blank" rel="noopener" aria-label="Instagram Avenma" class="inline-flex h-11 w-11 items-center justify-center rounded text-paper/70 transition-colors duration-150 hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"/>
                                </svg>
                            </a>

                            @endif

                            @if ($contact->threadsUrl())
                                <a href="{{ $contact->threadsUrl() }}" target="_blank" rel="noopener" aria-label="Threads Avenma" class="inline-flex h-11 w-11 items-center justify-center rounded text-paper/70 transition-colors duration-150 hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M18.263 11.097c-.03-3.486-1.92-5.586-5.111-5.586-2.13 0-3.922.963-4.863 2.499l2.062 1.438c.535-.843 1.272-1.543 2.628-1.543 1.528 0 2.318.85 2.544 2.431a15 15 0 0 0-2.236-.173c-4.125 0-6.068 1.867-6.068 4.336s1.943 3.99 4.804 3.99c3.139 0 5.013-2.115 5.781-4.735.798.361 1.348 1.204 1.348 2.47 0 3.387-3.907 5.232-7.22 5.232-4.885 0-8.077-3.207-8.077-8.424 0-6.392 4.223-10.487 9.9-10.487 3.808 0 5.69 1.671 6.97 3.914l2.108-1.475C21.44 2.078 18.331 0 13.663 0 6.227 0 1.168 5.277 1.168 12.934c0 7 4.953 11.066 10.856 11.066 4.878 0 9.809-2.846 9.809-7.716 0-2.545-1.46-4.231-3.569-5.187m-6.33 4.855c-1.077 0-2.026-.512-2.026-1.453 0-1.483 1.822-1.934 3.606-1.934.678 0 1.34.045 1.927.173-.422 1.927-1.671 3.215-3.508 3.214Z"/>
                                </svg>
                            </a>

                            @endif

                            @if (filled($contact->email))
                                <a href="mailto:{{ $contact->email }}" aria-label="Email Avenma" class="inline-flex h-11 w-11 items-center justify-center rounded text-paper/70 transition-colors duration-150 hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.174 3.29a4.5 4.5 0 01-3.652 0L1.5 8.67z"/>
                                    <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 3.886a3 3 0 002.572 0L22.5 6.908z"/>
                                </svg>
                            </a>

                            @endif

                            @if (filled($contact->address) || filled($contact->maps_url))
                                <a href="{{ $contact->maps_url ?: 'https://maps.google.com/maps?q='.rawurlencode($contact->address) }}" target="_blank" rel="noopener" aria-label="Lokasi kantor Avenma" class="inline-flex h-11 w-11 items-center justify-center rounded text-paper/70 transition-colors duration-150 hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-10 md:flex-row md:gap-16">
                        <div class="md:text-right">
                            <h2 class="text-sm font-semibold text-paper">Layanan</h2>
                            <ul class="mt-4 space-y-2 text-sm">
                                @foreach ($navCategories as $category => $label)
                                    <li><a href="{{ url('/layanan') }}#{{ $category }}" class="hover:text-paper">{{ $label }}</a></li>
                                @endforeach
                                @foreach ($navSisters as $sister)
                                    <li>
                                        <a href="{{ $sister['url'] }}" target="_blank" rel="noopener" class="hover:text-paper">{{ $sister['title'] }}</a>
                                        <span class="sr-only">(tab baru)</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="md:text-right">
                            <h2 class="text-sm font-semibold text-paper">Navigasi</h2>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li><a href="{{ url('/properti') }}" class="hover:text-paper">Properti</a></li>
                                <li><a href="{{ url('/blog') }}" class="hover:text-paper">Blog</a></li>
                                <li><a href="{{ url('/kontak') }}" class="hover:text-paper">Kontak</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-12 border-t border-paper/10 pt-6 text-xs text-paper/50">
                    &copy; {{ date('Y') }} Avenma Properti. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </footer>

        <x-toast />
    </body>
</html>
