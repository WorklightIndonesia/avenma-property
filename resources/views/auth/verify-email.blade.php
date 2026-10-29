<x-guest-layout>
    <div class="mb-4 text-sm text-ink-600">
        Verifikasi email Anda lewat tautan yang kami kirim. Belum menerima emailnya? Kami kirim ulang.
    </div>

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Kirim ulang email verifikasi
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-ink-600 hover:text-ink-950 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-700">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
