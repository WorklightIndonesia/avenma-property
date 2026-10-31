@props(['disabled' => false])

@php
    // Same error-lookup rule as <x-text-input>, so a rejected long field is
    // announced as invalid instead of only showing a message underneath.
    $field = str_replace(['[', ']'], ['.', ''], (string) $attributes->get('name'));
    $invalid = $field !== '' && $errors->has(rtrim($field, '.'));
@endphp

<textarea @disabled($disabled) @if ($invalid) aria-invalid="true" @endif {{ $attributes->merge(['class' => 'mt-1 block w-full rounded border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30']) }}>{{ $slot }}</textarea>
