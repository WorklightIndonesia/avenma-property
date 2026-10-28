@extends('layouts.admin')

@section('title', 'Akun')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">Akun</h1>
            <p class="mt-1 text-sm text-ink-500">Admin mengelola semua data, agen hanya listing miliknya sendiri.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah akun
        </a>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="mt-4 rounded border border-danger/30 bg-white px-4 py-3 text-sm text-danger">{{ $errors->first() }}</p>
    @endif

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-sand text-xs font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Telepon</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-ink-950 hover:underline">{{ $user->name }}</a>
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $user->phone ?? '—' }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $roles[$user->role] ?? $user->role }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-ink-500">Belum ada akun.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
