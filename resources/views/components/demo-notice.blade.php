@if (config('demo.notice'))
    {{-- Export only; see config/demo.php. --}}
    <p class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 border border-line bg-sand px-4 py-3 text-sm leading-6 text-ink-950">
        <span class="font-semibold">Demo statis.</span>
        <span class="text-ink-600">Filter, urutan, dan pencarian tidak aktif di pratinjau ini.</span>
    </p>
@endif
