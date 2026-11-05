@extends('layouts.admin')

@section('title', 'Riwayat')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">Riwayat</h1>
            <p class="mt-1 text-sm text-ink-500">Perubahan konten, akun, dan percobaan masuk. Hanya bisa dibaca.</p>
        </div>

        <div class="flex items-end gap-2">
            @if ($event !== '')
                <a href="{{ route('admin.audit.index') }}" class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium text-ink-600 transition-colors duration-150 hover:bg-sand hover:text-ink-950">Reset filter</a>
            @endif

            <form method="GET" action="{{ route('admin.audit.index') }}" class="flex items-end gap-2">
                <label for="event" class="sr-only">Jenis aksi</label>
                <x-select
                    id="event"
                    name="event"
                    :options="collect($events)->mapWithKeys(fn ($option) => [$option => ucfirst(str_replace('_', ' ', $option))])->all()"
                    :value="$event"
                    placeholder="Semua aksi"
                    aria-label="Jenis aksi"
                    class="w-48"
                />
                <x-primary-button>Terapkan</x-primary-button>
            </form>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto border border-line bg-white">
        <table class="w-full min-w-[860px] text-sm">
            <thead class="bg-sand text-sm font-semibold text-ink-600">
                <tr>
                    <th class="px-4 py-3 text-left"><span class="flex min-h-10 items-center">Waktu</span></th>
                    <th class="px-4 py-3 text-left"><span class="flex min-h-10 items-center">Pengguna</span></th>
                    <th class="px-4 py-3 text-left"><span class="flex min-h-10 items-center">Aksi</span></th>
                    <th class="px-4 py-3 text-left"><span class="flex min-h-10 items-center">Objek</span></th>
                    <th class="px-4 py-3 text-left"><span class="flex min-h-10 items-center">Perubahan</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-line align-top last:border-0 hover:bg-paper">
                        <td class="whitespace-nowrap px-4 py-3 tabular text-ink-600">
                            {{ $log->created_at?->locale('id')->translatedFormat('j M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 font-medium text-ink-950">{{ $log->event }}</td>
                        <td class="px-4 py-3 text-ink-600">
                            {{ class_basename($log->auditable_type ?? '') }}
                            @if ($log->auditable_id)
                                #{{ $log->auditable_id }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-ink-600">
                            @if ($log->changes)
                                <details>
                                    <summary class="cursor-pointer text-xs font-semibold text-ink-500">Lihat perubahan</summary>
                                    <pre class="mt-2 max-h-48 overflow-auto whitespace-pre-wrap break-words text-xs">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2">
                            <x-empty-state title="Belum ada riwayat." description="Perubahan yang dilakukan dari CMS akan muncul di sini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-pagination :paginator="$logs" />
    </div>
@endsection
