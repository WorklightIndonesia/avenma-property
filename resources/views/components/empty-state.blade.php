@props(['title', 'description' => null])

{{-- One sentence plus one action, per the empty-state rule in DESIGN.md §6. --}}
<div {{ $attributes->merge(['class' => 'px-4 py-12 text-center']) }}>
    <p class="text-sm font-semibold text-ink-950">{{ $title }}</p>

    @if ($description)
        <p class="mt-2 text-sm text-ink-600">{{ $description }}</p>
    @endif

    @if (isset($action) && trim((string) $action) !== '')
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">{{ $action }}</div>
    @endif
</div>
