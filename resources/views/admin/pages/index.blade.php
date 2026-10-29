@extends('layouts.admin')

@section('title', 'Halaman')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Halaman</h1>
            <p class="mt-1 max-w-xl text-sm text-ink-500">
                Teks, gambar hero, dan tombol tiap halaman. Susunan dan gaya halaman sudah tetap; yang diubah hanya isinya.
            </p>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Halaman</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Alamat</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Bagian</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($skeleton as $key => $page)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2 font-medium text-ink-950">{{ $page['label'] }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $page['path'] }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ count($page['sections']) }} bagian</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            @if ($pages->has($key))
                                <a href="{{ route('admin.pages.edit', $pages[$key]) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Ubah isi</a>
                            @else
                                <span class="px-3 text-ink-500">Belum ada data</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
