@php
    $priceOld = old('price', $listing->price);
    $priceValue = is_numeric($priceOld) && (int) $priceOld > 0 ? number_format((int) $priceOld, 0, ',', '.') : '';
    // The error bag has no wildcard lookup, so collect the per-file messages by hand.
    $photoErrors = collect($errors->keys())
        ->filter(fn ($key) => str_starts_with($key, 'photos'))
        ->flatMap(fn ($key) => $errors->get($key))
        ->all();

    // old('location_id') wins, so a failed save keeps the region the agent picked.
    $current = $currentLocation;
    $initial = [
        'address' => (string) old('address', $listing->address),
        'province' => $current?->province,
        'city' => $current?->city,
        'district' => $current?->name,
        'locationId' => $current?->id,
        'latitude' => old('latitude', $listing->latitude),
        'longitude' => old('longitude', $listing->longitude),
        'regionUrl' => route('admin.wilayah'),
        'addressUrl' => route('admin.alamat'),
    ];
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ propertyType: @js(old('property_type', $listing->property_type ?? 'rumah_siap_huni')), apartmentTypes: @js(\App\Models\Listing::APARTMENT_TYPES), landOnlyTypes: @js(\App\Models\Listing::LAND_TYPES), floorTypes: @js(\App\Models\Listing::FLOOR_TYPES) }" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Data properti</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="title" value="Judul" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $listing->title)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('title')" />
            </div>

            <div>
                <x-input-label for="developer_id" value="Developer" />
                <x-select
                    id="developer_id"
                    name="developer_id"
                    :options="$developers"
                    :value="old('developer_id', $listing->developer_id)"
                    placeholder="Tanpa developer"
                    aria-label="Developer"
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('developer_id')" />
            </div>

            <div>
                <x-input-label for="property_type" value="Tipe properti" />
                <x-select
                    id="property_type"
                    name="property_type"
                    :options="$propertyTypes"
                    :value="old('property_type', $listing->property_type ?? 'rumah_siap_huni')"
                    aria-label="Tipe properti"
                    class="mt-1"
                    x-on:select="propertyType = $event.detail.value"
                />
                <x-input-error class="mt-2" :messages="$errors->get('property_type')" />
            </div>

            <div>
                <x-input-label for="listing_type" value="Jenis penawaran" />
                <x-select
                    id="listing_type"
                    name="listing_type"
                    :options="$listingTypes"
                    :value="old('listing_type', $listing->listing_type ?? 'primary')"
                    aria-label="Jenis penawaran"
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('listing_type')" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <x-select
                    id="status"
                    name="status"
                    :options="$statuses"
                    :value="old('status', $listing->status ?? 'dijual')"
                    aria-label="Status penawaran"
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>

            <div>
                <x-input-label for="certificate" value="Kelengkapan Surat" />
                <x-text-input id="certificate" name="certificate" type="text" list="certificate-options" class="mt-1 block w-full" :value="old('certificate', $listing->certificate)" placeholder="Contoh: Surat Hak Milik" />
                <datalist id="certificate-options">
                    <option value="Surat Hak Milik"></option>
                    <option value="Surat Balik Nama"></option>
                </datalist>
                <x-input-error class="mt-2" :messages="$errors->get('certificate')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="description" value="Deskripsi dan Fasilitas" />
                <x-textarea id="description" name="description" rows="6" required>{{ old('description', $listing->description) }}</x-textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>
        </div>
    </section>

    {{-- Lokasi: typed address + pin preview + the three official region pickers. --}}
    <section
        class="border border-line bg-white p-5 sm:p-6"
        x-data="listingLocation(@js($initial))"
        x-on:click.outside="open = ''; suggestOpen = false; suggestActive = -1"
    >
        <h2 class="text-lg font-semibold text-ink-950">Lokasi</h2>
        <p class="mt-1 text-sm text-ink-500">Ketik alamat, klik saran bila cocok (tidak wajib), lalu lengkapi tiga tingkat wilayah dari daftar resmi. Tandai titik lokasi sebelum listing diterbitkan.</p>

        <div class="mt-4">
            <x-input-label for="address" value="Alamat lengkap" />
            <div class="relative">
                <input
                    id="address"
                    name="address"
                    type="text"
                    x-model="address"
                    x-on:input="onAddressInput()"
                    x-on:focus="open = ''; suggestOpen = suggestions.length > 0"
                    x-on:keydown="addressKeys($event)"
                    autocomplete="off"
                    class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30"
                    placeholder="Ketik minimal 3 huruf, mis. bintaro jaya sektor 9"
                >

                <ul x-show="suggestOpen" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-64 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(suggestion, index) in suggestions" :key="index">
                        <li x-on:mouseenter="suggestActive = index">
                            <button
                                type="button"
                                x-on:click="pickSuggestion(suggestion)"
                                class="block min-h-11 w-full border-l-2 px-3 py-2 text-left text-sm"
                                :class="index === suggestActive ? 'border-gold-700 bg-gold-500/20' : 'border-transparent hover:bg-sand'"
                            >
                                <span class="block font-medium" x-text="suggestion.label"></span>
                                <span class="block text-xs text-ink-500" x-text="contextOf(suggestion)"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <p class="mt-1 text-xs text-ink-500" x-show="!suggestNote">Saran dari OpenStreetMap (Photon). Boleh diabaikan — ketik alamat sendiri tetap bisa.</p>
            <p class="mt-1 text-xs text-danger" x-show="suggestNote" x-text="suggestNote"></p>
            <p class="mt-1 text-xs text-ink-500" x-show="suggestBusy">Mencari saran…</p>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button
                type="button"
                x-on:click="lihatPin()"
                class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold hover:bg-sand"
            >Lihat pin lokasi</button>

            <span class="text-xs text-ink-500" x-show="pinBusy">Mencari titik…</span>
            <span class="text-xs text-ink-500" x-show="pinNote && !pinBusy" x-text="pinNote"></span>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
        <x-input-error class="mt-2" :messages="$errors->get('longitude')" />

        <div x-show="pinSrc" x-cloak class="mt-3">
            <iframe :src="pinSrc" class="h-72 w-full border border-line" loading="lazy" title="Pratinjau pin lokasi"></iframe>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <div class="relative">
                <x-input-label for="provinsi" value="Provinsi" />
                <input
                    id="provinsi"
                    type="text"
                    x-model="qProv"
                    x-on:input="typeProvince($event.target.value)"
                    x-on:focus="suggestOpen = false; focusField('province')"
                    x-on:keydown="keys($event, options(provinces, qProv, selProv), pickProvince, 'province')"
                    autocomplete="off"
                    class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30"
                    placeholder="Ketik provinsi"
                >
                <ul x-show="open === 'province'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(provinces, qProv, selProv)" :key="item">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickProvince(item)" class="flex min-h-11 w-full items-center border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'" x-text="item"></button>
                        </li>
                    </template>
                    <li x-show="!busy && options(provinces, qProv, selProv).length === 0" class="px-3 py-2 text-sm text-ink-500">Tidak ada nama resmi yang cocok</li>
                    <li x-show="busy" class="px-3 py-2 text-sm text-ink-500">Memuat…</li>
                </ul>
            </div>

            <div class="relative">
                <x-input-label for="kota" value="Kota / kabupaten" />
                <input
                    id="kota"
                    type="text"
                    x-model="qCity"
                    x-bind:disabled="!selProv"
                    x-on:input="typeCity($event.target.value)"
                    x-on:focus="suggestOpen = false; selProv && focusField('city')"
                    x-on:keydown="keys($event, options(cities, qCity, selCity), pickCity, 'city')"
                    autocomplete="off"
                    class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30 disabled:bg-sand disabled:text-ink-500"
                    x-bind:placeholder="selProv ? 'Ketik kota/kabupaten' : 'Pilih provinsi dulu'"
                >
                <ul x-show="open === 'city'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(cities, qCity, selCity)" :key="item">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickCity(item)" class="flex min-h-11 w-full items-center border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'" x-text="item"></button>
                        </li>
                    </template>
                    <li x-show="!busy && options(cities, qCity, selCity).length === 0" class="px-3 py-2 text-sm text-ink-500">Tidak ada nama resmi yang cocok</li>
                    <li x-show="busy" class="px-3 py-2 text-sm text-ink-500">Memuat…</li>
                </ul>
            </div>

            <div class="relative">
                <x-input-label for="kecamatan" value="Kecamatan" />
                <input
                    id="kecamatan"
                    type="text"
                    x-model="qDistrict"
                    x-bind:disabled="!selCity"
                    x-on:input="typeDistrict($event.target.value)"
                    x-on:focus="suggestOpen = false; selCity && focusField('district')"
                    x-on:keydown="keys($event, options(districts, qDistrict, selDist), pickDistrict, 'district')"
                    autocomplete="off"
                    class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30 disabled:bg-sand disabled:text-ink-500"
                    x-bind:placeholder="selCity ? 'Ketik kecamatan' : 'Pilih kota dulu'"
                >
                <ul x-show="open === 'district'" x-on:click.stop x-cloak class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-line bg-white shadow-float">
                    <template x-for="(item, index) in options(districts, qDistrict, selDist)" :key="item.id">
                        <li x-on:mouseenter="active = index">
                            <button type="button" x-on:click="pickDistrict(item)" class="flex min-h-11 w-full items-center border-l-2 px-3 text-left text-sm" :class="index === active ? 'border-gold-700 bg-gold-500/20 font-medium' : 'border-transparent hover:bg-sand'" x-text="item.name"></button>
                        </li>
                    </template>
                    <li x-show="!busy && options(districts, qDistrict, selDist).length === 0" class="px-3 py-2 text-sm text-ink-500">Tidak ada nama resmi yang cocok</li>
                    <li x-show="busy" class="px-3 py-2 text-sm text-ink-500">Memuat…</li>
                </ul>
                <p class="mt-1 text-xs text-danger" x-show="qDistrict && !selLocation">Pilih kecamatan dari daftar, bukan hanya diketik.</p>
                <x-input-error class="mt-2" :messages="$errors->get('location_id')" />
            </div>
        </div>

        <p class="mt-3 text-xs" x-show="fillNote" x-text="fillNote" :class="fillOk ? 'text-success' : 'text-danger'"></p>

        <input type="hidden" name="location_id" :value="selLocation?.id ?? ''">
        <input type="hidden" name="latitude" :value="pin?.lat ?? ''">
        <input type="hidden" name="longitude" :value="pin?.lng ?? ''">
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Harga dan luas</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-3">
                <x-input-label for="price" value="Harga (Rp)" />
                <x-text-input
                    id="price"
                    name="price"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    class="tabular mt-1 block w-full"
                    :value="$priceValue"
                    required
                    x-data="priceInput"
                    x-on:input="format()"
                />
                <x-input-error class="mt-2" :messages="$errors->get('price')" />
            </div>

            <div>
                <x-input-label for="bedrooms" value="Kamar tidur" />
                <x-text-input id="bedrooms" name="bedrooms" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('bedrooms', $listing->bedrooms)" />
                <x-input-error class="mt-2" :messages="$errors->get('bedrooms')" />
            </div>

            <div>
                <x-input-label for="bathrooms" value="Kamar mandi" />
                <x-text-input id="bathrooms" name="bathrooms" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('bathrooms', $listing->bathrooms)" />
                <x-input-error class="mt-2" :messages="$errors->get('bathrooms')" />
            </div>

            <div>
                <x-input-label for="carport" value="Carport" />
                <x-text-input id="carport" name="carport" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('carport', $listing->carport)" />
                <x-input-error class="mt-2" :messages="$errors->get('carport')" />
            </div>

            <div x-show="!apartmentTypes.includes(propertyType)" x-cloak>
                <x-input-label for="land_area" value="Luas tanah (m²)" />
                <x-text-input id="land_area" name="land_area" type="number" min="0" class="mt-1 block w-full" :value="old('land_area', $listing->land_area)" />
                <x-input-error class="mt-2" :messages="$errors->get('land_area')" />
            </div>

            <div x-show="floorTypes.includes(propertyType)" x-cloak>
                <x-input-label for="floors" value="Jumlah lantai" />
                <x-text-input id="floors" name="floors" type="number" min="1" max="99" class="mt-1 block w-full" :value="old('floors', $listing->floors)" />
                <x-input-error class="mt-2" :messages="$errors->get('floors')" />
            </div>

            <div x-show="!landOnlyTypes.includes(propertyType)" x-cloak>
                <x-input-label for="building_area" value="Luas bangunan (m²)" />
                <x-text-input id="building_area" name="building_area" type="number" min="0" class="mt-1 block w-full" :value="old('building_area', $listing->building_area)" />
                <x-input-error class="mt-2" :messages="$errors->get('building_area')" />
            </div>
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Publikasi</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="contact_phone" value="Nomor WhatsApp agen" />
                <x-text-input id="contact_phone" name="contact_phone" type="tel" class="mt-1 block w-full" :value="old('contact_phone', $listing->contact_phone)" placeholder="{{ auth()->user()->phone }}" />
                <p class="mt-1 text-xs text-ink-500">Kosongkan untuk memakai nomor Anda, atau nomor kantor bila kosong.</p>
                <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
            </div>

            <div class="space-y-3 sm:pt-7">
                <x-checkbox id="is_published" name="is_published" :checked="old('is_published', $listing->is_published ?? true)">
                    Terbitkan di website
                </x-checkbox>
                <p class="text-xs text-ink-500">Listing terbit wajib punya titik lokasi di peta.</p>

                <x-checkbox id="show_kpr" name="show_kpr" :checked="old('show_kpr', $listing->show_kpr ?? true)">
                    Tampilkan simulasi KPR untuk listing ini?
                </x-checkbox>
                <p class="text-xs text-ink-500">Muncul di bawah halaman properti. Listing berstatus disewa tidak menampilkannya.</p>

                @if (auth()->user()->role === App\Models\User::ROLE_ADMIN)
                    <x-checkbox id="is_featured" name="is_featured" :checked="old('is_featured', $listing->is_featured ?? false)">
                        Tandai sebagai unggulan
                    </x-checkbox>
                    <p class="text-xs text-ink-500">Unggulan tampil lebih dulu di beranda. Hanya admin yang dapat menandainya.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Foto</h2>
        <p id="photo-order-help" class="mt-1 text-sm text-ink-500">
            Label tampil sebagai chip di galeri. Alt menjelaskan foto untuk pembaca layar.
            Tarik ikon titik enam, atau pakai tombol panah di atasnya, untuk mengubah urutan: foto paling atas menjadi sampul.
        </p>

        @if ($listing->exists)
            <div
                x-data="photoOrder"
                data-photo-order
                x-on:dragstart="begin($event)"
                x-on:dragover.prevent="over($event)"
                x-on:drop.prevent="end()"
                x-on:dragend="end()"
                x-on:mouseup="release($event)"
            >
                @foreach ($listing->images as $image)
                    <div data-sort-row class="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-[40px_88px_minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                        <div class="flex items-center gap-2 sm:w-full sm:flex-col sm:justify-center">
                            <button
                                type="button"
                                data-sort-handle
                                aria-label="Ubah urutan foto"
                                aria-describedby="photo-order-help"
                                class="inline-flex h-11 w-11 cursor-grab items-center justify-center rounded text-ink-500 transition-colors duration-150 hover:bg-sand hover:text-ink-950 active:cursor-grabbing"
                                x-on:mousedown="grab($event)"
                                x-on:keydown="keys($event)"
                            >
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                    <circle cx="9" cy="5" r="1.6" /><circle cx="15" cy="5" r="1.6" />
                                    <circle cx="9" cy="12" r="1.6" /><circle cx="15" cy="12" r="1.6" />
                                    <circle cx="9" cy="19" r="1.6" /><circle cx="15" cy="19" r="1.6" />
                                </svg>
                            </button>

                            <span data-sort-index class="tabular text-xs text-ink-500">{{ $loop->iteration }}</span>
                            <input type="hidden" data-sort-input name="images[{{ $image->id }}][sort_order]" value="{{ $loop->iteration }}">
                        </div>

                        <img src="{{ asset($image->path) }}" alt="" class="aspect-[4/3] w-32 rounded border border-line object-cover sm:w-full">

                        <div>
                            <x-input-label for="label-{{ $image->id }}" value="Label" />
                            <x-text-input id="label-{{ $image->id }}" name="images[{{ $image->id }}][label]" type="text" maxlength="60" class="mt-1 block w-full" :value="old('images.'.$image->id.'.label', $image->label)" placeholder="Tampak depan" />
                        </div>

                        <div>
                            <x-input-label for="alt-{{ $image->id }}" value="Alt" />
                            <x-text-input id="alt-{{ $image->id }}" name="images[{{ $image->id }}][alt]" type="text" maxlength="255" class="mt-1 block w-full" :value="old('images.'.$image->id.'.alt', $image->alt)" />
                        </div>

                        <button
                            type="button"
                            x-on:click="$dispatch('open-modal', 'delete-photo-{{ $image->id }}')"
                            class="inline-flex h-11 items-center justify-center rounded border border-line px-4 text-sm font-medium text-danger hover:bg-sand"
                        >Hapus</button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4 border-t border-line pt-4">
            <x-input-label for="photos" value="Tambah foto" />
            <x-file-input id="photos" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" />
            <p class="mt-1 text-xs text-ink-500">JPG, PNG, atau WebP. Maksimal 5 MB per foto, 10 foto sekali unggah, {{ \App\Models\Listing::MAX_PHOTOS }} foto per listing.</p>
            <x-input-error class="mt-2" :messages="$photoErrors" />
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <x-primary-button>{{ $listing->exists ? 'Simpan perubahan' : 'Simpan listing' }}</x-primary-button>
        <a href="{{ route('admin.listings.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Batal</a>
    </div>
</form>

 {{-- Each photo dialog lives outside the main form: form elements cannot nest. --}}
@if ($listing->exists)
    @foreach ($listing->images as $image)
        <x-modal name="delete-photo-{{ $image->id }}" labelledby="delete-photo-title-{{ $image->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.listings.photos.destroy', [$listing, $image]) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 id="delete-photo-title-{{ $image->id }}" class="text-lg font-semibold text-ink-950">Hapus foto?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    Foto &ldquo;{{ $image->label ?: 'tanpa label' }}&rdquo; akan dihapus dari galeri listing ini. Tindakan ini tidak bisa dibatalkan.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus foto</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endif
