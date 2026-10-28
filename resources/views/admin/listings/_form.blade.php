@php
    $select = 'mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
    $check = 'rounded border-line text-gold-700 focus:ring-gold-500/30';
    $priceOld = old('price', $listing->price);
    $priceValue = is_numeric($priceOld) && (int) $priceOld > 0 ? number_format((int) $priceOld, 0, ',', '.') : '';
    // The error bag has no wildcard lookup, so collect the per-file messages by hand.
    $photoErrors = collect($errors->keys())
        ->filter(fn ($key) => str_starts_with($key, 'photos'))
        ->flatMap(fn ($key) => $errors->get($key))
        ->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-6">
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
                <x-input-label for="category_id" value="Kategori" />
                <select id="category_id" name="category_id" class="{{ $select }}">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('category_id', $listing->category_id) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
            </div>

            <div>
                <x-input-label for="developer_id" value="Developer" />
                <select id="developer_id" name="developer_id" class="{{ $select }}">
                    <option value="">Tanpa developer</option>
                    @foreach ($developers as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('developer_id', $listing->developer_id) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('developer_id')" />
            </div>

            <div>
                <x-input-label for="property_type" value="Tipe properti" />
                <select id="property_type" name="property_type" class="{{ $select }}">
                    @foreach ($propertyTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('property_type', $listing->property_type ?? 'rumah') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('property_type')" />
            </div>

            <div>
                <x-input-label for="listing_type" value="Jenis penawaran" />
                <select id="listing_type" name="listing_type" class="{{ $select }}">
                    @foreach ($listingTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('listing_type', $listing->listing_type ?? 'primary') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('listing_type')" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="{{ $select }}">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $listing->status ?? 'dijual') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>

            <div>
                <x-input-label for="location_id" value="Lokasi" />
                <select id="location_id" name="location_id" class="{{ $select }}">
                    <option value="">Tanpa lokasi</option>
                    @foreach ($locations as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('location_id', $listing->location_id) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('location_id')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="address" value="Alamat" />
                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $listing->address)" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="description" value="Deskripsi" />
                <textarea id="description" name="description" rows="6" class="{{ $select }}" required>{{ old('description', $listing->description) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Harga dan luas</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-3">
                <x-input-label for="price" value="Harga (Rp)" />
                {{-- shortcut: the caret jumps to the end when editing mid-number --}}
                <x-text-input
                    id="price"
                    name="price"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    class="tabular mt-1 block w-full"
                    :value="$priceValue"
                    required
                    x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
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

            <div>
                <x-input-label for="land_area" value="Luas tanah (m²)" />
                <x-text-input id="land_area" name="land_area" type="number" min="0" class="mt-1 block w-full" :value="old('land_area', $listing->land_area)" />
                <x-input-error class="mt-2" :messages="$errors->get('land_area')" />
            </div>

            <div>
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
                <label for="is_published" class="flex min-h-11 items-center gap-3 text-sm font-medium text-ink-950">
                    <input id="is_published" name="is_published" type="checkbox" value="1" class="{{ $check }}" @checked(old('is_published', $listing->is_published ?? true))>
                    Terbitkan di website
                </label>

                <label for="is_featured" class="flex min-h-11 items-center gap-3 text-sm font-medium text-ink-950">
                    <input id="is_featured" name="is_featured" type="checkbox" value="1" class="{{ $check }}" @checked(old('is_featured', $listing->is_featured ?? false))>
                    Tandai sebagai unggulan
                </label>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="map_embed" value="Google Maps" />
                <textarea id="map_embed" name="map_embed" rows="3" class="{{ $select }}" placeholder="Tempel URL atau iframe Google Maps">{{ old('map_embed', $listing->map_embed) }}</textarea>
                <p class="mt-1 text-xs text-ink-500">Kosongkan untuk memakai pencarian alamat otomatis.</p>
                <x-input-error class="mt-2" :messages="$errors->get('map_embed')" />
            </div>
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Foto</h2>
        <p class="mt-1 text-sm text-ink-500">Label tampil sebagai chip di galeri. Alt menjelaskan foto untuk pembaca layar. Urutan kecil tampil lebih dulu.</p>

        @if ($listing->exists)
            @foreach ($listing->images as $image)
                <div class="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-[88px_minmax(0,1fr)_minmax(0,1fr)_72px_auto] sm:items-end">
                    <img src="{{ asset($image->path) }}" alt="" class="aspect-[4/3] w-32 rounded border border-line object-cover sm:w-full">

                    <div>
                        <x-input-label for="label-{{ $image->id }}" value="Label" />
                        <x-text-input id="label-{{ $image->id }}" name="images[{{ $image->id }}][label]" type="text" maxlength="60" class="mt-1 block w-full" :value="old('images.'.$image->id.'.label', $image->label)" placeholder="Tampak depan" />
                    </div>

                    <div>
                        <x-input-label for="alt-{{ $image->id }}" value="Alt" />
                        <x-text-input id="alt-{{ $image->id }}" name="images[{{ $image->id }}][alt]" type="text" maxlength="255" class="mt-1 block w-full" :value="old('images.'.$image->id.'.alt', $image->alt)" />
                    </div>

                    <div>
                        <x-input-label for="sort-{{ $image->id }}" value="Urutan" />
                        <x-text-input id="sort-{{ $image->id }}" name="images[{{ $image->id }}][sort_order]" type="number" min="0" max="999" class="mt-1 block w-full" :value="old('images.'.$image->id.'.sort_order', $image->sort_order)" />
                    </div>

                    <button
                        type="submit"
                        form="delete-photo-{{ $image->id }}"
                        class="inline-flex h-11 items-center justify-center rounded border border-line px-4 text-sm font-medium text-danger hover:bg-sand"
                    >Hapus</button>
                </div>
            @endforeach
        @endif

        <div class="mt-4 border-t border-line pt-4">
            <x-input-label for="photos" value="Tambah foto" />
            <input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-ink-600 file:mr-3 file:rounded file:border-0 file:bg-sand file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-ink-950 hover:file:bg-line">
            <p class="mt-1 text-xs text-ink-500">JPG, PNG, atau WebP. Maksimal 5 MB per foto, 10 foto sekali unggah.</p>
            <x-input-error class="mt-2" :messages="$photoErrors" />
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <x-primary-button>{{ $listing->exists ? 'Simpan perubahan' : 'Simpan listing' }}</x-primary-button>
        <a href="{{ route('admin.listings.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Batal</a>
    </div>
</form>

{{-- Form elements cannot nest, so each delete form lives outside and its button points here. --}}
@if ($listing->exists)
    @foreach ($listing->images as $image)
        <form id="delete-photo-{{ $image->id }}" method="POST" action="{{ route('admin.listings.photos.destroy', [$listing, $image]) }}" onsubmit="return confirm('Hapus foto ini?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
