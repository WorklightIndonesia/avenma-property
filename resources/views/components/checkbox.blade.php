@props(['id' => null, 'name' => null, 'value' => '1', 'checked' => false])

{{-- One checkbox for the whole app. The input sits inside its own label, so it is
     named by the visible text without an extra aria attribute. --}}
<label
    @if ($id) for="{{ $id }}" @endif
    {{ $attributes->merge(['class' => 'flex min-h-11 items-center gap-3 text-sm font-medium text-ink-950']) }}
>
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        type="checkbox"
        value="{{ $value }}"
        @checked($checked)
        class="shrink-0 rounded border-line text-gold-700 focus:ring-gold-500/30"
    >
    {{ $slot }}
</label>
