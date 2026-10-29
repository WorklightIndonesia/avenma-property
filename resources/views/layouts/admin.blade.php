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

        @stack('head')
    </head>
    <body
        class="font-sans antialiased bg-sand text-ink-950"
        x-data="{
            sidebarOpen: window.matchMedia('(min-width: 1024px)').matches,
            toggleSidebar() {
                this.sidebarOpen = ! this.sidebarOpen;
            },
        }"
    >
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-ink-950/50 lg:hidden"></div>

        <div class="min-h-screen lg:flex">
            <aside
                x-cloak
                id="admin-sidebar"
                :class="sidebarOpen ? 'translate-x-0 lg:w-64' : '-translate-x-full lg:w-0'"
                class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col overflow-hidden bg-ink-950 text-paper/70 transition-[transform,width] duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:self-start"
            >
                <div class="flex h-16 shrink-0 items-center overflow-hidden border-b border-paper/10 px-6">
                    <x-brand variant="dark" class="!gap-2 whitespace-nowrap" />
                </div>

                <nav class="mt-4 flex-1 space-y-1 overflow-y-auto px-3 text-sm">
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
                        <a href="{{ route('admin.pages.index') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.pages.*') ? 'bg-paper/10 text-paper' : '' }}">
                            Halaman
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.users.*') ? 'bg-paper/10 text-paper' : '' }}">
                            Akun
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 {{ request()->routeIs('admin.settings.*') ? 'bg-paper/10 text-paper' : '' }}">
                            Kontak Avenma
                        </a>
                    @endif
                    <a href="{{ url('/') }}" class="flex items-center rounded px-3 py-2 hover:bg-paper/10 hover:text-paper">
                        Lihat Website
                    </a>
                </nav>

                <div class="flex shrink-0 justify-end border-t border-paper/10 p-3">
                    <button
                        @click="toggleSidebar()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded text-paper/60 hover:bg-paper/10 hover:text-paper"
                        aria-controls="admin-sidebar"
                        aria-label="Sembunyikan menu"
                    >
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                            <path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </aside>

            <div class="flex-1 min-w-0">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-paper px-4 sm:px-6 lg:px-8">
                    <button @click="toggleSidebar()" class="inline-flex h-11 w-11 items-center justify-center rounded text-ink-600 hover:bg-sand" :aria-expanded="sidebarOpen" aria-controls="admin-sidebar" :aria-label="sidebarOpen ? 'Sembunyikan menu' : 'Buka menu'">
                        <svg class="h-6 w-6" :class="sidebarOpen ? '-scale-x-100' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            {{-- top bar slides into the panel edge --}}
                            <path d="M4 7h16" stroke-width="2" stroke-linecap="round" class="origin-center transition-transform duration-[400ms] ease-out [transform-box:fill-box] motion-reduce:transition-none" :class="sidebarOpen ? 'rotate-90 -translate-x-[7px] translate-y-[5px]' : ''" />
                            {{-- middle bar shortens into the arrow shaft --}}
                            <path d="M4 12h16" stroke-width="2" stroke-linecap="round" class="origin-center transition-transform duration-[400ms] ease-out [transform-box:fill-box] motion-reduce:transition-none" :class="sidebarOpen ? 'translate-x-[1px] scale-x-75' : ''" />
                            {{-- bottom bar fades away --}}
                            <path d="M4 17h16" stroke-width="2" stroke-linecap="round" class="transition-opacity duration-[400ms] ease-out motion-reduce:transition-none" :class="sidebarOpen ? 'opacity-0' : ''" />
                            {{-- arrow head slides in from behind the shaft --}}
                            <path d="M14 7l5 5-5 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="origin-center transition-[opacity,transform] duration-[400ms] ease-out [transform-box:fill-box] motion-reduce:transition-none" :class="sidebarOpen ? 'translate-x-0 opacity-100' : '-translate-x-[3px] opacity-0'" />
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

        @stack('scripts')

        {{-- Textareas sized by rows clip their own content; grow them to fit instead. --}}
        <script>
            document.querySelectorAll('[data-autogrow]').forEach((field) => {
                const grow = () => {
                    field.style.height = 'auto';
                    field.style.height = field.scrollHeight + 'px';
                };

                field.addEventListener('input', grow);
                grow();
            });
        </script>
    </body>
</html>
