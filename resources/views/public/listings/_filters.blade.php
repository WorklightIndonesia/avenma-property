@php
    $activeCount = count($chips);
    $prefix = $idPrefix ?? '';
    $isPreview = $action === '#';
    $query = request()->query();
    $removeUrl = fn (string $key) => $isPreview
        ? '#'
        : route('public.listings.index', Arr::except($query, [$key, 'page']));

    $selectClass = 'mt-1 block w-full scroll-mt-24 rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
@endphp

<div id="katalog" class="mt-6 border border-line bg-paper">
    @if ($activeCount > 0)
        <div class="flex flex-wrap items-center gap-2 border-b border-line bg-sand px-4 py-3">
            <span class="text-xs font-medium text-ink-500">Filter aktif</span>

            @foreach ($chips as $chip)
                <span class="inline-flex items-center rounded border border-line bg-white text-sm">
                    <a
                        href="#{{ $prefix }}filter-{{ $chip['key'] }}"
                        data-focus="#{{ $prefix }}filter-{{ $chip['key'] }}"
                        class="px-3 py-1.5 font-medium text-ink-950 hover:bg-sand"
                    >{{ $chip['label'] }}</a>
                    <a
                        href="{{ $removeUrl($chip['key']) }}"
                        aria-label="Hapus filter {{ $chip['label'] }}"
                        class="border-l border-line px-2.5 py-1.5 text-ink-500 hover:bg-sand hover:text-danger"
                    >&times;</a>
                </span>
            @endforeach

            <a href="{{ $isPreview ? '#' : route('public.listings.index') }}" class="ml-1 text-sm underline underline-offset-4 hover:text-ink-950">Hapus semua</a>
        </div>
    @endif

    {{-- Open when filters are applied, so the current values stay visible after Terapkan. --}}
    <details class="group" @if ($activeCount > 0) open @endif>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-ink-950 hover:bg-sand [&::-webkit-details-marker]:hidden">
            <span>
                Filter
                @if ($activeCount > 0)
                    <span class="text-ink-500">· {{ $activeCount }} aktif</span>
                @endif
            </span>
            <span class="text-xs font-medium text-ink-500 group-open:hidden">Buka</span>
            <span class="hidden text-xs font-medium text-ink-500 group-open:inline">Tutup</span>
        </summary>

        <form method="GET" action="{{ $action }}" class="grid gap-4 border-t border-line px-4 py-5 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label for="{{ $prefix }}filter-lokasi" class="block text-xs font-medium text-ink-500">Lokasi</label>
                <select id="{{ $prefix }}filter-lokasi" name="lokasi" class="{{ $selectClass }}">
                    <option value="">Semua lokasi</option>
                    @foreach ($locations as $slug => $name)
                        <option value="{{ $slug }}" @selected($filters['lokasi'] === (string) $slug)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-tipe" class="block text-xs font-medium text-ink-500">Tipe</label>
                <select id="{{ $prefix }}filter-tipe" name="tipe" class="{{ $selectClass }}">
                    <option value="">Semua tipe</option>
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['tipe'] === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-harga" class="block text-xs font-medium text-ink-500">Harga</label>
                <select id="{{ $prefix }}filter-harga" name="harga" class="{{ $selectClass }}">
                    <option value="">Semua harga</option>
                    @foreach (App\Models\Listing::PRICE_BUCKETS as $value => $bucket)
                        <option value="{{ $value }}" @selected($filters['harga'] === $value)>{{ $bucket['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-status" class="block text-xs font-medium text-ink-500">Status</label>
                <select id="{{ $prefix }}filter-status" name="status" class="{{ $selectClass }}">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-per" class="block text-xs font-medium text-ink-500">Per halaman</label>
                <select id="{{ $prefix }}filter-per" name="per" class="{{ $selectClass }}">
                    @foreach (App\Models\Listing::PER_PAGE_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} properti</option>
                    @endforeach
                </select>
            </div>

            <fieldset id="{{ $prefix }}filter-urut" tabindex="-1" class="scroll-mt-24 sm:col-span-2 lg:col-span-1">
                <legend class="text-xs font-medium text-ink-500">Urutkan</legend>
                <div class="mt-1 space-y-0.5">
                    @foreach (App\Models\Listing::SORTS as $value => $label)
                        <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-ink-700">
                            <input type="radio" name="urut" value="{{ $value }}" @checked($sort === $value) class="border-line text-gold-700 focus:ring-gold-500/30">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-6">
                <x-primary-button>Terapkan</x-primary-button>
                <a href="{{ $isPreview ? '#' : route('public.listings.index') }}" class="inline-flex items-center rounded px-3 py-3 text-sm font-semibold text-ink-600 hover:text-ink-950">Batal</a>
                <span class="text-xs text-ink-500">Perubahan berlaku setelah Terapkan.</span>
            </div>
        </form>
    </details>
</div>
