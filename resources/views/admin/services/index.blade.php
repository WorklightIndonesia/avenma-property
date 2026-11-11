@extends('layouts.admin')

@section('title', 'Layanan')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Layanan</h1>
            <p class="mt-1 max-w-xl text-sm text-ink-500">
                Daftar layanan di halaman <span class="tabular">/layanan</span>, dipecah per kategori. Tombol naik dan turun menggeser urutan di dalam kategorinya saja.
            </p>
        </div>

        <a href="{{ route('admin.services.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah layanan
        </a>
    </div>

    @foreach ($serviceGroups as $group)
        <h2 class="mt-8 text-lg font-semibold text-ink-950">{{ $group['label'] }}</h2>

        <div class="mt-3 overflow-x-auto border border-line bg-white">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-sand text-sm font-semibold text-ink-600">
                    <tr>
                        <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Urutan</span></th>
                        <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Layanan</span></th>
                        <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Tag</span></th>
                        <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($group['services'] as $service)
                        <tr class="min-h-12 border-b border-line last:border-0 hover:bg-paper">
                            <td class="whitespace-nowrap px-4 py-2">
                                <div class="flex items-center gap-1">
                                    <span class="tabular w-6 text-ink-500">{{ $loop->iteration }}</span>

                                    <form method="POST" action="{{ route('admin.services.move', [$service, 'up']) }}">
                                        @csrf
                                        <button type="submit" @disabled($loop->first) aria-label="Naikkan {{ $service->title }}" class="inline-flex h-9 w-9 items-center justify-center rounded text-ink-600 hover:bg-sand disabled:opacity-30 disabled:hover:bg-transparent">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><path d="m6 15 6-6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.services.move', [$service, 'down']) }}">
                                        @csrf
                                        <button type="submit" @disabled($loop->last) aria-label="Turunkan {{ $service->title }}" class="inline-flex h-9 w-9 items-center justify-center rounded text-ink-600 hover:bg-sand disabled:opacity-30 disabled:hover:bg-transparent">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.services.edit', $service) }}" class="font-medium text-ink-950 hover:underline">{{ $service->title }}</a>
                                <p class="mt-0.5 max-w-md truncate text-xs text-ink-500">{{ $service->description }}</p>
                            </td>
                            <td class="px-4 py-2 text-ink-600">{{ implode(', ', $service->tagsList()) ?: '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-1 text-center">
                                <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                                <button type="button" x-on:click="$dispatch('open-modal', 'delete-service-{{ $service->id }}')" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2">
                                <x-empty-state title="Belum ada layanan." description="Belum ada baris di kategori ini. Layanan yang ditambahkan di sini muncul di section {{ $group['label'] }} pada halaman /layanan.">
                                    <x-slot:action>
                                        <a href="{{ route('admin.services.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 hover:bg-gold-700 hover:text-white">Tambah layanan</a>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    @foreach ($serviceGroups->flatMap(fn (array $group) => $group['services']) as $service)
        <x-modal name="delete-service-{{ $service->id }}" labelledby="delete-service-title-{{ $service->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 id="delete-service-title-{{ $service->id }}" class="text-lg font-semibold text-ink-950">Hapus layanan?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    &ldquo;{{ $service->title }}&rdquo; akan hilang dari halaman Layanan. Nomor urut layanan lain menyesuaikan sendiri.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus layanan</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
