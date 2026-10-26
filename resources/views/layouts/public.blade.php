<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', 'Avenma Properti — katalog properti primer dan sekunder.')">

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/brand/favicon-512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-512.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-paper text-ink-950">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:bg-ink-950 focus:px-4 focus:py-3 focus:text-paper">
            Lewati ke konten
        </a>

        <header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-line bg-paper">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <x-brand />

                    <nav class="hidden lg:flex items-center gap-8 whitespace-nowrap text-sm font-medium text-ink-600">
                        <a href="{{ url('/') }}" class="hover:text-ink-950">Beranda</a>
                        <a href="{{ url('/properti') }}" class="hover:text-ink-950">Properti</a>
                        <a href="{{ url('/blog') }}" class="hover:text-ink-950">Blog</a>
                        <a href="{{ url('/tentang-kami') }}" class="hover:text-ink-950">Tentang Kami</a>
                        <a href="{{ url('/kontak') }}" class="hover:text-ink-950">Kontak</a>
                        <a href="{{ url('/kontak') }}" class="inline-flex items-center rounded bg-gold-500 px-4 py-2.5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                            Hubungi Kami
                        </a>
                    </nav>

                    <button @click="open = !open" class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded text-ink-600 hover:bg-sand" :aria-expanded="open" aria-controls="mobile-menu" :aria-label="open ? 'Tutup menu' : 'Buka menu'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="open" x-cloak id="mobile-menu" class="lg:hidden border-t border-line bg-paper">
                <div class="space-y-1 px-4 py-4 text-sm font-medium text-ink-600">
                    <a href="{{ url('/') }}" class="block rounded px-3 py-3 hover:bg-sand">Beranda</a>
                    <a href="{{ url('/properti') }}" class="block rounded px-3 py-3 hover:bg-sand">Properti</a>
                    <a href="{{ url('/blog') }}" class="block rounded px-3 py-3 hover:bg-sand">Blog</a>
                    <a href="{{ url('/tentang-kami') }}" class="block rounded px-3 py-3 hover:bg-sand">Tentang Kami</a>
                    <a href="{{ url('/kontak') }}" class="block rounded px-3 py-3 hover:bg-sand">Kontak</a>
                    <a href="{{ url('/kontak') }}" class="mt-2 flex items-center justify-center rounded bg-gold-500 px-4 py-3 font-semibold text-ink-950 hover:bg-gold-700 hover:text-white">Hubungi Kami</a>
                </div>
            </div>
        </header>

        <main id="main">
            @yield('content')
        </main>

        <footer class="border-t border-paper/15 bg-ink-950 text-paper/70">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-10 md:grid-cols-3">
                    <div>
                        <x-brand variant="dark" />
                        <p class="mt-4 max-w-xs text-sm leading-6">
                            Katalog properti primer dan sekunder pilihan di Indonesia.
                        </p>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-paper">Navigasi</h2>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li><a href="{{ url('/properti') }}" class="hover:text-paper">Properti</a></li>
                            <li><a href="{{ url('/blog') }}" class="hover:text-paper">Blog</a></li>
                            <li><a href="{{ url('/tentang-kami') }}" class="hover:text-paper">Tentang Kami</a></li>
                            <li><a href="{{ url('/kontak') }}" class="hover:text-paper">Kontak</a></li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-paper">Kontak</h2>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li>WhatsApp: segera tersedia</li>
                            <li>Email: segera tersedia</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t border-paper/10 pt-6 text-xs text-paper/50">
                    &copy; {{ date('Y') }} Avenma Properti. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </footer>
    </body>
</html>
