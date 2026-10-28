@extends('layouts.admin')

@section('title', 'Pengaturan situs')

@section('content')
    <h1 class="text-xl font-semibold text-ink-950">Pengaturan situs</h1>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Kontak</h2>

            <div class="mt-4 max-w-sm">
                <x-input-label for="whatsapp" value="Nomor WhatsApp kantor" />
                <x-text-input id="whatsapp" name="whatsapp" type="tel" class="mt-1 block w-full tabular" :value="old('whatsapp', $setting->whatsapp)" required autofocus />
                <p class="mt-1 text-xs text-ink-500">
                    Format <span class="tabular">08…</span>. Dipakai tombol WhatsApp pada listing yang belum punya nomor sendiri.
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Simpan
            </button>
        </div>
    </form>
@endsection
