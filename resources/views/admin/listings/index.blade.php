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

        <a href="{{ route('admin.listings.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah listing
        </a>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    <form id="listing-filters" method="GET" action="{{ route('admin.listings.index') }}" class="mt-6 flex flex-wrap items-center justify-between gap-3" x-data x-on:submit="$refs.focus.value = document.activeElement?.name ?? ''">
        <input type="hidden" name="focus" x-ref="focus">
        <p class="text-xs text-ink-500">Filter dan urutan berlaku otomatis.</p>

        @if (array_filter($filters) || in_array($sort, ['termurah', 'termahal'], true))
            <a href="{{ route('admin.listings.index') }}" class="text-sm font-medium text-ink-950 underline hover:text-gold-700">Reset</a>
        @endif
    </form>

    <div class="mt-3 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[760px] text-sm">
            <thead class="bg-sand text-xs font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left">Properti</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-left">Harga</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Agen</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
                <tr class="border-t border-line font-normal">
                    <th class="px-2 py-2 text-left font-normal">
                        <input form="listing-filters" type="search" name="properti" value="{{ $filters['properti'] ?? '' }}" placeholder="Cari judul" aria-label="Cari judul properti" class="w-full rounded border-line bg-white px-2 py-1.5 text-xs font-normal text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30" x-on:input.debounce.3000ms="$el.form.requestSubmit()" @if (request('focus') === 'properti') x-init="$el.focus(); $el.setSelectionRange($el.value.length, $el.value.length)" @endif>
                    </th>
                    <th class="px-2 py-2 text-left font-normal">
                        <input form="listing-filters" type="search" name="lokasi" value="{{ $filters['lokasi'] ?? '' }}" placeholder="Cari kecamatan atau kota" aria-label="Cari kecamatan atau kota" class="w-full rounded border-line bg-white px-2 py-1.5 text-xs font-normal text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30" x-on:input.debounce.3000ms="$el.form.requestSubmit()" @if (request('focus') === 'lokasi') x-init="$el.focus(); $el.setSelectionRange($el.value.length, $el.value.length)" @endif>
                    </th>
                    <th class="px-2 py-2 text-left font-normal">
                        <select form="listing-filters" name="urut" aria-label="Urutkan harga" class="w-full rounded border-line bg-white py-1.5 pl-2 pr-7 text-xs font-normal text-ink-950 focus:border-gold-700 focus:ring-gold-500/30" x-on:change="$el.form.requestSubmit()">
                            @foreach (App\Models\Listing::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-2 py-2 text-left font-normal">
                        <select form="listing-filters" name="status" aria-label="Filter status" class="w-full rounded border-line bg-white py-1.5 pl-2 pr-7 text-xs font-normal text-ink-950 focus:border-gold-700 focus:ring-gold-500/30" x-on:change="$el.form.requestSubmit()">
                            <option value="">Semua status</option>
                            @foreach (App\Models\Listing::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-2 py-2"></th>
                    <th class="px-2 py-2 text-center">
                        <button type="submit" form="listing-filters" class="sr-only">Terapkan</button>
                    </th>
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
