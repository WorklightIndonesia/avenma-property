<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', 'Avenma Property — katalog properti primer dan sekunder.')">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-slate-800">
        <header x-data="{ open: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-white font-bold">A</span>
                        <span class="font-semibold text-lg tracking-tight text-slate-900">Avenma Property</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                        <a href="{{ url('/') }}" class="hover:text-slate-900">Beranda</a>
                        <a href="{{ url('/properti') }}" class="hover:text-slate-900">Properti</a>
                        <a href="{{ url('/blog') }}" class="hover:text-slate-900">Blog</a>
                        <a href="{{ url('/tentang-kami') }}" class="hover:text-slate-900">Tentang Kami</a>
                        <a href="{{ url('/kontak') }}" class="hover:text-slate-900">Kontak</a>
                        <a href="{{ url('/kontak') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Hubungi Kami</a>
                    </nav>

                    <button @click="open = ! open" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100" aria-label="Buka menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="open" x-cloak class="md:hidden border-t border-slate-200 bg-white">
                <div class="px-4 py-4 space-y-2 text-sm font-medium text-slate-600">
                    <a href="{{ url('/') }}" class="block rounded-md px-3 py-2 hover:bg-slate-50">Beranda</a>
                    <a href="{{ url('/properti') }}" class="block rounded-md px-3 py-2 hover:bg-slate-50">Properti</a>
                    <a href="{{ url('/blog') }}" class="block rounded-md px-3 py-2 hover:bg-slate-50">Blog</a>
                    <a href="{{ url('/tentang-kami') }}" class="block rounded-md px-3 py-2 hover:bg-slate-50">Tentang Kami</a>
                    <a href="{{ url('/kontak') }}" class="block rounded-md px-3 py-2 hover:bg-slate-50">Kontak</a>
                </div>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="bg-slate-900 text-slate-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-2 text-white">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 font-bold">A</span>
                            <span class="font-semibold text-lg">Avenma Property</span>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-400">
                            Katalog properti primer dan sekunder pilihan di Indonesia.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Navigasi</h3>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li><a href="{{ url('/properti') }}" class="hover:text-white">Properti</a></li>
                            <li><a href="{{ url('/blog') }}" class="hover:text-white">Blog</a></li>
                            <li><a href="{{ url('/tentang-kami') }}" class="hover:text-white">Tentang Kami</a></li>
                            <li><a href="{{ url('/kontak') }}" class="hover:text-white">Kontak</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Kontak</h3>
                        <ul class="mt-4 space-y-2 text-sm text-slate-400">
                            <li>WhatsApp: segera tersedia</li>
                            <li>Email: segera tersedia</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 border-t border-white/10 pt-6 text-sm text-slate-400">
                    &copy; {{ date('Y') }} Avenma Property. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </footer>
    </body>
</html>
