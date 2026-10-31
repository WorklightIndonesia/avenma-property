@extends('layouts.admin')

@section('title', 'Kontak Avenma')

@section('content')
    <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Kontak Avenma</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Kontak</h2>
            <p class="mt-1 text-sm text-ink-500">Data ini tampil di footer dan halaman Kontak. Kosongkan yang belum ada; barisnya disembunyikan.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="whatsapp" value="Nomor WhatsApp kantor" />
                    <x-text-input id="whatsapp" name="whatsapp" type="tel" class="mt-1 block w-full tabular" :value="old('whatsapp', $setting->whatsapp)" required autofocus />
                    <p class="mt-1 text-xs text-ink-500">
                        Format <span class="tabular">08…</span>. Dipakai tombol WhatsApp pada listing yang belum punya nomor sendiri.
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $setting->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="address" value="Alamat kantor" />
                    <textarea id="address" name="address" rows="3" data-autogrow class="mt-1 block w-full resize-none overflow-hidden rounded border-line bg-white px-3 py-2 text-sm leading-5 text-ink-950 focus:border-gold-700 focus:ring-gold-500/30">{{ old('address', $setting->address) }}</textarea>
                    <p class="mt-1 text-xs text-ink-500">Tampil publik dan dipakai sebagai titik pencarian peta di halaman Kontak.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>

                <div>
                    <x-input-label for="instagram" value="Instagram" />
                    <x-text-input id="instagram" name="instagram" class="mt-1 block w-full" :value="old('instagram', $setting->instagram)" placeholder="avenma.properti" />
                    <p class="mt-1 text-xs text-ink-500">Nama pengguna saja. Tanda @ dan alamat lengkap juga diterima.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('instagram')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="maps_url" value="Tautan Google Maps" />
                    <x-text-input id="maps_url" name="maps_url" type="url" class="mt-1 block w-full" :value="old('maps_url', $setting->maps_url)" placeholder="https://maps.app.goo.gl/..." />
                    <p class="mt-1 text-xs text-ink-500">Buka tempat kantor di Google Maps, lalu Bagikan → Salin tautan. Dipakai ikon peta di footer dan tombol “Buka di Google Maps”; titik petanya diambil dari tautan ini.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('maps_url')" />
                </div>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Simpan
            </button>
        </div>
    </form>
@endsection
