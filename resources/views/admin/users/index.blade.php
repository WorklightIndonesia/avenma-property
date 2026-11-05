@extends('layouts.admin')

@section('title', 'Akun')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Akun</h1>
            <p class="mt-1 text-sm text-ink-500">Semua akun adalah admin dan dapat mengelola seluruh konten. Akun terakhir tidak bisa dihapus.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah akun
        </a>
    </div>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Nama</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Email</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Telepon</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="min-h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-ink-950 hover:underline">{{ $user->name }}</a>
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $user->phone ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <button type="button" x-on:click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2">
                            <x-empty-state title="Belum ada akun." description="Akun admin muncul di sini.">
                                <x-slot:action>
                                    <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 hover:bg-gold-700 hover:text-white">Tambah akun</a>
                                </x-slot:action>
                            </x-empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-pagination :paginator="$users" />
    </div>

    {{-- One dialog per row. The controller refuses the last account. --}}
    @foreach ($users as $user)
        <x-modal name="delete-user-{{ $user->id }}" labelledby="delete-user-title-{{ $user->id }}" max-width="md" focusable>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 id="delete-user-title-{{ $user->id }}" class="text-lg font-semibold text-ink-950">Hapus akun?</h2>
                <p class="mt-2 text-sm text-ink-600">
                    Akun &ldquo;{{ $user->name }}&rdquo; ({{ $user->email }}) akan dihapus permanen. Listing dan artikel yang dibuat akun ini tetap tampil, atas nama Avenma Properti.
                </p>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-secondary-button data-autofocus x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-danger-button>Hapus akun</x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
