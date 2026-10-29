@php
    $check = 'rounded border-line text-gold-700 focus:ring-gold-500/30';

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
                <textarea id="excerpt" name="excerpt" rows="3" data-autogrow class="mt-1 block w-full resize-none overflow-hidden rounded border-line bg-white px-3 py-2 text-sm leading-6 text-ink-950 focus:border-gold-700 focus:ring-gold-500/30">{{ old('excerpt', $post->excerpt) }}</textarea>
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
                >{!! $content !!}</div>
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
            <label for="remove_cover" class="mt-3 flex min-h-11 items-center gap-3 text-sm text-ink-950">
                <input type="checkbox" id="remove_cover" name="remove_cover" value="1" class="{{ $check }}">
                Hapus gambar ini
            </label>
        @endif

        <div class="mt-3">
            <x-input-label for="cover" value="{{ $post->cover_image ? 'Ganti gambar' : 'Unggah gambar' }}" />
            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-ink-600 file:mr-3 file:rounded file:border-0 file:bg-sand file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-ink-950 hover:file:bg-line">
            <p class="mt-1 text-xs text-ink-500">JPG, PNG, atau WebP. Maksimal 5 MB. Tanpa sampul, daftar artikel memakai blok lambang.</p>
            <x-input-error class="mt-2" :messages="$errors->get('cover')" />
        </div>
    </section>

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Publikasi</h2>

        <label for="is_published" class="mt-4 flex min-h-11 items-center gap-3 text-sm font-medium text-ink-950">
            <input id="is_published" name="is_published" type="checkbox" value="1" class="{{ $check }}" @checked(old('is_published', $post->is_published ?? true))>
            Terbitkan di website
        </label>
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
