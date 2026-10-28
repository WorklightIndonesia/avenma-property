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
                <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30">{{ old('description', $developer->description) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>
        </div>
    </section>

    <div class="flex items-center gap-3">
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Simpan
        </button>
        <a href="{{ route('admin.developers.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-medium text-ink-950 hover:bg-sand">
            Batal
        </a>
    </div>
</form>
