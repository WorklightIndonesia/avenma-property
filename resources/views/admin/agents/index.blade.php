@extends('layouts.admin')

@section('title', 'Agen')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Agen</h1>
            <p class="mt-1 text-sm text-ink-500">Agen tidak punya akun login. Datanya dipakai sebagai kontak pada listing yang ia pegang.</p>
        </div>

        <a href="{{ route('admin.agents.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah agen
        </a>
    </div>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Foto</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Nama</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Jabatan</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Telepon</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Email</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($agents as $agent)
                    <tr class="min-h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <img
                                src="{{ $agent->photoUrl() }}"
                                alt=""
                                @class([
                                    'h-10 w-10 rounded-full border border-line',
                                    'object-cover' => $agent->photo_path,
                                    'bg-sand object-contain p-1.5' => ! $agent->photo_path,
                                ])
                            >
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.agents.edit', $agent) }}" class="font-medium text-ink-950 hover:underline">{{ $agent->name }}</a>
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $agent->title ?? '—' }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $agent->phone }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $agent->email }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.agents.edit', $agent) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <button type="button" x-on:click="$dispatch('open-modal', 'delete-agent-{{ $agent->id }}')" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2">
                            <x-empty-state title="Belum ada agen." description="Agen yang ditambahkan di sini bisa dipilih saat mengisi listing.">
                                <x-slot:action>
                                    <a href="{{ route('admin.agents.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 hover:bg-gold-700 hover:text-white">Tambah agen</a>
                                </x-slot:action>
                            </x-empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-pagination :paginator="$agents" />
    </div>

    @foreach ($agents as $agent)
        <x-modal name="delete-agent-{{ $agent->id }}" labelledby="delete-agent-title-{{ $agent->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 id="delete-agent-title-{{ $agent->id }}" class="text-lg font-semibold text-ink-950">Hapus agen?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    Agen &ldquo;{{ $agent->name }}&rdquo; akan dihapus. Listing yang ia pegang tetap tampil, dengan kontak Avenma Properti.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus agen</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
