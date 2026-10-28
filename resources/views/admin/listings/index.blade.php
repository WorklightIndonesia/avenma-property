@extends('layouts.admin')

@section('title', 'Listing')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">Listing</h1>
            <p class="mt-1 text-sm text-ink-500">
                {{ auth()->user()->role === App\Models\User::ROLE_ADMIN ? 'Semua listing di website.' : 'Listing yang Anda buat.' }}
            </p>
        </div>

        <a href="{{ route('admin.listings.create') }}" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Tambah listing
        </a>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-sand text-xs font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left">Properti</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-right">Harga</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Agen</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr class="h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.listings.edit', $listing) }}" class="font-medium text-ink-950 hover:underline">{{ $listing->title }}</a>

                            @unless ($listing->is_published)
                                <span class="ml-2 rounded bg-sand px-1.5 py-0.5 text-xs font-medium text-ink-500">Draf</span>
                            @endunless
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->location?->name ?? '—' }}</td>
                        <td class="tabular whitespace-nowrap px-4 py-2 text-right">Rp {{ number_format($listing->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ ucfirst($listing->status) }}</td>
                        <td class="px-4 py-2 text-ink-600">{{ $listing->creator?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-1 text-right">
                            <a href="{{ route('admin.listings.edit', $listing) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Edit</a>

                            <form method="POST" action="{{ route('admin.listings.destroy', $listing) }}" class="inline" onsubmit="return confirm('Hapus listing ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-10 items-center rounded px-3 font-medium text-danger hover:bg-sand">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-ink-500">Belum ada listing.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $listings->links() }}
    </div>
@endsection
