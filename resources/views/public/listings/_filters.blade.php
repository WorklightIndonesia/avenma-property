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

    $inputClass = 'mt-1 block w-full scroll-mt-24 rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30 disabled:bg-sand disabled:text-ink-500';

    $countOptions = fn (int $max) => collect(range(1, $max))->mapWithKeys(fn (int $n) => [$n => $n.'+'])->all();
    $hargaOptions = collect(App\Models\Listing::PRICE_BUCKETS)->map(fn (array $bucket) => $bucket['label'])->all();

    $regionData = [
        'provinces' => $regions['provinces'],
        'cities' => $regions['cities'],
        'districts' => $regions['districts'],
        'province' => $filters['provinsi'],
        'city' => $filters['kota'],
        'district' => $filters['kecamatan'],
    ];
@endphp

<div id="katalog" class="border border-line bg-paper">
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

    {{-- Open on desktop where it is a sidebar; collapsed on phones to keep the
         results close to the top of the page. --}}
    <div x-data="{ open: window.matchMedia('(min-width: 1024px)').matches }">
        <button
            type="button"
            x-on:click="open = ! open"
            x-bind:aria-expanded="open"
            aria-controls="{{ $prefix }}filter-panel"
            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-ink-950 hover:bg-sand lg:cursor-default lg:hover:bg-transparent"
        >
            <span>
                Filter
                @if ($activeCount > 0)
                    <span class="text-ink-500">· {{ $activeCount }} aktif</span>
                @endif
            </span>

            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0 text-ink-500">
                <path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        {{-- Same 0fr → 1fr height animation as the accordion inside it. --}}
        <div
            x-cloak
            id="{{ $prefix }}filter-panel"
            class="grid transition-[grid-template-rows,opacity] duration-300 ease-out motion-reduce:transition-none"
            x-bind:class="open ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
        >
        <div class="overflow-hidden" x-bind:inert="! open">
        <form
            method="GET"
            action="{{ $action }}"
            class="border-t border-line"
            x-data="catalogFilter(@js($regionData))"
            x-on:click.outside="open = ''"
        >
            <x-filter-group title="Lokasi">
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
            </x-filter-group>

            <x-filter-group title="Tipe Properti">
                <div>
                    <label for="{{ $prefix }}filter-tipe" class="block text-xs font-medium text-ink-500">Tipe</label>
                    <x-select :id="$prefix.'filter-tipe'" name="tipe" :options="$typeOptions" :value="$filters['tipe']" placeholder="Semua tipe" aria-label="Tipe properti" class="mt-1 scroll-mt-24" />
                </div>

                <div>
                    <label for="{{ $prefix }}filter-status" class="block text-xs font-medium text-ink-500">Status</label>
                    <x-select :id="$prefix.'filter-status'" name="status" :options="$statusOptions" :value="$filters['status']" placeholder="Semua status" aria-label="Status penawaran" class="mt-1 scroll-mt-24" />
                </div>

                <div>
                    <label for="{{ $prefix }}filter-penawaran" class="block text-xs font-medium text-ink-500">Jenis penawaran</label>
                    <x-select :id="$prefix.'filter-penawaran'" name="penawaran" :options="$listingTypeOptions" :value="$filters['penawaran']" placeholder="Semua penawaran" aria-label="Jenis penawaran" class="mt-1 scroll-mt-24" />
                </div>
            </x-filter-group>

            <x-filter-group title="Fasilitas">
                <div>
                    <label for="{{ $prefix }}filter-kamar" class="block text-xs font-medium text-ink-500">Kamar tidur</label>
                    <x-select :id="$prefix.'filter-kamar'" name="kamar" :options="$countOptions(5)" :value="$filters['kamar']" placeholder="Semua" aria-label="Kamar tidur minimal" class="mt-1 scroll-mt-24" />
                </div>

                <div>
                    <label for="{{ $prefix }}filter-mandi" class="block text-xs font-medium text-ink-500">Kamar mandi</label>
                    <x-select :id="$prefix.'filter-mandi'" name="mandi" :options="$countOptions(4)" :value="$filters['mandi']" placeholder="Semua" aria-label="Kamar mandi minimal" class="mt-1 scroll-mt-24" />
                </div>

                <div>
                    <label for="{{ $prefix }}filter-carport" class="block text-xs font-medium text-ink-500">Carport</label>
                    <x-select :id="$prefix.'filter-carport'" name="carport" :options="$countOptions(4)" :value="$filters['carport']" placeholder="Semua" aria-label="Carport minimal" class="mt-1 scroll-mt-24" />
                </div>
            </x-filter-group>

            <x-filter-group title="Dimensi Properti">
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
                    <label for="{{ $prefix }}filter-lantai" class="block text-xs font-medium text-ink-500">Jumlah lantai minimal</label>
                    <x-select :id="$prefix.'filter-lantai'" name="lantai" :options="$countOptions(3)" :value="$filters['lantai']" placeholder="Semua" aria-label="Jumlah lantai minimal" class="mt-1 scroll-mt-24" />
                </div>
            </x-filter-group>

            <x-filter-group title="Harga dan Developer">
                <div>
                    <label for="{{ $prefix }}filter-harga" class="block text-xs font-medium text-ink-500">Harga</label>
                    <x-select :id="$prefix.'filter-harga'" name="harga" :options="$hargaOptions" :value="$filters['harga']" placeholder="Semua harga" aria-label="Rentang harga" class="mt-1 scroll-mt-24" />
                </div>

                <div>
                    <label for="{{ $prefix }}filter-developer" class="block text-xs font-medium text-ink-500">Developer</label>
                    <x-select :id="$prefix.'filter-developer'" name="developer" :options="$developerOptions" :value="$filters['developer']" placeholder="Semua developer" aria-label="Developer" class="mt-1 scroll-mt-24" />
                </div>
            </x-filter-group>

            <div class="flex flex-wrap items-center gap-3 border-t border-line px-4 py-4">
                <x-primary-button>Terapkan</x-primary-button>
                <a href="{{ $isPreview ? '#' : route('public.listings.index') }}" class="inline-flex items-center rounded px-3 py-3 text-sm font-semibold text-ink-600 hover:text-ink-950">Batal</a>
                <span class="text-xs text-ink-500">Perubahan berlaku setelah Terapkan.</span>
            </div>

            <input type="hidden" name="provinsi" :value="selProv ?? ''">
            <input type="hidden" name="kota" :value="selCity ?? ''">
            <input type="hidden" name="kecamatan" :value="selDist ?? ''">
            {{-- Kept out of the panel, but applying a filter must not reset it. --}}
            <input type="hidden" name="per" value="{{ $perPage }}">
            <input type="hidden" name="urut" value="{{ $sort }}">
        </form>
        </div>
        </div>
    </div>
</div>
