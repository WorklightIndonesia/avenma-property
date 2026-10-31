@props(['paginator', 'arrows' => false])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();

    // The gallery pages through properties five at a time and slides one page
    // per arrow. The public lists keep the window they had: two either side.
    $start = $arrows ? min($current, max(1, $last - 4)) : max(1, $current - 2);
    $end = $arrows ? min($last, $start + 4) : min($last, $current + 2);

    $link = 'px-3 py-1.5 text-ink-600 hover:text-ink-950';
    $off = 'px-3 py-1.5 text-ink-500';
@endphp

<div class="flex items-center gap-1">
    @if ($arrows)
        @if ($current <= 1)
            <span class="{{ $off }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M13 5l-7 7 7 7M20 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        @else
            <a href="{{ $paginator->url(max(1, $current - 5)) }}" class="{{ $link }}" aria-label="Mundur 5 halaman">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M13 5l-7 7 7 7M20 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        @endif

        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="{{ $link }}" aria-label="Sebelumnya">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        @endif
    @else
        @if ($paginator->onFirstPage())
            <span class="{{ $off }}">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="{{ $link }}">Sebelumnya</a>
        @endif
    @endif

    @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
        @if ($page === $current)
            <span aria-current="page" class="border border-ink-950 px-3 py-1.5 font-semibold">{{ $page }}</span>
        @else
            <a href="{{ $url }}" class="{{ $link }}">{{ $page }}</a>
        @endif
    @endforeach

    @if ($arrows)
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="{{ $link }}" aria-label="Berikutnya">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        @else
            <span class="{{ $off }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        @endif

        @if ($current >= $last)
            <span class="{{ $off }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M4 5l7 7-7 7M11 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        @else
            <a href="{{ $paginator->url(min($last, $current + 5)) }}" class="{{ $link }}" aria-label="Maju 5 halaman">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M4 5l7 7-7 7M11 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        @endif
    @else
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="{{ $link }}">Berikutnya</a>
        @else
            <span class="{{ $off }}">Berikutnya</span>
        @endif
    @endif
</div>
