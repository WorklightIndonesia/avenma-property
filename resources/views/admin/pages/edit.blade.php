@extends('layouts.admin')

@section('title', 'Ubah '.$page->title)

@section('content')
    @php
        // Sanitizing on the way back in keeps a rejected submit from injecting
        // markup into the editor it came from.
        $sanitize = fn (?string $html) => \App\Support\HtmlSanitizer::clean($html);
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink-950">{{ $page->title }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ config("pages.{$page->key}.path") }}</p>
        </div>

        <a href="{{ route('admin.pages.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
            Semua halaman
        </a>
    </div>

    @if (session('status'))
        <p class="mt-4 rounded border border-line bg-white px-4 py-3 text-sm text-ink-950">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="mt-4 rounded border border-danger bg-white px-4 py-3 text-sm text-danger">
            Ada isian yang perlu diperbaiki. Periksa pesan di bawah tiap bagian.
        </p>
    @endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Judul dan mesin pencari</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="title" value="Judul halaman" />
                    <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $page->title)" required />
                    <p class="mt-1 text-xs text-ink-500">Dipakai di navigasi dan judul tab browser.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-input-label for="meta_title" value="Judul untuk Google (opsional)" />
                    <x-text-input id="meta_title" name="meta_title" class="mt-1 block w-full" :value="old('meta_title', $page->meta_title)" />
                    <p class="mt-1 text-xs text-ink-500">Kosongkan untuk memakai judul halaman.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('meta_title')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="meta_description" value="Deskripsi untuk Google (opsional)" />
                    <textarea id="meta_description" name="meta_description" rows="2" data-autogrow class="mt-1 block w-full resize-none overflow-hidden rounded border-line bg-white px-3 py-2 text-sm leading-5 text-ink-950 focus:border-gold-700 focus:ring-gold-500/30">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <p class="mt-1 text-xs text-ink-500">Satu atau dua kalimat. Panjang ideal 150–160 karakter.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('meta_description')" />
                </div>
            </div>
        </section>

        @foreach ($skeleton as $key => $meta)
            @php
                $section = $sections->get($key);
                $bodyHtml = $sanitize(old("sections.$key.body", $section?->body));
                $path = "sections.$key";
                $field = "sections[$key]";
            @endphp

            <section class="border border-line bg-white p-5 sm:p-6" aria-labelledby="section-{{ $key }}">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 id="section-{{ $key }}" class="text-lg font-semibold text-ink-950">{{ $meta['label'] }}</h2>
                    <span class="text-xs text-ink-500">Urutan dan gaya tetap</span>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label :for="'heading-'.$key" value="Judul bagian" />
                        <x-text-input :id="'heading-'.$key" :name="$field.'[heading]'" class="mt-1 block w-full" :value="old($path.'.heading', $section?->heading)" />
                        <x-input-error class="mt-2" :messages="$errors->get($path.'.heading')" />
                    </div>

                    <div>
                        <x-input-label :for="'body-'.$key" value="Isi" />
                        <div
                            id="body-{{ $key }}"
                            class="mt-1 border border-line bg-white"
                            data-quill="{{ $key }}"
                            data-label="Isi {{ $meta['label'] }}"
                        >{!! $bodyHtml !!}</div>
                        <input type="hidden" id="input-{{ $key }}" name="{{ $field }}[body]" value="{{ $bodyHtml }}">
                        <p class="mt-1 text-xs text-ink-500">
                            Sub-judul, tebal, miring, daftar, dan tautan tersedia di bilah atas. Tempelan dari Word dibersihkan otomatis.
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get($path.'.body')" />
                    </div>

                    @if ($meta['image'] ?? false)
                        <div class="border-t border-line pt-4">
                            <p class="text-sm font-medium text-ink-950">Gambar</p>

                            @if ($section?->image)
                                <img src="{{ asset($section->image) }}" alt="{{ $section->image_alt }}" class="mt-2 h-40 w-full max-w-sm border border-line object-cover">
                                <label for="remove-{{ $key }}" class="mt-3 flex min-h-11 items-center gap-3 text-sm text-ink-950">
                                    <input type="checkbox" id="remove-{{ $key }}" name="{{ $field }}[remove_image]" value="1" class="rounded border-line text-gold-700 focus:ring-gold-500/30">
                                    Hapus gambar ini
                                </label>
                            @endif

                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'image-'.$key" value="{{ $section?->image ? 'Ganti gambar' : 'Unggah gambar' }}" />
                                    <input type="file" id="image-{{ $key }}" name="{{ $field }}[image]" accept="image/jpeg,image/png" class="mt-1 block w-full text-sm text-ink-950 file:mr-3 file:rounded file:border-0 file:bg-sand file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-ink-950">
                                    <p class="mt-1 text-xs text-ink-500">JPEG atau PNG, maksimal 5 MB.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get($path.'.image')" />
                                </div>

                                <div>
                                    <x-input-label :for="'alt-'.$key" value="Alt text gambar" />
                                    <x-text-input :id="'alt-'.$key" :name="$field.'[image_alt]'" class="mt-1 block w-full" :value="old($path.'.image_alt', $section?->image_alt)" />
                                    <p class="mt-1 text-xs text-ink-500">Dibacakan pembaca layar. Wajib bila ada gambar.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get($path.'.image_alt')" />
                                </div>
                            </div>
                        </div>
                    @endif

                    @php
                        $buttons = (int) ($meta['cta'] ?? 0);
                    @endphp

                    @if ($buttons > 0)
                        <div class="space-y-4 border-t border-line pt-4">
                            <p class="text-sm font-medium text-ink-950">Tombol</p>

                            @foreach (range(1, $buttons) as $slot)
                                @php
                                    $suffix = $slot === 1 ? '' : '_2';
                                    $slotName = $slot === 1 ? 'utama' : 'kedua';
                                    $labelKey = 'cta_label'.$suffix;
                                    $urlKey = 'cta_url'.$suffix;
                                    $labelValue = old($path.'.'.$labelKey, $section?->{$labelKey});
                                    $urlValue = old($path.'.'.$urlKey, $section?->{$urlKey});
                                @endphp

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label :for="'cta-label-'.$key.$suffix" :value="'Teks tombol '.$slotName.' (opsional)'" />
                                        <x-text-input :id="'cta-label-'.$key.$suffix" :name="$field.'['.$labelKey.']'" class="mt-1 block w-full" :value="$labelValue" />
                                        <x-input-error class="mt-2" :messages="$errors->get($path.'.'.$labelKey)" />
                                    </div>

                                    <div>
                                        <x-input-label :for="'cta-url-'.$key.$suffix" :value="'Tujuan tombol '.$slotName.' (opsional)'" />
                                        <x-text-input :id="'cta-url-'.$key.$suffix" :name="$field.'['.$urlKey.']'" type="text" class="mt-1 block w-full" :value="$urlValue" placeholder="/properti atau https://" />
                                        <x-input-error class="mt-2" :messages="$errors->get($path.'.'.$urlKey)" />
                                    </div>
                                </div>
                            @endforeach

                            <p class="text-xs text-ink-500">
                                Alamat di situs ini diawali /, alamat luar diawali https://. Kosongkan teks dan tujuan untuk menyembunyikan tombol.
                                @if ($buttons > 1)
                                    Tombol utama tampil sebagai tombol emas, tombol kedua bergaris.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Simpan
            </button>

            <a href="{{ url(config("pages.{$page->key}.path")) }}" target="_blank" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
                Lihat halaman
            </a>
        </div>
    </form>
@endsection

@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/quill/quill.js') }}"></script>
    <script>
        document.querySelectorAll('[data-quill]').forEach((host) => {
            const input = document.getElementById('input-' + host.dataset.quill);

            if (! window.Quill) {
                return;
            }

            const editor = new Quill(host, {
                theme: 'snow',
                placeholder: 'Tulis isi bagian ini…',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean'],
                    ],
                },
            });

            editor.root.setAttribute('aria-label', host.dataset.label);

            const sync = () => {
                input.value = editor.root.innerHTML;
            };

            editor.on('text-change', sync);
            sync();
        });
    </script>
@endpush
