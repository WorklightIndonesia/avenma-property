@php
    $activeCount = count($chips);
    $prefix = $idPrefix ?? '';
    $isPreview = $action === '#';
    $query = request()->query();
    // Removing a level removes the levels below it, otherwise the URL keeps a
    // kota that the server drops anyway.
    $children = ['provinsi' => ['kota', 'kecamatan'], 'kota' => ['kecamatan']];
    $removeUrl = fn (string $key) => $isPreview
        ? '#'
        : route('public.listings.index', Arr::except($query, [...($children[$key] ?? []), $key, 'page']));

    $selectClass = 'mt-1 block w-full scroll-mt-24 rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
    $inputClass = 'mt-1 block w-full scroll-mt-24 rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30 disabled:bg-sand disabled:text-ink-500';

    $regionData = [
        'provinces' => $regions['provinces'],
        'cities' => $regions['cities'],
        'districts' => $regions['districts'],
        'province' => $filters['provinsi'],
        'city' => $filters['kota'],
        'district' => $filters['kecamatan'],
    ];
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

        <form
            method="GET"
            action="{{ $action }}"
            class="grid gap-4 border-t border-line px-4 py-5 sm:grid-cols-2 lg:grid-cols-3"
            x-data="catalogFilter(@js($regionData))"
            x-on:click.outside="open = ''"
        >
            {{-- Options hold only regions that have a published listing, and the
                 visible inputs are search boxes: the hidden inputs below are what
                 reaches the server, and only after Terapkan. --}}
            <div class="relative">
                <label for="{{ $prefix }}filter-provinsi" class="block text-xs font-medium text-ink-500">Provinsi</label>
                <input
                    id="{{ $prefix }}filter-provinsi"
                    type="text"
                    x-model="qProv"
                    x-on:input="typeProvince($event.target.value)"
                    x-on:focus="focusField('province')"
                    x-on:keydown="keys($event, options(provinces, qProv, selProv), pickProvince, 'province')"
                    autocomplete="off"
                    class="{{ $inputClass }}"
                    placeholder="Semua provinsi"
                >
                <ul x-show="open === 'province'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(provinces, qProv, selProv)" :key="item.name">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickProvince(item)" class="flex min-h-11 w-full items-center justify-between gap-3 border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'">
                                <span x-text="item.name"></span>
                                <span class="text-xs text-ink-500" x-text="item.count"></span>
                            </button>
                        </li>
                    </template>
                    <li x-show="options(provinces, qProv, selProv).length === 0" class="px-3 py-2 text-sm text-ink-500">Belum ada properti terbit di sini.</li>
                </ul>
            </div>

            <div class="relative">
                <label for="{{ $prefix }}filter-kota" class="block text-xs font-medium text-ink-500">Kota / kabupaten</label>
                <input
                    id="{{ $prefix }}filter-kota"
                    type="text"
                    x-model="qCity"
                    x-bind:disabled="!selProv"
                    x-on:input="typeCity($event.target.value)"
                    x-on:focus="selProv && focusField('city')"
                    x-on:keydown="keys($event, options(cities, qCity, selCity), pickCity, 'city')"
                    autocomplete="off"
                    class="{{ $inputClass }}"
                    x-bind:placeholder="selProv ? 'Semua kota' : 'Pilih provinsi dulu'"
                >
                <ul x-show="open === 'city'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(cities, qCity, selCity)" :key="item.name">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickCity(item)" class="flex min-h-11 w-full items-center justify-between gap-3 border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'">
                                <span x-text="item.name"></span>
                                <span class="text-xs text-ink-500" x-text="item.count"></span>
                            </button>
                        </li>
                    </template>
                    <li x-show="options(cities, qCity, selCity).length === 0" class="px-3 py-2 text-sm text-ink-500">Belum ada properti terbit di sini.</li>
                </ul>
            </div>

            <div class="relative">
                <label for="{{ $prefix }}filter-kecamatan" class="block text-xs font-medium text-ink-500">Kecamatan</label>
                <input
                    id="{{ $prefix }}filter-kecamatan"
                    type="text"
                    x-model="qDistrict"
                    x-bind:disabled="!selCity"
                    x-on:input="typeDistrict($event.target.value)"
                    x-on:focus="selCity && focusField('district')"
                    x-on:keydown="keys($event, options(districts, qDistrict, selDist), pickDistrict, 'district')"
                    autocomplete="off"
                    class="{{ $inputClass }}"
                    x-bind:placeholder="selCity ? 'Semua kecamatan' : 'Pilih kota dulu'"
                >
                <ul x-show="open === 'district'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(districts, qDistrict, selDist)" :key="item.name">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickDistrict(item)" class="flex min-h-11 w-full items-center justify-between gap-3 border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'">
                                <span x-text="item.name"></span>
                                <span class="text-xs text-ink-500" x-text="item.count"></span>
                            </button>
                        </li>
                    </template>
                    <li x-show="options(districts, qDistrict, selDist).length === 0" class="px-3 py-2 text-sm text-ink-500">Belum ada properti terbit di sini.</li>
                </ul>
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
                <label for="{{ $prefix }}filter-penawaran" class="block text-xs font-medium text-ink-500">Jenis penawaran</label>
                <select id="{{ $prefix }}filter-penawaran" name="penawaran" class="{{ $selectClass }}">
                    <option value="">Semua penawaran</option>
                    @foreach ($listingTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['penawaran'] === (string) $value)>{{ $label }}</option>
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
                <label for="{{ $prefix }}filter-harga" class="block text-xs font-medium text-ink-500">Harga</label>
                <select id="{{ $prefix }}filter-harga" name="harga" class="{{ $selectClass }}">
                    <option value="">Semua harga</option>
                    @foreach (App\Models\Listing::PRICE_BUCKETS as $value => $bucket)
                        <option value="{{ $value }}" @selected($filters['harga'] === $value)>{{ $bucket['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-kamar" class="block text-xs font-medium text-ink-500">Kamar tidur</label>
                <select id="{{ $prefix }}filter-kamar" name="kamar" class="{{ $selectClass }}">
                    <option value="">Semua</option>
                    @foreach (range(1, 5) as $count)
                        <option value="{{ $count }}" @selected($filters['kamar'] === (string) $count)>{{ $count }}+</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-mandi" class="block text-xs font-medium text-ink-500">Kamar mandi</label>
                <select id="{{ $prefix }}filter-mandi" name="mandi" class="{{ $selectClass }}">
                    <option value="">Semua</option>
                    @foreach (range(1, 4) as $count)
                        <option value="{{ $count }}" @selected($filters['mandi'] === (string) $count)>{{ $count }}+</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-lt" class="block text-xs font-medium text-ink-500">Luas tanah minimal (m²)</label>
                <input
                    id="{{ $prefix }}filter-lt"
                    name="lt"
                    type="number"
                    min="1"
                    step="1"
                    inputmode="numeric"
                    value="{{ $filters['lt'] }}"
                    placeholder="Semua"
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label for="{{ $prefix }}filter-lb" class="block text-xs font-medium text-ink-500">Luas bangunan minimal (m²)</label>
                <input
                    id="{{ $prefix }}filter-lb"
                    name="lb"
                    type="number"
                    min="1"
                    step="1"
                    inputmode="numeric"
                    value="{{ $filters['lb'] }}"
                    placeholder="Semua"
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label for="{{ $prefix }}filter-lantai" class="block text-xs font-medium text-ink-500">Jumlah lantai</label>
                <select id="{{ $prefix }}filter-lantai" name="lantai" class="{{ $selectClass }}">
                    <option value="">Semua</option>
                    @foreach (range(1, 3) as $count)
                        <option value="{{ $count }}" @selected($filters['lantai'] === (string) $count)>{{ $count }}+</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="{{ $prefix }}filter-developer" class="block text-xs font-medium text-ink-500">Developer</label>
                <select id="{{ $prefix }}filter-developer" name="developer" class="{{ $selectClass }}">
                    <option value="">Semua developer</option>
                    @foreach ($developerOptions as $id => $name)
                        <option value="{{ $id }}" @selected($filters['developer'] === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <fieldset id="{{ $prefix }}filter-urut" tabindex="-1" class="scroll-mt-24 sm:col-span-2 lg:col-span-3">
                <legend class="text-xs font-medium text-ink-500">Urutkan</legend>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach (App\Models\Listing::SORTS as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="urut" value="{{ $value }}" @checked($sort === $value) class="peer sr-only">
                            <span class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-medium text-ink-600 hover:border-ink-950 peer-checked:border-ink-950 peer-checked:bg-ink-950 peer-checked:text-paper peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-gold-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-3">
                <x-primary-button>Terapkan</x-primary-button>
                <a href="{{ $isPreview ? '#' : route('public.listings.index') }}" class="inline-flex items-center rounded px-3 py-3 text-sm font-semibold text-ink-600 hover:text-ink-950">Batal</a>
                <span class="text-xs text-ink-500">Perubahan berlaku setelah Terapkan.</span>
            </div>

            <input type="hidden" name="provinsi" :value="selProv ?? ''">
            <input type="hidden" name="kota" :value="selCity ?? ''">
            <input type="hidden" name="kecamatan" :value="selDist ?? ''">
            {{-- Kept out of the panel, but applying a filter must not reset it. --}}
            <input type="hidden" name="per" value="{{ $perPage }}">
        </form>
    </details>
</div>
