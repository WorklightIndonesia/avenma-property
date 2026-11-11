@php
    $priceOld = old('price');
    $priceValue = is_numeric($priceOld) && (int) $priceOld > 0 ? number_format((int) $priceOld, 0, ',', '.') : '';
@endphp

<form
    method="POST"
    action="{{ route('public.submissions.store') }}"
    class="mt-6 space-y-4"
    x-data="{ propertyType: @js(old('property_type')), certificate: @js(old('certificate')) }"
>
    @csrf

    {{-- Bots fill this; people never see it. --}}
    <div class="hidden" aria-hidden="true">
        <label for="website">Website</label>
        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>

    <div>
        <x-input-label for="owner_name" value="Nama pemilik" />
        <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name')" autocomplete="name" required />
        <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
    </div>

    <div>
        <x-input-label for="phone" value="Nomor WhatsApp" />
        <x-text-input id="phone" name="phone" type="tel" inputmode="tel" class="mt-1 block w-full" :value="old('phone')" autocomplete="tel" placeholder="08…" required />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="property_type" value="Jenis properti" />
        <x-select
            id="property_type"
            name="property_type"
            :options="array_merge($propertyTypes, [\App\Models\PropertySubmission::OTHER => 'Lainnya'])"
            :value="old('property_type')"
            placeholder="Pilih jenis properti"
            aria-label="Jenis properti"
            class="mt-1"
            x-on:select="propertyType = $event.detail.value"
        />
        <x-input-error class="mt-2" :messages="$errors->get('property_type')" />
    </div>

    <div x-show="propertyType === 'lainnya'" x-cloak>
        <x-input-label for="property_type_other" value="Jenis properti lainnya" />
        <x-text-input id="property_type_other" name="property_type_other" type="text" maxlength="100" class="mt-1 block w-full" :value="old('property_type_other')" placeholder="Contoh: gudang berpendingin" :required="old('property_type') === 'lainnya'" x-bind:required="propertyType === 'lainnya'" />
        <x-input-error class="mt-2" :messages="$errors->get('property_type_other')" />
    </div>

    <div>
        <x-input-label for="address" value="Alamat properti" />
        <x-textarea id="address" name="address" rows="3" maxlength="500" required>{{ old('address') }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>

    <fieldset>
        <legend class="text-sm font-medium text-ink-950">Titip jual atau sewa</legend>
        <div class="mt-1 flex flex-wrap gap-5">
            @foreach (\App\Models\PropertySubmission::OFFERS as $value => $label)
                <label class="inline-flex min-h-11 items-center gap-2 text-sm text-ink-950">
                    <input type="radio" name="offer" value="{{ $value }}" @checked(old('offer', 'jual') === $value) class="h-4 w-4 border-line text-gold-700 focus:ring-gold-500/30">
                    {{ $label }}
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('offer')" />
    </fieldset>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="land_area" value="Luas tanah (m²)" />
            <x-text-input id="land_area" name="land_area" type="number" min="0" class="mt-1 block w-full" :value="old('land_area')" />
            <x-input-error class="mt-2" :messages="$errors->get('land_area')" />
        </div>

        <div>
            <x-input-label for="building_area" value="Luas bangunan (m²)" />
            <x-text-input id="building_area" name="building_area" type="number" min="0" class="mt-1 block w-full" :value="old('building_area')" />
            <x-input-error class="mt-2" :messages="$errors->get('building_area')" />
        </div>

        <div>
            <x-input-label for="bedrooms" value="Kamar tidur" />
            <x-text-input id="bedrooms" name="bedrooms" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('bedrooms')" />
            <x-input-error class="mt-2" :messages="$errors->get('bedrooms')" />
        </div>

        <div>
            <x-input-label for="bathrooms" value="Kamar mandi" />
            <x-text-input id="bathrooms" name="bathrooms" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('bathrooms')" />
            <x-input-error class="mt-2" :messages="$errors->get('bathrooms')" />
        </div>

        <div>
            <x-input-label for="facing" value="Hadap" />
            <x-select
                id="facing"
                name="facing"
                :options="\App\Models\PropertySubmission::FACINGS"
                :value="old('facing')"
                placeholder="Tidak diisi"
                aria-label="Hadap"
                class="mt-1"
            />
            <x-input-error class="mt-2" :messages="$errors->get('facing')" />
        </div>

        <div>
            <x-input-label for="certificate" value="Legalitas" />
            <x-select
                id="certificate"
                name="certificate"
                :options="array_merge($certificates, [\App\Models\PropertySubmission::OTHER => 'Lainnya'])"
                :value="old('certificate')"
                placeholder="Pilih legalitas"
                aria-label="Legalitas"
                class="mt-1"
                x-on:select="certificate = $event.detail.value"
            />
            <x-input-error class="mt-2" :messages="$errors->get('certificate')" />
        </div>

        <div x-show="certificate === 'lainnya'" x-cloak>
            <x-input-label for="certificate_other" value="Legalitas lainnya" />
            <x-text-input id="certificate_other" name="certificate_other" type="text" maxlength="100" class="mt-1 block w-full" :value="old('certificate_other')" placeholder="Contoh: PPJB warisan" :required="old('certificate') === 'lainnya'" x-bind:required="certificate === 'lainnya'" />
            <x-input-error class="mt-2" :messages="$errors->get('certificate_other')" />
        </div>
    </div>

    <div>
        <x-input-label for="price" value="Harga jual / sewa (Rp)" />
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

    <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">Kirim data properti</button>

    <p class="text-xs leading-5 text-ink-500">
        Komisi jual {{ \App\Models\PropertySubmission::commissionLabel('jual') }} dan sewa {{ \App\Models\PropertySubmission::commissionLabel('sewa') }} dari harga transaksi.
    </p>
</form>
