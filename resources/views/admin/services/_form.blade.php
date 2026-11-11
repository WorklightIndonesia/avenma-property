<form method="POST" action="{{ $action }}" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Isi layanan</h2>

        <div class="mt-4 space-y-4">
            <div>
                <x-input-label for="category" value="Kategori" />
                <x-select name="category" class="mt-1" :options="\App\Models\Service::CATEGORIES" :value="old('category', $service->category ?: 'properti')" />
                <p class="mt-1 text-xs text-ink-500">Menentukan section di halaman /layanan.</p>
                <x-input-error class="mt-2" :messages="$errors->get('category')" />
            </div>

            <div>
                <x-input-label for="title" value="Nama layanan" />
                <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $service->title)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('title')" />
            </div>

            <div>
                <x-input-label for="tags" value="Tag" />
                <x-text-input id="tags" name="tags" class="mt-1 block w-full" :value="old('tags', $service->tags)" placeholder="Rumah, Ruko, Apartemen" />
                <p class="mt-1 text-xs text-ink-500">Dipisah koma. Tampil sebagai satu baris kecil di bawah nama layanan.</p>
                <x-input-error class="mt-2" :messages="$errors->get('tags')" />
            </div>

            <div>
                <x-input-label for="description" value="Penjelasan" />
                <x-textarea id="description" name="description" rows="4" required>{{ old('description', $service->description) }}</x-textarea>
                <p class="mt-1 text-xs text-ink-500">Satu paragraf. Tampil saat baris layanan dibuka.</p>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <div>
                <x-input-label for="cta_label" value="Teks tombol" />
                <x-text-input id="cta_label" name="cta_label" class="mt-1 block w-full" :value="old('cta_label', $service->cta_label)" placeholder="Konsultasi" />
                <p class="mt-1 text-xs text-ink-500">Kosongkan untuk memakai &ldquo;Diskusikan via WhatsApp&rdquo;.</p>
                <x-input-error class="mt-2" :messages="$errors->get('cta_label')" />
            </div>

            <div>
                <x-input-label for="message" value="Pesan WhatsApp" />
                <x-textarea id="message" name="message" rows="4">{{ old('message', $service->message) }}</x-textarea>
                <p class="mt-1 text-xs text-ink-500">
                    Pesan yang sudah terisi saat pengunjung menekan tombol. Teks dalam kurung siku adalah contoh yang diisi pengunjung.
                    Kosongkan untuk memakai pesan umum.
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('message')" />
            </div>
        </div>
    </section>

    <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Simpan
        </button>
        <a href="{{ route('admin.services.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-medium text-ink-950 hover:bg-sand">
            Batal
        </a>
    </div>
</form>
