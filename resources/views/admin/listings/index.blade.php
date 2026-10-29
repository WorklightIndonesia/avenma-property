@extends('layouts.admin')

@section('title', 'Listing')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
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

    @if ($featured && $featured['count'] > $featured['shown'])
        <div class="mt-4 border border-gold-700 bg-gold-300/30 px-4 py-3 text-sm text-ink-950">
            <p class="font-semibold">
                {{ $featured['count'] }} properti ditandai unggulan, beranda hanya menampilkan {{ $featured['shown'] }}.
            </p>
            <p class="mt-1">
                Sisanya tidak tampil di beranda. Lepas centang pada yang paling lama ditandai:
                @if ($featured['oldest'])
                    <a href="{{ route('admin.listings.edit', $featured['oldest']) }}" class="font-semibold underline hover:text-gold-700">{{ $featured['oldest']->title }}</a>.
                @endif
            </p>
        </div>
    @endif

    <form id="listing-filters" method="GET" action="{{ route('admin.listings.index') }}" x-data x-on:submit="$refs.focus.value = document.activeElement?.name ?? ''">
        <input type="hidden" name="focus" x-ref="focus">
        <button type="submit" class="sr-only">Terapkan</button>
    </form>

    @php
    $field = 'block w-full rounded border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30';
    $header = 'flex w-full items-center justify-between gap-2 rounded text-left text-sm font-semibold text-ink-600 hover:text-ink-950';
        $refocus = fn (string $name) => request('focus') === $name
            ? '$nextTick(() => { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length) })'
            : '';

        // A column only opens when its header was the last thing being edited.
        // A filled filter is readable from the header text, so it stays closed.
        $open = in_array(request('focus'), ['properti', 'lokasi', 'urut', 'status'], true)
            ? request('focus')
            : null;
    @endphp

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full table-fixed min-w-[880px] text-sm" x-data="{ open: @js($open) }">
            <colgroup>
                <col class="w-[21%]">
                <col class="w-[17%]">
                <col class="w-[18%]">
                <col class="w-[16%]">
                <col class="w-[9%]">
                <col class="w-[19%]">
            </colgroup>
            <thead class="bg-sand text-sm font-semibold text-ink-600" x-on:click.outside="open = null">
                <tr>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'properti'" x-on:click="open = 'properti'; $nextTick(() => $refs.properti.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ ($filters['properti'] ?? '') !== '' ? $filters['properti'] : 'Properti' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'properti'" x-ref="properti" aria-label="Cari judul properti" form="listing-filters" id="filter-properti" type="search" name="properti" value="{{ $filters['properti'] ?? '' }}" placeholder="Cari judul" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:blur="open = null" x-on:keydown.escape="open = null" x-init="{{ $refocus('properti') }}">
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <button type="button" x-show="open !== 'lokasi'" x-on:click="open = 'lokasi'; $nextTick(() => $refs.lokasi.focus())" class="{{ $header }} min-h-10">
                            <span class="truncate">{{ ($filters['lokasi'] ?? '') !== '' ? $filters['lokasi'] : 'Lokasi' }}</span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                        <input x-cloak x-show="open === 'lokasi'" x-ref="lokasi" aria-label="Cari kecamatan atau kota" form="listing-filters" id="filter-lokasi" type="search" name="lokasi" value="{{ $filters['lokasi'] ?? '' }}" placeholder="Kecamatan atau kota" class="{{ $field }}" x-on:input.debounce.1000ms="$el.form.requestSubmit()" x-on:blur="open = null" x-on:keydown.escape="open = null" x-init="{{ $refocus('lokasi') }}">
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <x-select
                            id="filter-urut"
                            name="urut"
                            form="listing-filters"
                            variant="header"
                            :options="App\Models\Listing::SORTS"
                            :value="$sort ?: 'terbaru'"
                            aria-label="Urutkan harga"
                            :submit="true"
                        />
                    </th>
                    <th class="px-3 py-3 text-left align-top">
                        <x-select
                            id="filter-status"
                            name="status"
                            form="listing-filters"
                            variant="header"
                            :options="App\Models\Listing::STATUSES"
                            :value="$filters['status'] ?? ''"
                            placeholder="Semua status"
                            aria-label="Status penawaran"
                            :submit="true"
                        />
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

                            @if ($listing->is_featured)
                                <span class="ml-2 rounded bg-gold-300 px-1.5 py-0.5 text-xs font-medium text-ink-950">Unggulan</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->location?->name ?? '—' }}</td>
                        <td class="tabular whitespace-nowrap px-4 py-2 text-left">Rp {{ number_format($listing->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->statusLabel() }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->creator?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.listings.edit', $listing) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <button type="button" x-on:click="$dispatch('open-modal', 'delete-listing-{{ $listing->id }}')" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
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

    {{-- One dialog per row, placed outside the table so nothing clips it. --}}
    @foreach ($listings as $listing)
        <x-modal name="delete-listing-{{ $listing->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.listings.destroy', $listing) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 class="text-lg font-semibold text-ink-950">Hapus listing?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    &ldquo;{{ $listing->title }}&rdquo; akan dihapus permanen, termasuk semua fotonya. Tindakan ini tidak bisa dibatalkan.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus listing</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
