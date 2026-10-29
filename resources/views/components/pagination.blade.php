@props(['paginator'])

<div class="flex items-center gap-1">
    @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 text-ink-500">Sebelumnya</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">Sebelumnya</a>
    @endif

    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
        @if ($page === $paginator->currentPage())
            <span aria-current="page" class="border border-ink-950 px-3 py-1.5 font-semibold">{{ $page }}</span>
        @else
            <a href="{{ $url }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">{{ $page }}</a>
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-ink-600 hover:text-ink-950">Berikutnya</a>
    @else
        <span class="px-3 py-1.5 text-ink-500">Berikutnya</span>
    @endif
</div>
