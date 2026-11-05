<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Data agen</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="photo" value="Foto profil" />

                <div class="mt-1 flex items-center gap-4">
                    <img
                        src="{{ $agent->photoUrl() }}"
                        alt=""
                        @class([
                            'h-16 w-16 shrink-0 rounded-full border border-line',
                            'object-cover' => $agent->photo_path,
                            'bg-sand object-contain p-2' => ! $agent->photo_path,
                        ])
                    >

                    <div class="min-w-0 flex-1">
                        <x-file-input id="photo" name="photo" accept="image/jpeg,image/png,image/webp" />
                        <p class="mt-1 text-xs text-ink-500">Kosongkan bila tidak diubah. Tanpa foto, dipakai logo Avenma.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                    </div>
                </div>
            </div>

            <div>
                <x-input-label for="name" value="Nama" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $agent->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="title" value="Jabatan" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $agent->title)" placeholder="Sales Representative" />
                <p class="mt-1 text-xs text-ink-500">Tampil di bawah nama pada halaman properti.</p>
                <x-input-error class="mt-2" :messages="$errors->get('title')" />
            </div>

            <div>
                <x-input-label for="phone" value="Nomor WhatsApp" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $agent->phone)" required placeholder="0812…" />
                <p class="mt-1 text-xs text-ink-500">Dipakai tombol WhatsApp pada listing yang ia pegang. Format <span class="tabular">08…</span>.</p>
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $agent->email)" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>
    </section>

    <div class="sticky bottom-0 z-10 -mx-4 flex flex-wrap items-center gap-3 border-t border-line bg-sand px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Simpan
        </button>
        <a href="{{ route('admin.agents.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-medium text-ink-950 hover:bg-sand">
            Batal
        </a>
    </div>
</form>
