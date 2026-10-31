@props(['paginator', 'arrows' => false])

<div class="flex items-center gap-1">
    @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 text-ink-500" @if ($arrows) aria-hidden="true" @endif>
            @if ($arrows)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            @else
                Sebelumnya
            @endif
        </span>
    @else
        <a
            href="{{ $paginator->previousPageUrl() }}"
            class="px-3 py-1.5 text-ink-600 hover:text-ink-950"
            @if ($arrows) aria-label="Sebelumnya" @endif
        >
            @if ($arrows)
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            @else
                Sebelumnya
            @endif
        </a>
    @endif

    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
        @if ($page === $paginator->currentPage())
            <span aria-current="page" class="border border-ink-950 px-3 py-1.5 font-semibold">{{ $page }}</span>
        @else
            <a href="{{ $url }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">{{ $page }}</a>
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a
            href="{{ $paginator->nextPageUrl() }}"
            class="px-3 py-1.5 text-ink-600 hover:text-ink-950"
            @if ($arrows) aria-label="Berikutnya" @endif
        >
            @if ($arrows)
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            @else
                Berikutnya
            @endif
        </a>
    @else
        <span class="px-3 py-1.5 text-ink-500" @if ($arrows) aria-hidden="true" @endif>
            @if ($arrows)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            @else
                Berikutnya
            @endif
        </span>
    @endif
</div>
