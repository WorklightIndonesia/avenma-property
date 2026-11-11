@extends('layouts.admin')

@section('title', 'Titip Properti')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Titip Properti</h1>
            <p class="mt-1 max-w-2xl text-sm text-ink-500">Pengajuan dari formulir /titip-properti. Hubungi pemilik lewat WhatsApp, lalu tarik datanya menjadi listing.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.submissions.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
        <div class="min-w-56 flex-1">
            <x-input-label for="q" value="Cari" />
            <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filters['q'] ?? ''" placeholder="Nama pemilik atau alamat" />
        </div>

        <div class="w-48">
            <x-input-label for="status" value="Status" />
            <x-select id="status" name="status" :options="\App\Models\PropertySubmission::STATUSES" :value="$filters['status'] ?? ''" placeholder="Semua status" aria-label="Status" class="mt-1" />
        </div>

        <div class="w-40">
            <x-input-label for="offer" value="Titip" />
            <x-select id="offer" name="offer" :options="\App\Models\PropertySubmission::OFFERS" :value="$filters['offer'] ?? ''" placeholder="Semua" aria-label="Jenis titip" class="mt-1" />
        </div>

        <x-primary-button>Terapkan</x-primary-button>

        @if (array_filter($filters))
            <a href="{{ route('admin.submissions.index') }}" class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium text-ink-600 hover:bg-sand">Reset</a>
        @endif
    </form>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[880px] text-sm">
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Tanggal</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Pemilik</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Properti</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Titip</span></th>
                    <th class="px-4 py-3 text-left align-top"><span class="flex min-h-10 items-center font-semibold">Harga</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Status</span></th>
                    <th class="px-4 py-3 text-center align-top"><span class="flex min-h-10 items-center justify-center font-semibold">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr class="min-h-12 border-b border-line last:border-0 hover:bg-paper">
                        <td class="whitespace-nowrap px-4 py-2 text-ink-600">{{ $submission->created_at->locale('id')->translatedFormat('j M Y') }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.submissions.show', $submission) }}" class="font-medium text-ink-950 hover:underline">{{ $submission->owner_name }}</a>
                            <span class="block text-xs text-ink-500">{{ $submission->phone }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="block font-medium text-ink-950">{{ $submission->typeLabel() }}</span>
                            <span class="block text-xs text-ink-500">{{ \Illuminate\Support\Str::limit($submission->address, 60) }}</span>
                        </td>
                        <td class="px-4 py-2 text-ink-600">{{ $submission->offerLabel() }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-ink-600">Rp {{ number_format($submission->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-center">
                            <span @class([
                                'inline-flex rounded px-2.5 py-1 text-xs font-semibold',
                                'bg-gold-500/20 text-ink-950' => $submission->status === 'baru',
                                'bg-sand text-ink-600' => $submission->status === 'dihubungi',
                                'bg-success/10 text-success' => $submission->status === 'selesai',
                            ])>{{ $submission->statusLabel() }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-1 text-center">
                            <a href="{{ route('admin.submissions.show', $submission) }}" class="inline-flex h-10 items-center rounded px-3 font-medium text-ink-950 hover:bg-sand">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2">
                            <x-empty-state title="Belum ada pengajuan." description="Pengajuan dari formulir Titip Properti akan muncul di sini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-pagination :paginator="$submissions" />
    </div>
@endsection
