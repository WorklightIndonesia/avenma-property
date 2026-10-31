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
                <x-icon-chevron direction="left" double />
            </span>
        @else
            <a href="{{ $paginator->url(max(1, $current - 5)) }}" class="{{ $link }}" aria-label="Mundur 5 halaman">
                <x-icon-chevron direction="left" double />
            </a>
        @endif

        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-hidden="true">
                <x-icon-chevron direction="left" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="{{ $link }}" aria-label="Sebelumnya">
                <x-icon-chevron direction="left" />
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
                <x-icon-chevron direction="right" />
            </a>
        @else
            <span class="{{ $off }}" aria-hidden="true">
                <x-icon-chevron direction="right" />
            </span>
        @endif

        @if ($current >= $last)
            <span class="{{ $off }}" aria-hidden="true">
                <x-icon-chevron direction="right" double />
            </span>
        @else
            <a href="{{ $paginator->url(min($last, $current + 5)) }}" class="{{ $link }}" aria-label="Maju 5 halaman">
                <x-icon-chevron direction="right" double />
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
