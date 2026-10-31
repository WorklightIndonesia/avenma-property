@php
    // Same error-lookup rule as <x-text-input>: images[0][cover] becomes the dot
    // key the error bag stores.
    $field = str_replace(['[', ']'], ['.', ''], (string) $attributes->get('name'));
    $invalid = $field !== '' && $errors->has(rtrim($field, '.'));
@endphp

{{-- The button text comes from the browser locale, so this styles the control
     rather than replacing it with a scripted picker. --}}
<input
    type="file"
    @if ($invalid) aria-invalid="true" @endif
    {{ $attributes->merge(['class' => 'mt-1 block w-full text-sm text-ink-600 file:mr-3 file:cursor-pointer file:rounded file:border-0 file:bg-sand file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-ink-950 hover:file:bg-line']) }}
>
