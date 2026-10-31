@props(['direction' => 'left', 'double' => false])

@php
    $path = [
        'left' => $double ? 'M13 5l-7 7 7 7M20 5l-7 7 7 7' : 'M15 5l-7 7 7 7',
        'right' => $double ? 'M4 5l7 7-7 7M11 5l7 7-7 7' : 'M9 5l7 7-7 7',
    ][$direction];
@endphp

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.5', 'class' => 'h-5 w-5', 'aria-hidden' => 'true']) }}><path d="{{ $path }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
