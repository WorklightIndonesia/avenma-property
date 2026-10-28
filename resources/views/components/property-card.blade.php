@props(['listing'])

@php
    $type = ['primary' => 'Primer', 'secondary' => 'Sekunder'][$listing->listing_type] ?? $listing->listing_type;

    $badge = [
        'dijual' => 'border border-ink-950 bg-paper text-ink-950',
        'disewa' => 'bg-gold-700 text-white',
        'baru' => 'bg-gold-300 text-ink-950',
    ][$listing->status] ?? 'border border-ink-950 bg-paper text-ink-950';

    $specs = collect([
        $listing->bedrooms ? $listing->bedrooms.' KT' : null,
        $listing->bathrooms ? $listing->bathrooms.' KM' : null,
        $listing->carport ? $listing->carport.' carport' : null,
        $listing->land_area ? 'LT '.$listing->land_area.' m²' : null,
    ])->filter()->implode(' · ');
@endphp

<article class="relative border border-line bg-white transition duration-150 hover:-translate-y-1 hover:border-ink-950 motion-reduce:transition-none motion-reduce:hover:translate-y-0">
    {{-- shortcut: foto properti belum ada, ganti blok sand dengan gambar listing saat file tersedia (Task 14) --}}
    <div class="relative flex aspect-[4/3] items-center justify-center bg-sand">
        <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
        <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($listing->status) }}</span>
    </div>

    <div class="space-y-3 p-4">
        <p class="text-xs">
            <span class="font-semibold text-ink-950">{{ $type }}</span>
            @if ($listing->developer)
                <span class="text-ink-500">· {{ $listing->developer->name }}</span>
            @endif
        </p>

        <h3 class="text-lg font-semibold leading-snug">
            <a href="{{ route('public.listings.show', $listing) }}" class="after:absolute after:inset-0">{{ $listing->title }}</a>
        </h3>

        @if ($listing->location)
            <p class="text-sm text-ink-500">{{ $listing->location->name }}</p>
        @endif

        <p class="tabular text-lg font-semibold">Rp {{ number_format($listing->price, 0, ',', '.') }}</p>

        @if ($specs !== '')
            <p class="text-sm text-ink-600">{{ $specs }}</p>
        @endif
    </div>
</article>
