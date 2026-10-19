<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'CMS') — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800" x-data="{ sidebarOpen: false }">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

        <div class="min-h-screen lg:flex">
            <aside
                x-cloak
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-200 transition-transform duration-200 lg:static lg:translate-x-0"
            >
                <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 font-bold text-white">A</span>
                    <span class="font-semibold text-white">Avenma CMS</span>
                </div>

                <nav class="mt-4 space-y-1 px-3 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-lg px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ url('/') }}" class="flex items-center rounded-lg px-3 py-2 text-slate-400 hover:bg-white/10 hover:text-white">
                        Lihat Website
                    </a>
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true" class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100" aria-label="Buka menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden lg:block text-sm font-medium text-slate-500">@yield('title', 'Dashboard')</div>

                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('profile.edit') }}" class="hidden sm:inline text-slate-600 hover:text-slate-900">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </header>

                <main class="p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
