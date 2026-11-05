@props(['variant' => 'light'])

@php
    $mark = $variant === 'dark' ? 'avenma-mark-paper-112.png' : 'avenma-logo-mark-112.png';
    $text = $variant === 'dark' ? 'text-paper' : 'text-ink-950';
@endphp

{{-- Below sm the wordmark is hidden, so the link needs its name from here. --}}
<a {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-2.5 whitespace-nowrap', 'aria-label' => 'Avenma Properti']) }} href="{{ url('/') }}">
    <img src="{{ asset('images/brand/'.$mark) }}" alt="" width="56" height="40" class="h-10 w-14" onerror="this.remove()">
    <span class="hidden text-lg font-semibold tracking-tight sm:inline {{ $text }}">Avenma Properti</span>
</a>
