@props(['disabled' => false])

@php
    // Bracket notation ("images[0][label]") becomes the dot key the error bag
    // uses ("images.0.label"), so the field announces itself as invalid.
    $field = str_replace(['[', ']'], ['.', ''], (string) $attributes->get('name'));
    $invalid = $field !== '' && $errors->has(rtrim($field, '.'));
@endphp

<input @disabled($disabled) @if ($invalid) aria-invalid="true" @endif {{ $attributes->merge(['class' => 'rounded border-line bg-white text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30']) }}>
