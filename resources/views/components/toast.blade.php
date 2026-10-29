@php
    $toasts = array_values(array_filter([
        ['tone' => 'success', 'message' => session('status'), 'duration' => 6000],
        ['tone' => 'error', 'message' => $errors->first(), 'duration' => 10000],
    ], fn (array $toast) => filled($toast['message'])));
@endphp

@if ($toasts !== [])
    <div class="pointer-events-none fixed inset-x-4 bottom-4 z-[60] flex flex-col gap-3 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-[28rem]">
        @foreach ($toasts as $toast)
            @php
                $isError = $toast['tone'] === 'error';
            @endphp

            <div
                role="{{ $isError ? 'alert' : 'status' }}"
                aria-live="{{ $isError ? 'assertive' : 'polite' }}"
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, {{ $toast['duration'] }})"
                x-show="show"
                x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="pointer-events-auto relative border border-line border-l-4 bg-white px-6 py-5 pr-14 text-base leading-6 shadow-float {{ $isError ? 'border-l-danger text-danger' : 'border-l-success text-success' }}"
            >
                {{ $toast['message'] }}

                <button
                    type="button"
                    x-on:click="show = false"
                    aria-label="Tutup pesan"
                    class="absolute right-1 top-1 inline-flex h-10 w-10 items-center justify-center rounded text-ink-500 transition-colors duration-150 hover:bg-sand hover:text-ink-950"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" /></svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
