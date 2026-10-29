@props(['href'])

<a href="{{ $href }}" class="mb-1 inline-flex min-h-10 items-center gap-1.5 rounded text-sm font-medium text-ink-600 transition-colors duration-150 hover:text-ink-950">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M19 12H5m0 0 6-6m-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    {{ $slot }}
</a>
