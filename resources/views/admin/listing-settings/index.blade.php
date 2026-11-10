@extends('layouts.admin')

@section('title', 'Pengaturan Listing')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-950">Pengaturan Listing</h1>
        <p class="mt-1 text-sm text-ink-500">Pilihan yang muncul di formulir listing. Nama opsi bisa diubah, dan opsi bisa dihapus selama belum dipakai listing.</p>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-success">{{ session('status') }}</p>
    @endif

    @error('option')
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror

    @error('option_label')
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="border border-line bg-white p-5" x-data="{ q: '' }">
            <h2 class="text-lg font-semibold text-ink-950">Developer</h2>
            <p class="mt-1 text-sm text-ink-500">Nama pengembang atau penjual yang bisa dipilih di listing.</p>

            <input type="search" x-model="q" aria-label="Cari developer" placeholder="Cari developer" class="mt-4 block w-full rounded border border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30">

            <ul class="mt-2 space-y-2 text-sm">
                @forelse ($developers as $developer)
                    <li class="flex items-center justify-between gap-3 border-b border-line pb-2 last:border-0" data-search="{{ mb_strtolower($developer->name) }}" x-show="q === '' || $el.dataset.search.includes(q.toLowerCase())">
                        <span class="min-w-0">
                            <span class="block truncate text-ink-950">{{ $developer->name }}</span>
                            <span class="block text-xs text-ink-500">{{ $developer->listings_count }} listing</span>
                        </span>

                        <span class="flex shrink-0 items-center gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.developers.edit', $developer) }}" class="text-ink-500 hover:text-ink-950">Edit</a>
                            <button type="button" x-on:click="$dispatch('open-modal', 'delete-setting-developer-{{ $developer->id }}')" class="text-ink-500 hover:text-danger">Hapus</button>
                        </span>
                    </li>
                @empty
                    <li class="text-sm text-ink-500">Belum ada developer.</li>
                @endforelse
            </ul>

            @foreach ($developers as $developer)
                <x-modal name="delete-setting-developer-{{ $developer->id }}" labelledby="delete-setting-developer-title-{{ $developer->id }}" max-width="md" focusable>
                    <form method="POST" action="{{ route('admin.listing-settings.developers.destroy', $developer) }}" class="p-6">
                        @csrf
                        @method('DELETE')

                        <h2 id="delete-setting-developer-title-{{ $developer->id }}" class="text-lg font-semibold text-ink-950">Hapus developer?</h2>
                        <p class="mt-2 text-sm text-ink-600">
                            &ldquo;{{ $developer->name }}&rdquo; akan dihapus dari pilihan listing. Developer yang masih dipakai listing tidak bisa dihapus.
                        </p>

                        <div class="mt-6 flex flex-wrap justify-end gap-3">
                            <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-danger-button>Hapus developer</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endforeach

            <form method="POST" action="{{ route('admin.listing-settings.developers.store') }}" class="mt-4 space-y-2 border-t border-line pt-4">
                @csrf
                <x-input-label for="developer-name" value="Tambah developer" />

                <x-text-input id="developer-name" name="name" class="block w-full" placeholder="Nama, contoh: Graha Nusantara" aria-label="Nama developer" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />

                <div class="grid gap-2 sm:grid-cols-2">
                    <x-text-input id="developer-whatsapp" name="whatsapp" type="tel" class="block w-full" placeholder="WhatsApp, 08…" aria-label="Nomor WhatsApp developer" />
                    <x-text-input id="developer-email" name="email" type="email" class="block w-full" placeholder="Email" aria-label="Email developer" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">Tambah</button>
            </form>
        </section>

        @foreach ($groups as $group => $groupLabel)
            <section class="border border-line bg-white p-5" x-data="{ q: '' }">
                <h2 class="text-lg font-semibold text-ink-950">{{ $groupLabel }}</h2>
                <p class="mt-1 text-sm text-ink-500">{{ $groupHints[$group] ?? 'Opsi yang belum dipakai listing bisa dihapus, termasuk bawaan sistem.' }}</p>

                <input type="search" x-model="q" aria-label="Cari {{ mb_strtolower($groupLabel) }}" placeholder="Cari {{ mb_strtolower($groupLabel) }}" class="mt-4 block w-full rounded border border-line bg-white px-3 py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30">

                <ul class="mt-2 space-y-2 text-sm">
                    @forelse ($options[$group] as $option)
                        <li class="flex items-center justify-between gap-3 border-b border-line pb-2 last:border-0" data-search="{{ mb_strtolower($option->label) }}" x-show="q === '' || $el.dataset.search.includes(q.toLowerCase())">
                            <span class="min-w-0 truncate text-ink-950">{{ $option->label }}</span>

                            <span class="flex shrink-0 items-center gap-3 text-xs font-semibold">
                                <button type="button" x-on:click="$dispatch('open-modal', 'edit-setting-option-{{ $option->id }}')" class="text-ink-500 hover:text-ink-950">Edit</button>
                                <button type="button" x-on:click="$dispatch('open-modal', 'delete-setting-option-{{ $option->id }}')" class="text-ink-500 hover:text-danger">Hapus</button>
                            </span>
                        </li>
                    @empty
                        <li class="text-sm text-ink-500">Belum ada opsi.</li>
                    @endforelse
                </ul>

                @foreach ($options[$group] as $option)
                    <x-modal name="delete-setting-option-{{ $option->id }}" labelledby="delete-setting-option-title-{{ $option->id }}" max-width="md" focusable>
                        <form method="POST" action="{{ route('admin.listing-settings.options.destroy', $option) }}" class="p-6">
                            @csrf
                            @method('DELETE')

                            <h2 id="delete-setting-option-title-{{ $option->id }}" class="text-lg font-semibold text-ink-950">Hapus opsi?</h2>
                            <p class="mt-2 text-sm text-ink-600">
                                &ldquo;{{ $option->label }}&rdquo; akan dihapus dari daftar pilihan. Opsi yang sudah dipakai listing tidak bisa dihapus.
                            </p>

                            <div class="mt-6 flex flex-wrap justify-end gap-3">
                                <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                                <x-danger-button>Hapus opsi</x-danger-button>
                            </div>
                        </form>
                    </x-modal>

                    <x-modal name="edit-setting-option-{{ $option->id }}" labelledby="edit-setting-option-title-{{ $option->id }}" max-width="md" focusable>
                        <form method="POST" action="{{ route('admin.listing-settings.options.update', $option) }}" class="p-6">
                            @csrf
                            @method('PUT')

                            <h2 id="edit-setting-option-title-{{ $option->id }}" class="text-lg font-semibold text-ink-950">Edit opsi</h2>
                            <p class="mt-2 text-sm text-ink-600">
                                Nama baru berlaku di formulir listing dan halaman publik. Listing yang sudah memakai opsi ini tetap terhubung.
                            </p>

                            <x-input-label for="edit-option-label-{{ $option->id }}" value="Nama opsi" class="mt-4" />
                            <x-text-input id="edit-option-label-{{ $option->id }}" name="label" class="mt-1 block w-full" :value="$option->label" required />
                            <x-input-error class="mt-2" :messages="$errors->get('option_label')" />

                            <div class="mt-6 flex flex-wrap justify-end gap-3">
                                <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                                <x-primary-button>Simpan</x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach

                <form method="POST" action="{{ route('admin.listing-settings.options.store') }}" class="mt-4 space-y-2 border-t border-line pt-4">
                    @csrf
                    <input type="hidden" name="group" value="{{ $group }}">

                    <x-input-label for="label-{{ $group }}" value="Tambah opsi" />
                    <div class="flex items-center gap-2">
                        <x-text-input id="label-{{ $group }}" name="label" class="block w-full" placeholder="Contoh: {{ ['property_type' => 'Tanah', 'status' => 'Lelang', 'water_source' => 'Air Hujan', 'electricity_power' => '7.700 VA', 'certificate' => 'SHM'][$group] ?? 'Tukar guling' }}" />
                        <button type="submit" class="inline-flex min-h-11 shrink-0 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">Tambah</button>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('label')" />
                </form>
            </section>
        @endforeach
    </div>
@endsection
