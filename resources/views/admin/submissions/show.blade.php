@extends('layouts.admin')

@section('title', 'Detail titip properti')

@section('content')
    <x-back-link :href="route('admin.submissions.index')">Titip Properti</x-back-link>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">{{ $submission->owner_name }}</h1>
            <p class="mt-1 text-sm text-ink-500">Dikirim {{ $submission->created_at->locale('id')->translatedFormat('j F Y, H:i') }}</p>
        </div>

        <span @class([
            'inline-flex rounded px-3 py-1 text-sm font-semibold',
            'bg-gold-500/20 text-ink-950' => $submission->status === 'baru',
            'bg-sand text-ink-600' => $submission->status === 'dihubungi',
            'bg-success/10 text-success' => $submission->status === 'selesai',
        ])>{{ $submission->statusLabel() }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Data properti</h2>

            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Jenis properti</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->typeLabel() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Titip</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->offerLabel() }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Alamat</dt>
                    <dd class="mt-1 text-sm leading-6 text-ink-950">{{ $submission->address }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Luas tanah</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->land_area !== null ? $submission->land_area.' m²' : '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Luas bangunan</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->building_area !== null ? $submission->building_area.' m²' : '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Kamar tidur</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->bedrooms ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Kamar mandi</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->bathrooms ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Hadap</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->facingLabel() ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Legalitas</dt>
                    <dd class="mt-1 text-sm text-ink-950">{{ $submission->certificateLabel() ?? '—' }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Harga {{ strtolower($submission->offerLabel()) }}</dt>
                    <dd class="mt-1 text-lg font-semibold text-ink-950">Rp {{ number_format($submission->price, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <a
                href="https://maps.google.com/?q={{ rawurlencode($submission->address) }}"
                target="_blank"
                rel="noopener"
                class="mt-5 inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand"
            >Buka alamat di Google Maps</a>
        </section>

        <div class="space-y-6">
            <section class="border border-line bg-white p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-ink-950">Kontak dan komisi</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Nama pemilik</dt>
                        <dd class="mt-1 text-ink-950">{{ $submission->owner_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">WhatsApp</dt>
                        <dd class="mt-1 text-ink-950">{{ $submission->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Komisi</dt>
                        <dd class="mt-1 text-ink-950">
                            {{ str_replace('.', ',', (string) $submission->commission_rate) }}%
                            &middot; Rp {{ number_format($submission->commissionAmount(), 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                @if ($submission->whatsappUrl())
                    <a
                        href="{{ $submission->whatsappUrl() }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 hover:bg-gold-700 hover:text-white"
                    >Hubungi via WhatsApp</a>
                @endif
            </section>

            <section class="border border-line bg-white p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-ink-950">Tindak lanjut</h2>

                <form method="POST" action="{{ route('admin.submissions.update', $submission) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" :options="\App\Models\PropertySubmission::STATUSES" :value="old('status', $submission->status)" aria-label="Status" class="mt-1" />
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div>
                        <x-input-label for="commission_rate" value="Komisi (%)" />
                        <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('commission_rate', $submission->commission_rate)" />
                        <x-input-error class="mt-2" :messages="$errors->get('commission_rate')" />
                    </div>

                    <x-primary-button>Simpan</x-primary-button>
                </form>

                <div class="mt-5 border-t border-line pt-5">
                    @if ($submission->listing)
                        <a href="{{ route('admin.listings.edit', $submission->listing) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">
                            Lihat listing hasil tarikan
                        </a>
                    @else
                        <a href="{{ route('admin.listings.create', ['titip' => $submission->id]) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">
                            Buat listing dari data ini
                        </a>
                        <p class="mt-2 text-xs leading-5 text-ink-500">Data terisi otomatis. Wilayah, deskripsi, dan foto masih perlu dilengkapi sebelum terbit.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
