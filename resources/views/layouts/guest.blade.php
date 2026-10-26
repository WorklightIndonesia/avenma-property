<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/brand/favicon-512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/brand/favicon-512.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink-950 antialiased">
        <div class="flex min-h-screen flex-col items-center bg-sand pt-6 sm:justify-center sm:pt-0">
            <x-brand />

            <div class="mt-8 w-full overflow-hidden border border-line bg-paper px-6 py-8 sm:max-w-md sm:rounded">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
