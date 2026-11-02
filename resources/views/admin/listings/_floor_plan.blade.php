@php
    $storedRooms = $listing->rooms
        ->map(fn ($room) => $room->only(['floor', 'label', 'x', 'y', 'w', 'h', 'tone']))
        ->values()
        ->all();

    // A rejected save puts the drawn rooms back, not the stored ones.
    $oldRooms = old('rooms');
    $roomRows = is_array($oldRooms) ? array_values($oldRooms) : $storedRooms;

    $floorCount = max(1, (int) old('floors', $listing->floors ?: 1));

    foreach ($roomRows as $row) {
        $floorCount = max($floorCount, (int) ($row['floor'] ?? 1));
    }
@endphp

<section
    id="denah"
    class="scroll-mt-20 border border-line bg-white p-5 sm:p-6"
    x-data="floorPlan(@js($roomRows), {{ $floorCount }})"
>
    <h2 class="text-lg font-semibold text-ink-950">Denah</h2>
    <p class="mt-1 text-sm text-ink-500">
        Seret di dalam kotak untuk menggambar ruangan. Seret bagian tengahnya untuk memindahkan,
        seret sudut kanan bawah untuk mengubah ukuran, lalu pilih ruangan untuk memberi nama dan warna.
        Panah menggeser ruangan; Shift + panah mengubah ukurannya.
    </p>

    <div class="mt-4 flex flex-wrap items-center gap-2" role="tablist" aria-label="Lantai denah">
        <template x-for="n in floors" :key="n">
            <button
                type="button"
                role="tab"
                x-bind:aria-selected="floor === n"
                x-on:click="selectFloor(n)"
                class="inline-flex min-h-11 items-center rounded border px-4 text-sm font-semibold"
                :class="floor === n ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line bg-white text-ink-950 hover:bg-sand'"
                x-text="'Lantai ' + n"
            ></button>
        </template>

        <button
            type="button"
            x-on:click="addFloor()"
            class="inline-flex min-h-11 items-center rounded border border-dashed border-line px-4 text-sm font-semibold text-ink-600 hover:bg-sand"
        >+ Lantai</button>
    </div>

    <div class="mt-4 grid gap-5 lg:grid-cols-[minmax(0,360px)_minmax(0,1fr)]">
        <div>
            <div
                x-ref="canvas"
                tabindex="0"
                role="group"
                aria-label="Bidang gambar denah"
                x-on:pointerdown.prevent="start($event)"
                x-on:pointermove="drag($event)"
                x-on:pointerup="end()"
                x-on:pointercancel="end()"
                x-on:lostpointercapture="end()"
                x-on:keydown="keys($event)"
                class="relative aspect-[3/4] w-full touch-none select-none border-2 border-ink-950 bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700"
            >
                <div class="pointer-events-none absolute inset-0 opacity-10" style="background-image: linear-gradient(to right, #14110E 1px, transparent 1px), linear-gradient(to bottom, #14110E 1px, transparent 1px); background-size: 8.3333% 6.25%"></div>

                <template x-for="room in visible" :key="room.id">
                    <div
                        class="absolute flex items-start overflow-hidden border-2 border-ink-950"
                        :class="{
                            'bg-white': room.tone === 'biasa',
                            'bg-gold-300/40': room.tone === 'utama',
                            'border-dashed border-ink-500/60 bg-sand': room.tone === 'luar',
                            'z-10 ring-2 ring-gold-700': active && active.id === room.id,
                        }"
                        :style="roomStyle(room)"
                        x-on:pointerdown.stop.prevent="startMove($event, room)"
                    >
                        <span class="pointer-events-none block p-1 text-[10px] leading-tight text-ink-950" x-text="room.label"></span>

                        <span
                            x-show="active && active.id === room.id"
                            x-on:pointerdown.stop.prevent="startResize($event, room)"
                            class="absolute bottom-0 right-0 h-4 w-4 cursor-se-resize border-l-2 border-t-2 border-ink-950 bg-gold-500"
                        ></span>
                    </div>
                </template>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    x-on:click="addRoom()"
                    class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand"
                >Tambah ruangan</button>

                <button
                    type="button"
                    x-on:click="undo()"
                    x-bind:disabled="history.length === 0"
                    class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand disabled:opacity-50"
                >Urungkan</button>

                <span class="text-xs text-ink-500" x-text="rooms.length + ' ruangan'"></span>
            </div>
        </div>

        <div>
            <template x-if="active">
                <div class="space-y-4">
                    <div>
                        <label for="room-label" class="block text-xs font-medium text-ink-600">Nama ruangan</label>
                        <input
                            id="room-label"
                            type="text"
                            maxlength="40"
                            list="room-labels"
                            x-model="active.label"
                            x-on:input="sync()"
                            placeholder="R. Tamu"
                            class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30"
                        >
                        <datalist id="room-labels">
                            <option value="R. Tamu"></option>
                            <option value="R. Keluarga + Makan"></option>
                            <option value="Dapur"></option>
                            <option value="K. Tidur 1"></option>
                            <option value="K. Tidur 2"></option>
                            <option value="K. Tidur 3"></option>
                            <option value="KM"></option>
                            <option value="KM 2"></option>
                            <option value="Carport"></option>
                            <option value="Taman depan"></option>
                            <option value="Taman belakang"></option>
                            <option value="R. Cuci"></option>
                            <option value="Gudang"></option>
                        </datalist>
                    </div>

                    <fieldset>
                        <legend class="text-xs font-medium text-ink-600">Warna</legend>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="option in toneOptions" :key="option.value">
                                <label
                                    class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded border px-3 text-sm"
                                    :class="active.tone === option.value ? 'border-ink-950 bg-sand' : 'border-line bg-white'"
                                >
                                    <input type="radio" :value="option.value" x-model="active.tone" x-on:change="sync()" class="border-line text-gold-700 focus:ring-gold-500/30">
                                    <span x-text="option.label"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>

                    <button
                        type="button"
                        x-on:click="remove()"
                        class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-medium text-danger hover:bg-sand"
                    >Hapus ruangan</button>
                </div>
            </template>

            <p x-show="!active" class="border border-dashed border-line p-4 text-sm text-ink-500">
                Belum ada ruangan di lantai ini. Seret di dalam kotak untuk menggambar, atau tekan Tambah ruangan.
            </p>
        </div>
    </div>

    <x-input-error class="mt-2" :messages="$errors->get('rooms')" />

    <input type="hidden" name="rooms" x-ref="field" x-bind:value="payload">

    <div class="mt-6 border-t border-line pt-4">
        <h3 class="text-base font-semibold text-ink-950">Atau unggah gambar denah</h3>
        <p class="mt-1 text-sm text-ink-500">
            Dipakai bila developer sudah punya gambar denah sendiri. Gambar yang diunggah menggantikan
            denah yang digambar di atas pada halaman properti.
        </p>

        @if ($listing->floor_plan_path)
            <div class="mt-3 flex items-start gap-3">
                <img src="{{ asset($listing->floor_plan_path) }}" alt="" class="w-32 border border-line">

                <x-checkbox id="remove_floor_plan" name="remove_floor_plan" :checked="old('remove_floor_plan')">
                    Hapus gambar denah
                </x-checkbox>
            </div>
        @endif

        <div class="mt-3">
            <x-input-label for="floor_plan" value="Berkas gambar" />
            <x-file-input id="floor_plan" name="floor_plan" accept="image/jpeg,image/png,image/webp" />
            <p class="mt-1 text-xs text-ink-500">JPG, PNG, atau WebP maksimal 5 MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('floor_plan')" />
        </div>
    </div>
</section>
