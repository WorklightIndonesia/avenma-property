@php
    // Sanitizing on the way back in keeps a rejected submit from injecting
    // markup into the editor it came from.
    $content = \App\Support\HtmlSanitizer::clean(old('content', $post->content));
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Artikel</h2>

        <div class="mt-4 space-y-4">
            <div>
                <x-input-label for="title" value="Judul" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $post->title)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('title')" />
            </div>

            <div>
                <x-input-label for="excerpt" value="Ringkasan (opsional)" />
                <x-textarea id="excerpt" name="excerpt" rows="3" data-autogrow class="resize-none overflow-hidden leading-6">{{ old('excerpt', $post->excerpt) }}</x-textarea>
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
                    Sub-judul, tebal, miring, daftar, dan tautan tersedia di bilah atas. Tempelan dari Word dibersihkan otomatis.
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
            <x-input-label for="cover" value="{{ $post->cover_image ? 'Ganti gambar' : 'Unggah gambar' }}" />
            <x-file-input id="cover" name="cover" accept="image/jpeg,image/png,image/webp" />
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

    <div class="flex flex-wrap items-center gap-3">
        <x-primary-button>{{ $post->exists ? 'Simpan perubahan' : 'Simpan artikel' }}</x-primary-button>
        <a href="{{ route('admin.blog.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-semibold text-ink-950 hover:bg-sand">Batal</a>
    </div>
</form>
