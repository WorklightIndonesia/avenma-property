@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-gold-700 bg-sand py-2 pe-4 ps-3 text-start text-base font-medium text-ink-950 transition-colors duration-150'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-ink-600 hover:bg-sand hover:text-ink-950 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
