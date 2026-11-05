<form method="POST" action="{{ $action }}" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Data developer</h2>

        <div class="mt-4 space-y-4">
            <div>
                <x-input-label for="name" value="Nama" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $developer->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="description" value="Deskripsi" />
                <x-textarea id="description" name="description" rows="4">{{ old('description', $developer->description) }}</x-textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="whatsapp" value="WhatsApp" />
                    <x-text-input id="whatsapp" name="whatsapp" type="tel" class="mt-1 block w-full" :value="old('whatsapp', $developer->whatsapp)" placeholder="0812…" />
                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $developer->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
            </div>
        </div>
    </section>

    <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Simpan
        </button>
        <a href="{{ route('admin.listing-settings.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-medium text-ink-950 hover:bg-sand">
            Batal
        </a>
    </div>
</form>
