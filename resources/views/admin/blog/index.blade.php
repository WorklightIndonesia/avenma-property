@extends('layouts.admin')

@section('title', 'Blog')

@section('content')
    @php
        $title = $filters['judul'] ?? '';
        $sortAsc = $sort === 'terlama';
        $sortDesc = $sort === 'terbaru';
        $filtered = $title !== '' || $sortAsc || $sortDesc;

        $field = 'block w-full rounded border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30';
        $header = 'flex w-full min-h-10 items-center justify-between gap-2 rounded text-left text-sm font-semibold text-ink-600 hover:text-ink-950';
        $refocus = request('focus') === 'judul'
            ? '$nextTick(() => { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length) })'
            : '';
        // A column only opens when its header was the last thing being edited.
        $open = request('focus') === 'judul' ? 'judul' : null;
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Blog</h1>
            <p class="mt-1 text-sm text-ink-500">
                {{ auth()->user()->role === App\Models\User::ROLE_ADMIN ? 'Semua artikel di website.' : 'Artikel yang Anda tulis.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if ($filtered)
                <a href="{{ route('admin.blog.index') }}" class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium text-ink-600 transition-colors duration-150 hover:bg-sand hover:text-ink-950">Reset filter</a>
            @endif

            <a href="{{ route('admin.blog.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Tulis artikel
            </a>
        </div>
    </div>

    <form id="blog-filters" method="GET" action="{{ route('admin.blog.index') }}" x-data x-on:submit="$refs.focus.value = document.activeElement?.name ?? ''">
        <input type="hidden" name="focus" x-ref="focus">
        <button type="submit" class="sr-only">Terapkan</button>
    </form>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full table-fixed min-w-[720px] text-sm" x-data="{ open: @js($open) }">
            <colgroup>
                <col class="w-[44%]">
                <col class="w-[20%]">
                <col class="w-[20%]">
                <col class="w-[16%]">
            </colgroup>
            <thead class="bg-sand text-sm font-semibold text-ink-600" x-on:click.outside="open = null">
                <tr>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'judul'" x-on:click="open = 'judul'; $nextTick(() => $refs.judul.focus())" class="{{ $header }}">
                            <span class="truncate">{{ $title !== '' ? $title : 'Judul' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'judul'" x-ref="judul" aria-label="Cari judul artikel" form="blog-filters" id="filter-judul" type="search" name="judul" value="{{ $title }}" placeholder="Cari judul" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:blur="open = null" x-on:keydown.escape="open = null" x-init="{{ $refocus }}">
                    </th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center">Penulis</span></th>
                    <th class="px-4 py-3 text-left align-top">
                        <a href="{{ route('admin.blog.index', array_filter(['judul' => $title, 'urut' => $sortAsc ? 'terbaru' : 'terlama'])) }}" class="flex min-h-10 items-center gap-2 rounded text-sm font-semibold text-ink-600 hover:text-ink-950">
                            <span>Tanggal</span>
                            @if ($sortAsc)
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M12 19V5m0 0-6 6m6-6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @elseif ($sortDesc)
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M12 5v14m0 0 6-6m-6 6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M8 7l4-4 4 4M8 17l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="font-medium text-ink-950 hover:underline">{{ $post->title }}</a>

                            @unless ($post->is_published)
                                <span class="ml-2 rounded bg-sand px-1.5 py-0.5 text-xs font-medium text-ink-500">Draf</span>
                            @endunless
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $post->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j M Y') }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            @if ($post->is_published)
                                <a href="{{ route('public.blog.show', $post) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Lihat</a>
                            @endif

                            <button type="button" x-on:click="$dispatch('open-modal', 'delete-post-{{ $post->id }}')" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-ink-500">
                            @if ($title !== '')
                                Tidak ada artikel yang cocok.
                                <a href="{{ route('admin.blog.index') }}" class="font-medium text-ink-950 underline hover:text-gold-700">Reset filter</a>
                            @else
                                Belum ada artikel.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>

    {{-- One dialog per row, placed outside the table so nothing clips it. --}}
    @foreach ($posts as $post)
        <x-modal name="delete-post-{{ $post->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 class="text-lg font-semibold text-ink-950">Hapus artikel?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    &ldquo;{{ $post->title }}&rdquo; akan dihapus permanen, termasuk gambar sampulnya. Tindakan ini tidak bisa dibatalkan.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus artikel</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
