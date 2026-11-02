@props(['rooms', 'caption' => null])

@php
    $cols = \App\Models\ListingRoom::COLS;
    $rows = \App\Models\ListingRoom::ROWS;
@endphp

<figure {{ $attributes->merge(['class' => 'm-0']) }}>
    <div
        class="relative aspect-[3/4] w-full border-2 border-ink-950 bg-white"
        role="img"
        aria-label="Denah ruangan: {{ collect($rooms)->pluck('label')->implode(', ') }}"
    >
        @foreach ($rooms as $room)
            @php
                $tone = match ($room->tone) {
                    'utama' => 'bg-gold-300/40',
                    'luar' => 'border-dashed border-ink-500/60 bg-sand',
                    default => 'bg-white',
                };
            @endphp

            <div
                class="absolute flex items-start overflow-hidden border-2 border-ink-950 {{ $tone }}"
                style="left: {{ round($room->x / $cols * 100, 4) }}%; top: {{ round($room->y / $rows * 100, 4) }}%; width: {{ round($room->w / $cols * 100, 4) }}%; height: {{ round($room->h / $rows * 100, 4) }}%;"
            >
                <span class="p-1.5 text-[11px] leading-tight text-ink-950">{{ $room->label }}</span>
            </div>
        @endforeach
    </div>

    @if ($caption)
        <figcaption class="mt-2 text-xs text-ink-500">{{ $caption }}</figcaption>
    @endif
</figure>
