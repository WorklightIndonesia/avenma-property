<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'CMS') — {{ config('app.name') }}</title>

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/brand/favicon-512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-512.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-sand text-ink-950" x-data="{ sidebarOpen: false }">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-ink-950/50 lg:hidden"></div>

        <div class="min-h-screen lg:flex">
            <aside
                x-cloak
                id="admin-sidebar"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 w-64 transform bg-ink-950 text-paper/70 transition-transform duration-200 lg:static lg:translate-x-0"
            >
                <div class="flex h-16 items-center border-b border-paper/10 px-6">
                    <x-brand variant="dark" class="!gap-2" />
                </div>

                <nav class="mt-4 space-y-1 px-3 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.dashboard') ? 'bg-paper/10 text-paper' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.listings.index') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.listings.*') ? 'bg-paper/10 text-paper' : '' }}">
                        Listing
                    </a>
                    <a href="{{ route('admin.developers.index') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.developers.*') ? 'bg-paper/10 text-paper' : '' }}">
                        Developer
                    </a>
                    @if (auth()->user()->role === App\Models\User::ROLE_ADMIN)
                        <a href="{{ route('admin.users.index') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.users.*') ? 'bg-paper/10 text-paper' : '' }}">
                            Akun
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.settings.*') ? 'bg-paper/10 text-paper' : '' }}">
                            Pengaturan
                        </a>
                    @endif
                    <a href="{{ url('/') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 hover:text-paper">
                        Lihat Website
                    </a>
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-paper px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true" class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded text-ink-600 hover:bg-sand" :aria-expanded="sidebarOpen" aria-controls="admin-sidebar" aria-label="Buka menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden lg:block text-sm font-medium text-ink-500">@yield('title', 'Dashboard')</div>

                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('profile.edit') }}" class="hidden sm:inline text-ink-600 hover:text-ink-950">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded border border-line px-3 py-2.5 font-medium text-ink-950 hover:bg-sand">
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
