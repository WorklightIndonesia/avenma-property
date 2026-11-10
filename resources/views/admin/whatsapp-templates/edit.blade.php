@extends('layouts.admin')

@section('title', 'Template WhatsApp')

@section('content')
    <x-back-link :href="route('admin.dashboard')">Dashboard</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Template WhatsApp</h1>
    <p class="mt-1 max-w-2xl text-sm text-ink-500">
        Pesan yang sudah terisi saat pengunjung menekan tombol WhatsApp. Teks dalam kurung kurawal diganti otomatis, jadi jangan dihapus selama informasinya masih dibutuhkan.
    </p>

    <form method="POST" action="{{ route('admin.whatsapp-templates.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Template sistem</h2>

            <div class="mt-4 space-y-5">
                @foreach (App\Models\WhatsAppTemplate::TEMPLATES as $key => $template)
                    <div>
                        <x-input-label :for="'template-'.$key" :value="$template['label']" />
                        <x-textarea id="template-{{ $key }}" name="templates[{{ $key }}]" rows="3" data-autogrow class="resize-none overflow-hidden leading-5">{{ old('templates.'.$key, $templates->get($key)?->message) }}</x-textarea>
                        <p class="mt-1 text-xs text-ink-500">
                            {{ $template['hint'] }}
                            @if ($template['placeholders'])
                                Placeholder: <span class="tabular">{{ implode(', ', $template['placeholders']) }}</span>.
                            @endif
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('templates.'.$key)" />
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Template per layanan</h2>
            <p class="mt-1 text-sm text-ink-500">
                Pesan tombol tiap baris di halaman <span class="tabular">/layanan</span>. Kolom yang sama juga bisa diubah dari
                <a href="{{ route('admin.services.index') }}" class="underline decoration-line underline-offset-4">Layanan</a>.
            </p>

            @if ($services->isEmpty())
                <p class="mt-4 text-sm text-ink-600">
                    Belum ada layanan. <a href="{{ route('admin.services.create') }}" class="underline decoration-line underline-offset-4">Tambah layanan</a>.
                </p>
            @else
                <div class="mt-4 space-y-5">
                    @foreach ($services as $service)
                        <div>
                            <x-input-label :for="'service-'.$service->id" :value="$service->title" />
                            <x-textarea id="service-{{ $service->id }}" name="services[{{ $service->id }}]" rows="3" data-autogrow class="resize-none overflow-hidden leading-5">{{ old('services.'.$service->id, $service->message) }}</x-textarea>
                            <p class="mt-1 text-xs text-ink-500">Kosongkan untuk memakai template Layanan umum.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('services.'.$service->id)" />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Simpan
            </button>
        </div>
    </form>
@endsection
