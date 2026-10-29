<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-ink-950">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-ink-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" max-width="md" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-ink-950">
                Hapus akun?
            </h2>

            <p class="mt-2 text-sm text-ink-600">
                Akun ini akan dihapus permanen. Masukkan password untuk mengonfirmasi. Tindakan ini tidak bisa dibatalkan.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
