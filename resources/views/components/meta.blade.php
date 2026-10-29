@props([
    'title' => '',
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
])

@php
    // The suffix lives here so a page title that already carries the brand
    // (typed in the admin as meta title) is not doubled.
    $title = trim((string) $title);
    $title = $title === '' ? 'Avenma Properti' : (str_contains($title, 'Avenma Properti') ? $title : $title.' - Avenma Properti');

    $description = $description ?: 'Avenma Properti - katalog properti primer dan sekunder.';
    $canonical = $canonical ?: url()->current();
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="Avenma Properti">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
@if (filled($image))
    <meta property="og:image" content="{{ $image }}">
@endif
