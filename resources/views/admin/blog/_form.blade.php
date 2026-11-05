@php
    // Sanitizing on the way back in keeps a rejected submit from injecting
    // markup into the editor it came from.
    $content = \App\Support\HtmlSanitizer::clean(old('content', $post->content));
    $previewDate = ($post->published_at ?? now())->locale('id')->translatedFormat('j F Y');
    $previewAuthor = $post->creator?->name ?? auth()->user()->name;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    class="mt-6 space-y-6"
    x-data="{
        preview: false,
        title: @js(old('title', $post->title) ?? ''),
        excerpt: @js(old('excerpt', $post->excerpt) ?? ''),
        content: @js($content),
        cover: @js($post->cover_image ? asset($post->cover_image) : null),
        coverPreview: null,
        removeCover: false,
        get shownCover() {
            return this.removeCover ? null : (this.coverPreview || this.cover);
        },
        showPreview() {
            this.removeCover = document.getElementById('remove_cover')?.checked ?? false;
            this.preview = true;
            this.$nextTick(() => this.$refs.preview?.scrollIntoView({ block: 'start' }));
        },
        pickCover(event) {
            const file = event.target.files[0];
            this.coverPreview = null;
            if (! file) {
                return;
            }
            // A data URL, because the page CSP blocks blob: images.
            const reader = new FileReader();
            reader.onload = () => {
                this.coverPreview = reader.result;
            };
            reader.readAsDataURL(file);
        },
    }"
    x-on:quill-change="content = $event.detail"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Artikel</h2>

        <div class="mt-4 space-y-4">
            <div>
                <x-input-label for="title" value="Judul" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" x-model="title" :value="old('title', $post->title)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('title')" />
            </div>

            <div>
                <x-input-label for="excerpt" value="Ringkasan (opsional)" />
                <x-textarea id="excerpt" name="excerpt" rows="3" data-autogrow x-model="excerpt" class="resize-none overflow-hidden leading-6">{{ old('excerpt', $post->excerpt) }}</x-textarea>
                <p class="mt-1 text-xs text-ink-500">Tampil di daftar artikel dan di bawah judul halaman baca.</p>
                <x-input-error class="mt-2" :messages="$errors->get('excerpt')" />
            </div>

            <div>
                <x-input-label for="content" value="Isi artikel" />
                <div
                    id="content"
                    class="mt-1 border border-line bg-white"
                    data-quill="content"
                    data-label="Isi artikel"
                ></div>
                <input type="hidden" id="input-content" name="content" value="{{ $content }}">
                <p class="mt-1 text-xs text-ink-500">
                    Sub-judul, tebal, miring, daftar, dan link tersedia di bilah atas. Tempelan dari Word dibersihkan otomatis.
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('content')" />
            </div>
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Gambar sampul</h2>

        @if ($post->cover_image)
            <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="mt-3 aspect-[16/9] w-full max-w-md border border-line object-cover">
            <x-checkbox id="remove_cover" name="remove_cover" class="mt-3">
                Hapus gambar ini
            </x-checkbox>
        @endif

        <div class="mt-3">
            <x-input-label for="cover" value="{{ $post->cover_image ? 'Ganti gambar' : 'Upload gambar' }}" />
            <x-file-input id="cover" name="cover" accept="image/jpeg,image/png,image/webp" x-on:change="pickCover($event)" />
            <p class="mt-1 text-xs text-ink-500">JPG, PNG, atau WebP. Maksimal 5 MB. Tanpa sampul, daftar artikel memakai logo Avenma.</p>
            <x-input-error class="mt-2" :messages="$errors->get('cover')" />
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Publikasi</h2>

        <x-checkbox id="is_published" name="is_published" :checked="old('is_published', $post->is_published ?? true)" class="mt-4">
            Terbitkan di website
        </x-checkbox>
        <p class="text-xs text-ink-500">
            @if ($post->published_at)
                Terbit sejak {{ $post->published_at->locale('id')->translatedFormat('j F Y') }}.
            @else
                Tanggal terbit diisi otomatis saat artikel pertama kali diterbitkan.
            @endif
        </p>
    </section>

    {{-- Mirrors the public article, built from the unsaved form state. --}}
    <section x-show="preview" x-cloak x-ref="preview" class="mt-6 scroll-mt-24 border border-line bg-paper">
        <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
            <p class="text-sm font-semibold text-ink-950">Pratinjau artikel</p>
            <button type="button" x-on:click="preview = false" class="inline-flex min-h-10 items-center rounded border border-line px-3 text-sm font-semibold text-ink-950 hover:bg-sand">Sembunyikan</button>
        </div>

        <article class="mx-auto max-w-3xl px-5 py-8 sm:px-8">
            <h2 id="preview-article-title" class="text-3xl font-semibold leading-tight tracking-tight text-ink-950" x-text="title.trim() !== '' ? title : 'Judul artikel'"></h2>

            <p class="mt-3 text-sm text-ink-500">{{ $previewDate }} · {{ $previewAuthor }}</p>

            <template x-if="shownCover">
                <img :src="shownCover" alt="{{ $post->title }}" :alt="title" class="mt-6 aspect-[16/9] w-full border border-line object-cover">
            </template>

            <template x-if="excerpt.trim() !== ''">
                <p class="mt-6 border-l border-line pl-4 text-lg leading-snug text-ink-600" x-text="excerpt"></p>
            </template>

            <div class="rich-text mt-6" x-html="content"></div>
        </article>
    </section>

    <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <x-primary-button>{{ $post->exists ? 'Simpan perubahan' : 'Simpan artikel' }}</x-primary-button>
        <button type="button" x-on:click="showPreview()" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Pratinjau</button>
        <a href="{{ route('admin.blog.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Batal</a>
    </div>
</form>
