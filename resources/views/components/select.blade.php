@props([
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => null,
    'id' => null,
    'form' => null,
    'submit' => false,
    'ariaLabel' => null,
    'variant' => 'field',
])

@php
    $id = $id ?? 'select-'.preg_replace('/[^a-z0-9]+/i', '-', (string) $name);

    $items = collect($options)
        ->map(fn ($label, $key) => ['value' => (string) $key, 'label' => (string) $label])
        ->values();

    if ($placeholder !== null) {
        $items->prepend(['value' => '', 'label' => (string) $placeholder]);
    }

    $value = (string) $value;

    // A table header reads as a label, so the trigger drops the field chrome and
    // the panel opens straight from the header click.
    $trigger = $variant === 'header'
        ? 'flex min-h-10 w-full items-center justify-between gap-2 rounded bg-transparent p-0 text-left text-sm font-semibold text-ink-600 hover:text-ink-950'
        : 'flex min-h-11 w-full items-center justify-between gap-2 rounded border border-line bg-white px-3 py-2 text-left text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
@endphp

{{-- One dropdown for the whole site: the same panel, gold highlight, and
     keyboard handling as the location picker on the public filter. --}}
<div
    {{ $attributes->class(['relative']) }}
    x-data="selectBox({ value: @js($value), options: @js($items), submit: @js($submit), header: @js($variant === 'header') })"
    x-on:click.outside="open = false"
>
    {{-- The name is attached by Alpine so a no-JS browser submits only the
         plain <select> below, never two values for the same field. --}}
    <input type="hidden" x-ref="input" data-name="{{ $name }}" value="{{ $value }}" @if ($form) form="{{ $form }}" @endif>

    <button
        type="button"
        x-ref="button"
        @if ($id) id="{{ $id }}" @endif
        aria-haspopup="listbox"
        aria-label="{{ $ariaLabel ?? $placeholder ?? $name }}"
        :aria-expanded="open ? 'true' : 'false'"
        class="{{ $trigger }}"
        x-on:click="toggle()"
        x-on:keydown="keydown($event)"
        x-bind:class="open && ! header ? 'border-gold-700 ring-1 ring-gold-500/30' : ''"
    >
        <span class="truncate" x-text="label"></span>

        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0 text-ink-500">
            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <ul
        role="listbox"
        tabindex="-1"
        x-ref="panel"
        x-cloak
        x-show="open"
        x-on:keydown="keydown($event)"
        class="absolute z-30 mt-1 max-h-64 w-full overflow-auto border border-line bg-white shadow-float"
    >
        <template x-for="(option, index) in options" :key="option.value">
            <li role="option" :aria-selected="option.value === value ? 'true' : 'false'">
                <button
                    type="button"
                    x-on:click="pick(option)"
                    x-on:mouseenter="active = index"
                    class="flex min-h-11 w-full items-center justify-between gap-3 border-l-2 px-3 text-left text-sm text-ink-950"
                    :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'"
                >
                    <span x-text="option.label"></span>
                </button>
            </li>
        </template>
    </ul>
    <noscript>
        <select
            name="{{ $name }}"
            @if ($form) form="{{ $form }}" @endif
            class="mt-1 block min-h-11 w-full rounded border border-line bg-white px-3 py-2 text-sm text-ink-950"
        >
            @foreach ($items as $item)
                <option value="{{ $item['value'] }}" @selected($item['value'] === $value)>{{ $item['label'] }}</option>
            @endforeach
        </select>
    </noscript>
</div>
