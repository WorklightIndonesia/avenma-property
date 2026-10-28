@extends('layouts.admin')

@section('title', 'Developer')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">Developer</h1>
            <p class="mt-1 text-sm text-ink-500">Developer yang bisa dipilih saat membuat atau mengubah listing.</p>
        </div>

        <a href="{{ route('admin.developers.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah developer
        </a>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="mt-4 rounded border border-danger/30 bg-white px-4 py-3 text-sm text-danger">{{ $errors->first() }}</p>
    @endif

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-sand text-xs font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-right">Listing</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($developers as $developer)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.developers.edit', $developer) }}" class="font-medium text-ink-950 hover:underline">{{ $developer->name }}</a>
                        </td>
                        <td class="tabular px-4 py-2 text-right text-ink-600">{{ $developer->listings_count }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-right">
                            <a href="{{ route('admin.developers.edit', $developer) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <form method="POST" action="{{ route('admin.developers.destroy', $developer) }}" class="inline" onsubmit="return confirm('Hapus developer ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-ink-500">Belum ada developer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $developers->links() }}
    </div>
@endsection
