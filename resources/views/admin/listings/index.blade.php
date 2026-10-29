@extends('layouts.admin')

@section('title', 'Listing')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">Listing</h1>
            <p class="mt-1 text-sm text-ink-500">
                {{ auth()->user()->role === App\Models\User::ROLE_ADMIN ? 'Semua listing di website.' : 'Listing yang Anda buat.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if (array_filter($filters) || in_array($sort, ['termurah', 'termahal'], true))
                <a href="{{ route('admin.listings.index') }}" class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium text-ink-600 transition-colors duration-150 hover:bg-sand hover:text-ink-950">Reset filter</a>
            @endif

            <a href="{{ route('admin.listings.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Tambah listing
            </a>
        </div>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    <form id="listing-filters" method="GET" action="{{ route('admin.listings.index') }}" x-data x-on:submit="$refs.focus.value = document.activeElement?.name ?? ''">
        <input type="hidden" name="focus" x-ref="focus">
        <button type="submit" class="sr-only">Terapkan</button>
    </form>

    @php
        $select = 'mt-1.5 block w-full rounded border-line bg-white py-2 pl-3 pr-8 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
        $field = 'mt-1.5 block w-full rounded border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30';
        $header = 'flex w-full items-center justify-between gap-2 rounded text-left text-xs font-semibold text-ink-600 hover:text-ink-950';
        $refocus = fn (string $name) => request('focus') === $name
            ? '$nextTick(() => { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length) })'
            : '';

        // The column that was being edited stays open across the reload; otherwise
        // the first column carrying a value opens, so no active filter is hidden.
        $active = collect([
            'properti' => $filters['properti'] ?? '',
            'lokasi' => $filters['lokasi'] ?? '',
            'urut' => in_array($sort, ['termurah', 'termahal'], true) ? $sort : '',
            'status' => $filters['status'] ?? '',
        ])->filter()->keys()->first();

        $open = in_array(request('focus'), ['properti', 'lokasi', 'urut', 'status'], true)
            ? request('focus')
            : $active;
    @endphp

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[880px] text-sm" x-data="{
            open: @js($open),
            filled: @js([
                'properti' => ($filters['properti'] ?? '') !== '',
                'lokasi' => ($filters['lokasi'] ?? '') !== '',
                'urut' => in_array($sort, ['termurah', 'termahal'], true),
                'status' => ($filters['status'] ?? '') !== '',
            ]),
            closeEmpty() {
                if (this.open && ! this.filled[this.open]) {
                    this.open = null;
                }
            },
        }">
            <thead class="bg-sand text-xs font-semibold text-ink-600" x-on:click.outside="closeEmpty()">
                <tr>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'properti'" x-on:click="open = 'properti'; $nextTick(() => $refs.properti.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ ($filters['properti'] ?? '') !== '' ? $filters['properti'] : 'Properti' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'properti'" x-ref="properti" aria-label="Cari judul properti" form="listing-filters" id="filter-properti" type="search" name="properti" value="{{ $filters['properti'] ?? '' }}" placeholder="Cari judul" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:keydown.escape="open = null" x-init="{{ $refocus('properti') }}">
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'lokasi'" x-on:click="open = 'lokasi'; $nextTick(() => $refs.lokasi.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ ($filters['lokasi'] ?? '') !== '' ? $filters['lokasi'] : 'Lokasi' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'lokasi'" x-ref="lokasi" aria-label="Cari kecamatan atau kota" form="listing-filters" id="filter-lokasi" type="search" name="lokasi" value="{{ $filters['lokasi'] ?? '' }}" placeholder="Kecamatan atau kota" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:keydown.escape="open = null" x-init="{{ $refocus('lokasi') }}">
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'urut'" x-on:click="open = 'urut'; $nextTick(() => $refs.urut.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ App\Models\Listing::SORTS[$sort] ?? 'Harga' }}</span>
                            @if ($sort === 'termurah')
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M12 19V5m0 0-6 6m6-6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @elseif ($sort === 'termahal')
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M12 5v14m0 0 6-6m-6 6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M8 7l4-4 4 4M8 17l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </button>
                        <select x-cloak x-show="open === 'urut'" x-ref="urut" aria-label="Urutkan harga" form="listing-filters" id="filter-urut" name="urut" class="{{ $select }}" x-on:change="$el.form.requestSubmit()" x-on:keydown.escape="open = null">
                            @foreach (App\Models\Listing::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'status'" x-on:click="open = 'status'; $nextTick(() => $refs.status.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ App\Models\Listing::STATUSES[$filters['status'] ?? ''] ?? 'Status' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <select x-cloak x-show="open === 'status'" x-ref="status" aria-label="Filter status" form="listing-filters" id="filter-status" name="status" class="{{ $select }}" x-on:change="$el.form.requestSubmit()" x-on:keydown.escape="open = null">
                            <option value="">Semua status</option>
                            @foreach (App\Models\Listing::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center">Agen</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.listings.edit', $listing) }}" class="font-medium text-ink-950 hover:underline">{{ $listing->title }}</a>

                            @unless ($listing->is_published)
                                <span class="ml-2 rounded bg-sand px-1.5 py-0.5 text-xs font-medium text-ink-500">Draf</span>
                            @endunless
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->location?->name ?? '—' }}</td>
                        <td class="tabular whitespace-nowrap px-4 py-2 text-left">Rp {{ number_format($listing->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->statusLabel() }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->creator?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.listings.edit', $listing) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <form method="POST" action="{{ route('admin.listings.destroy', $listing) }}" class="inline" onsubmit="return confirm('Hapus listing ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-ink-500">
                            @if (array_filter($filters))
                                Tidak ada listing yang cocok dengan filter.
                                <a href="{{ route('admin.listings.index') }}" class="font-medium text-ink-950 underline hover:text-gold-700">Reset filter</a>
                            @else
                                Belum ada listing.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $listings->links() }}
    </div>
@endsection
