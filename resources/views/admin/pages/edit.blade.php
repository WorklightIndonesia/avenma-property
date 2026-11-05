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
            <x-back-link :href="route('admin.pages.index')">Halaman</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">{{ $page->title }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ config("pages.{$page->key}.path") }}</p>
        </div>

        <a href="{{ route('admin.pages.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
            Semua halaman
        </a>
    </div>

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
                    <x-textarea id="meta_description" name="meta_description" rows="2" data-autogrow class="resize-none overflow-hidden leading-5">{{ old('meta_description', $page->meta_description) }}</x-textarea>
                    <p class="mt-1 text-xs text-ink-500">Satu atau dua kalimat. Panjang ideal 150–160 karakter.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('meta_description')" />
                </div>
            </div>
        </section>

        @if ($galleries)
            <section class="border border-line bg-white p-5 sm:p-6" aria-labelledby="gallery-heading">
                <h2 id="gallery-heading" class="text-lg font-semibold text-ink-950">Galeri beranda</h2>
                <p class="mt-1 text-sm text-ink-500">
                    Centang foto properti yang bergilir di hero, maksimal {{ $galleries['max'] }} foto.
                    Satu properti tampil per halaman. Tanpa centang, hero memakai foto properti terbaru.
                </p>

                <x-input-error class="mt-2" :messages="array_merge($errors->get('gallery'), $errors->get('gallery.*'))" />

                <div class="mt-4" data-gallery-picker x-data="galleryPicker({{ $galleries['max'] }})" x-on:change="sync($event)">
                    <x-input-label for="gallery-search" value="Cari foto properti" />
                    <x-text-input
                        id="gallery-search"
                        type="search"
                        name="q"
                        form="gallery-search-form"
                        :value="$galleries['search']"
                        class="mt-1 block w-full sm:max-w-sm"
                        placeholder="Judul properti atau teks alternatif foto"
                    />
                    <input type="hidden" name="pilih" form="gallery-search-form" value="{{ implode(',', $galleries['picked']) }}" data-picks-field>

                    <div class="mt-3" data-picked-list>
                        <p class="text-xs font-semibold text-ink-600">Foto yang dipakai sekarang</p>
                        <p class="mt-1 text-xs text-ink-500" data-picked-empty @if ($galleries['picked'] !== []) hidden @endif>
                            Belum ada foto terpilih, jadi hero memakai foto properti terbaru.
                        </p>

                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($galleries['chips'] as $chip)
                                <li
                                    data-picked-id="{{ $chip['id'] }}"
                                    @if (! in_array($chip['id'], $galleries['picked'], true)) hidden @endif
                                    class="flex items-center gap-2 border border-line bg-sand p-1 pr-2"
                                >
                                    <img src="{{ asset($chip['src']) }}" alt="" class="h-10 w-14 border border-line object-cover">
                                    <span class="max-w-[9rem] text-xs">
                                        <span class="block truncate font-medium text-ink-950">{{ $chip['title'] }}</span>
                                        <span class="block truncate text-ink-500">{{ $chip['label'] }}</span>
                                    </span>
                                    <button
                                        type="button"
                                        aria-label="Lepas foto {{ $chip['label'] }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded text-ink-500 hover:bg-white hover:text-danger"
                                        x-on:click="unpick($event, '{{ $chip['id'] }}')"
                                    >&times;</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="mt-2 text-sm">
                        <span class="font-semibold" data-count>{{ count($galleries['picked']) }}</span> dari {{ $galleries['max'] }} foto dipilih.
                    </p>

                    <p
                        data-limit-warning
                        role="status"
                        aria-live="polite"
                        class="mt-2 border-l-4 border-gold-700 bg-sand px-4 py-2 text-sm text-ink-950"
                        hidden
                    >
                        Galeri beranda maksimal {{ $galleries['max'] }} foto. Lepas salah satu dulu sebelum menambah.
                    </p>

                    {{-- Picks made on other pages; hidden fields so this save keeps them. --}}
                    @foreach ($galleries['others'] as $id)
                        <input type="hidden" name="gallery[]" value="{{ $id }}">
                    @endforeach

                    @if ($galleries['listing'])
                        <div class="mt-4 flex flex-wrap items-baseline justify-between gap-2 border-t border-line pt-4">
                            <p class="text-sm font-semibold text-ink-950">{{ $galleries['listing']->title }}</p>
                            <p class="text-xs text-ink-500">
                                Properti {{ $galleries['paginator']->currentPage() }} dari {{ $galleries['paginator']->total() }}
                            </p>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($galleries['listing']->images as $image)
                                <x-checkbox
                                    name="gallery[]"
                                    :value="$image->id"
                                    :checked="in_array($image->id, $galleries['picked'])"
                                    class="min-h-11 cursor-pointer rounded border border-line p-2 hover:bg-sand"
                                >
                                    <img src="{{ asset($image->path) }}" alt="" class="h-14 w-20 shrink-0 border border-line object-cover">
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-ink-950">{{ $image->label ?: 'Tanpa label' }}</span>
                                        <span class="block truncate text-xs text-ink-500">{{ $image->alt ?: 'Tanpa teks alternatif' }}</span>
                                    </span>
                                </x-checkbox>
                            @endforeach
                        </div>

                        <div class="mt-4" data-pager>
                            <x-pagination :paginator="$galleries['paginator']" arrows />
                        </div>
                    @else
                        @if ($galleries['paginator']->total() > 0)
                            <p class="mt-4 text-sm text-ink-500">
                                Halaman {{ $galleries['paginator']->currentPage() }} tidak ada.
                                <a href="{{ $galleries['paginator']->url(1) }}" class="font-semibold text-gold-700 hover:underline">Kembali ke halaman pertama</a>.
                            </p>
                        @else
                            <p class="mt-4 text-sm text-ink-500">
                                @if ($galleries['search'] !== '')
                                    Tidak ada properti yang cocok dengan &ldquo;{{ $galleries['search'] }}&rdquo;.
                                @else
                                    Belum ada foto properti. Upload foto di menu Properti lebih dulu.
                                @endif
                            </p>
                        @endif
                    @endif
                </div>
            </section>
        @endif

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
                        ></div>
                        <input type="hidden" id="input-{{ $key }}" name="{{ $field }}[body]" value="{{ $bodyHtml }}">
                        <p class="mt-1 text-xs text-ink-500">
                            Sub-judul, tebal, miring, daftar, dan link tersedia di bilah atas. Tempelan dari Word dibersihkan otomatis.
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get($path.'.body')" />
                    </div>

                    @if ($meta['image'] ?? false)
                        <div class="border-t border-line pt-4">
                            <p class="text-sm font-medium text-ink-950">Gambar</p>

                            @if ($section?->image)
                                <img src="{{ asset($section->image) }}" alt="{{ $section->image_alt }}" class="mt-2 h-40 w-full max-w-sm border border-line object-cover">
                                <x-checkbox :id="'remove-'.$key" :name="$field.'[remove_image]'" class="mt-3">
                                    Hapus gambar ini
                                </x-checkbox>
                            @endif

                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'image-'.$key" value="{{ $section?->image ? 'Ganti gambar' : 'Upload gambar' }}" />
                                    <x-file-input id="image-{{ $key }}" name="{{ $field }}[image]" accept="image/jpeg,image/png,image/webp" />
                                    <p class="mt-1 text-xs text-ink-500">JPEG, PNG, atau WebP, maksimal 5 MB. Disimpan sebagai WebP, lebar maksimal 1920 px.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get($path.'.image')" />
                                </div>

                                <div>
                                    <x-input-label :for="'alt-'.$key" value="Alt text gambar" />
                                    <x-text-input :id="'alt-'.$key" :name="$field.'[image_alt]'" class="mt-1 block w-full" :value="old($path.'.image_alt', $section?->image_alt)" />
                                    <p class="mt-1 text-xs text-ink-500">Dibacakan screen reader bagi pengunjung yang tidak melihat gambar. Wajib bila ada gambar.</p>
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

                                    @if ($meta['cta_url'] ?? true)
                                        <div>
                                            <x-input-label :for="'cta-url-'.$key.$suffix" :value="'Tujuan tombol '.$slotName.' (opsional)'" />
                                            <x-text-input :id="'cta-url-'.$key.$suffix" :name="$field.'['.$urlKey.']'" type="text" class="mt-1 block w-full" :value="$urlValue" placeholder="Contoh: /properti" />
                                            <x-input-error class="mt-2" :messages="$errors->get($path.'.'.$urlKey)" />
                                        </div>
                                    @endif
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

        <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                Simpan
            </button>

            <a href="{{ url(config("pages.{$page->key}.path")) }}" target="_blank" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
                Lihat halaman
            </a>
        </div>
    </form>

    @if ($galleries)
        {{-- The picker lives inside the page form, and forms cannot nest, so its
             search is a GET form of its own that the fields above refer to. --}}
        <form id="gallery-search-form" method="GET" action="{{ route('admin.pages.edit', $page) }}"></form>
    @endif
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

            // Quill renders icon-only buttons without labels; give each a tooltip.
            // The toolbar sits beside the editor, so read it from the module.
            const toolbar = editor.getModule('toolbar').container;
            const tooltips = { bold: 'Tebal', italic: 'Miring', underline: 'Garis bawah', blockquote: 'Kutipan', link: 'Link', clean: 'Hapus format' };

            toolbar.querySelectorAll('button').forEach((button) => {
                const name = [...button.classList].find((cls) => cls.startsWith('ql-'))?.slice(3);

                button.title = name === 'list'
                    ? (button.getAttribute('value') === 'ordered' ? 'Daftar bernomor' : 'Daftar poin')
                    : (tooltips[name] ?? button.title);
            });

            const headerPicker = toolbar.querySelector('.ql-header');

            if (headerPicker) {
                headerPicker.title = 'Judul bagian';
            }

            // Seeded from the hidden field so posted HTML is parsed by Quill
            // instead of being written into the page as-is.
            if (input.value.trim() !== '') {
                editor.clipboard.dangerouslyPasteHTML(input.value, 'silent');
            }

            const sync = () => {
                input.value = editor.root.innerHTML;
            };

            editor.on('text-change', sync);
            sync();
        });
    </script>
@endpush
