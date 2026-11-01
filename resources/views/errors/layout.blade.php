<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>@yield('heading') — Avenma Properti</title>

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/brand/favicon-512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-512.png') }}">

        {{-- No database, no session, no SiteSetting: this layout must survive the
             failure it is reporting. A 500 caused by a dead connection still renders. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet"></noscript>

        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-paper text-ink-950">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:bg-ink-950 focus:px-4 focus:py-3 focus:text-paper">
            Lewati ke konten
        </a>

        <div class="mx-auto flex min-h-screen w-full max-w-[90rem] flex-col px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center">
                <x-brand />
            </div>

            <main id="main" class="flex flex-1 items-center py-16">
                <div class="max-w-prose">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-500">Error @yield('code')</p>

                    <h1 class="mt-3 text-4xl font-semibold leading-[1.15] tracking-tight">@yield('heading')</h1>

                    <p class="mt-4 text-base leading-[1.6] text-ink-600">@yield('body')</p>

                    @hasSection('actions')
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            @yield('actions')
                        </div>
                    @endif
                </div>
            </main>

            <div class="border-t border-line py-6 text-xs text-ink-500">
                &copy; {{ date('Y') }} Avenma Properti
            </div>
        </div>
    </body>
</html>
