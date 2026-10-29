@extends('layouts.admin')

@section('title', 'Developer')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">Developer</h1>
            <p class="mt-1 text-sm text-ink-500">Developer yang bisa dipilih saat membuat atau mengubah listing.</p>
        </div>

        <div class="flex items-center gap-2">
            @if (($filters['nama'] ?? '') !== '' || in_array($sort, ['tersedikit', 'terbanyak'], true))
                <a href="{{ route('admin.developers.index') }}" class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium text-ink-600 transition-colors duration-150 hover:bg-sand hover:text-ink-950">Reset filter</a>
            @endif

            <a href="{{ route('admin.developers.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Tambah developer
            </a>
        </div>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="mt-4 rounded border border-danger/30 bg-white px-4 py-3 text-sm text-danger">{{ $errors->first() }}</p>
    @endif

    <form id="developer-filters" method="GET" action="{{ route('admin.developers.index') }}" x-data x-on:submit="$refs.focus.value = document.activeElement?.name ?? ''">
        <input type="hidden" name="focus" x-ref="focus">
        <button type="submit" class="sr-only">Terapkan</button>
    </form>

    @php
        $field = 'mt-1.5 block w-full rounded border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30';
        $refocus = fn (string $key) => request('focus') === $key
            ? '$nextTick(() => { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length) })'
            : '';

        $name = $filters['nama'] ?? '';
        $sortAsc = $sort === 'tersedikit';
        $sortDesc = $sort === 'terbanyak';
        // Only the column that was just edited opens; a filled filter is already
        // readable from the header text.
        $open = request('focus') === 'nama' ? 'nama' : null;
    @endphp

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full table-fixed min-w-[400px] text-sm" x-data="{ open: @js($open) }">
            <colgroup>
                <col>
                <col class="w-24">
                <col class="w-40">
            </colgroup>
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'nama'" x-on:click="open = 'nama'; $nextTick(() => $refs.nama.focus())" class="flex w-full min-h-10 items-center justify-between gap-2 rounded text-left text-sm font-semibold text-ink-600 hover:text-ink-950">
                            <span class="truncate">{{ $name !== '' ? $name : 'Nama' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'nama'" x-ref="nama" aria-label="Cari nama developer" form="developer-filters" id="filter-nama" type="search" name="nama" value="{{ $name }}" placeholder="Cari nama" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:blur="open = null" x-on:keydown.escape="open = null" x-init="{{ $refocus('nama') }}">
                    </th>
                    <th class="px-4 py-3 text-right align-top">
                        <a href="{{ route('admin.developers.index', array_filter(['nama' => $name, 'urut' => $sortAsc ? 'terbanyak' : 'tersedikit'])) }}" class="flex min-h-10 items-center justify-end gap-2 rounded text-sm font-semibold text-ink-600 hover:text-ink-950">
                            <span>Listing</span>
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
                @forelse ($developers as $developer)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.developers.edit', $developer) }}" class="font-medium text-ink-950 hover:underline">{{ $developer->name }}</a>
                        </td>
                        <td class="tabular px-4 py-2 text-right text-ink-600">{{ $developer->listings_count }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.developers.edit', $developer) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <form method="POST" action="{{ route('admin.developers.destroy', $developer) }}" class="inline" onsubmit="return confirm('Hapus developer ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-ink-500">
                            @if ($name !== '')
                                Tidak ada developer yang cocok.
                                <a href="{{ route('admin.developers.index') }}" class="font-medium text-ink-950 underline hover:text-gold-700">Reset filter</a>
                            @else
                                Belum ada developer.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $developers->links() }}
    </div>
@endsection
