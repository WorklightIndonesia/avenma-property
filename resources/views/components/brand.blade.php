@props(['variant' => 'light'])

@php
    $mark = $variant === 'dark' ? 'avenma-mark-paper-112.png' : 'avenma-logo-mark-112.png';
    $text = $variant === 'dark' ? 'text-paper' : 'text-ink-950';
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-2.5 whitespace-nowrap']) }} href="{{ url('/') }}">
    <img src="{{ asset('images/brand/'.$mark) }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
    <span class="text-lg font-semibold tracking-tight {{ $text }}">Avenma Properti</span>
</a>
