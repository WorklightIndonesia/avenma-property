@props(['title'])

<div class="border-t border-line" x-data="{ g: false }">
    <button
        type="button"
        x-on:click="g = ! g"
        x-bind:aria-expanded="g"
        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-ink-950 hover:bg-sand"
    >
        {{ $title }}

        {{-- A plus rotated 45° reads as a close cross. --}}
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0 text-ink-500 transition-transform duration-300 ease-out motion-reduce:transition-none" x-bind:class="g ? 'rotate-45' : ''">
            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
        </svg>
    </button>

    {{-- 0fr → 1fr animates the panel height without a fixed value. --}}
    <div
        class="grid transition-[grid-template-rows,opacity] duration-300 ease-out motion-reduce:transition-none"
        x-bind:class="g ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
    >
        <div class="overflow-hidden" x-bind:inert="! g">
            <div class="grid gap-4 px-4 pb-5 sm:grid-cols-2 lg:grid-cols-1">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
