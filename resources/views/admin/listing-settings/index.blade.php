@extends('layouts.admin')

@section('title', 'Pengaturan Listing')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-950">Pengaturan Listing</h1>
        <p class="mt-1 text-sm text-ink-500">Pilihan yang muncul di formulir listing. Opsi bawaan sistem tidak bisa dihapus, opsi tambahan bisa.</p>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-success">{{ session('status') }}</p>
    @endif

    @error('option')
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="border border-line bg-white p-5">
            <h2 class="text-lg font-semibold text-ink-950">Developer</h2>
            <p class="mt-1 text-sm text-ink-500">Nama pengembang atau penjual yang bisa dipilih di listing.</p>

            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($developers as $developer)
                    <li class="flex items-center justify-between gap-3 border-b border-line pb-2 last:border-0">
                        <span class="min-w-0">
                            <span class="block truncate text-ink-950">{{ $developer->name }}</span>
                            <span class="block text-xs text-ink-500">{{ $developer->listings_count }} listing</span>
                        </span>

                        <form method="POST" action="{{ route('admin.listing-settings.developers.destroy', $developer) }}" onsubmit="return confirm('Hapus developer ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-ink-500 hover:text-danger">Hapus</button>
                        </form>
                    </li>
                @empty
                    <li class="text-sm text-ink-500">Belum ada developer.</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('admin.listing-settings.developers.store') }}" class="mt-4 border-t border-line pt-4">
                @csrf
                <x-input-label for="developer-name" value="Tambah developer" />
                <div class="mt-1 flex items-center gap-2">
                    <x-text-input id="developer-name" name="name" class="block w-full" placeholder="Contoh: Graha Nusantara" />
                    <button type="submit" class="inline-flex min-h-11 shrink-0 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">Tambah</button>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </form>
        </section>

        @foreach ($groups as $group => $groupLabel)
            <section class="border border-line bg-white p-5">
                <h2 class="text-lg font-semibold text-ink-950">{{ $groupLabel }}</h2>
                <p class="mt-1 text-sm text-ink-500">Opsi bawaan sistem ditandai, sisanya bisa dihapus selama belum dipakai listing.</p>

                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($options[$group] as $value => $label)
                        @php($extra = $custom[$group]->firstWhere('slug', $value))
                        <li class="flex items-center justify-between gap-3 border-b border-line pb-2 last:border-0">
                            <span class="min-w-0 truncate text-ink-950">{{ $label }}</span>

                            @if ($extra)
                                <form method="POST" action="{{ route('admin.listing-settings.options.destroy', $extra) }}" onsubmit="return confirm('Hapus opsi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-ink-500 hover:text-danger">Hapus</button>
                                </form>
                            @else
                                <span class="text-xs text-ink-500">Bawaan</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('admin.listing-settings.options.store') }}" class="mt-4 border-t border-line pt-4">
                    @csrf
                    <input type="hidden" name="group" value="{{ $group }}">

                    <x-input-label for="label-{{ $group }}" value="Tambah opsi" />
                    <div class="mt-1 flex items-center gap-2">
                        <x-text-input id="label-{{ $group }}" name="label" class="block w-full" placeholder="Contoh: {{ $group === 'property_type' ? 'Tanah' : ($group === 'status' ? 'Lelang' : 'Tukar guling') }}" />
                        <button type="submit" class="inline-flex min-h-11 shrink-0 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">Tambah</button>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('label')" />
                </form>
            </section>
        @endforeach
    </div>
@endsection
