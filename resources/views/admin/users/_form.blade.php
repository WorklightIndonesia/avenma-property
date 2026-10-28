@php
    $select = 'mt-1 block w-full rounded border-line bg-white py-2 text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500/30';
@endphp

<form method="POST" action="{{ $action }}" class="mt-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="border border-line bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-ink-950">Data akun</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="name" value="Nama" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="phone" value="Nomor telepon" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" />
                <p class="mt-1 text-xs text-ink-500">Wajib untuk agen; dipakai sebagai nomor WhatsApp listing yang ia buat. Format <span class="tabular">08…</span>.</p>
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="{{ $select }}">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role ?? App\Models\User::ROLE_AGEN) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$user->exists === false" />
                <p class="mt-1 text-xs text-ink-500">
                    {{ $user->exists ? 'Kosongkan bila tidak diubah.' : 'Minimal 8 karakter, ada huruf besar, kecil, angka, dan simbol.' }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$user->exists === false" />
            </div>
        </div>
    </section>

    <div class="flex items-center gap-3">
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-gold-500 px-5 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
            Simpan
        </button>
        <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-11 items-center rounded border border-line px-5 text-sm font-medium text-ink-950 hover:bg-sand">
            Batal
        </a>
    </div>
</form>
