@extends('layouts.public')

@section('meta')
    <x-meta
        :title="$listing->title"
        :description="Str::limit(strip_tags($listing->description), 155)"
        :image="$listing->images->first() ? asset($listing->images->first()->path) : null"
    />
@endsection

@php
    $type = \App\Models\ListingOption::options('listing_type')[$listing->listing_type] ?? $listing->listing_type;
    $badge = [
        'dijual' => 'bg-gold-300 text-ink-950',
        'disewa' => 'bg-gold-700 text-white',
    ][$listing->status] ?? 'bg-gold-300 text-ink-950';

    // Apartments have no land; a unit's size is its building area.
    $isHouse = $listing->hasFloors();
    $isLand = $listing->isLand();
    $isApartment = $listing->isApartment();

    $stats = [];

    if (! $isApartment && $listing->land_area) {
        $stats[] = ['value' => $listing->land_area, 'label' => 'Luas tanah m²'];
    }

    if (! $isLand && $listing->building_area) {
        $stats[] = ['value' => $listing->building_area, 'label' => ($isApartment ? 'Luas unit' : 'Luas bangunan').' m²'];
    }

    if ($listing->bedrooms && $listing->bathrooms) {
        $stats[] = ['value' => $listing->bedrooms.'+'.$listing->bathrooms, 'label' => 'Kamar tidur + mandi'];
    } else {
        if ($listing->bedrooms) {
            $stats[] = ['value' => $listing->bedrooms, 'label' => 'Kamar tidur'];
        }
        if ($listing->bathrooms) {
            $stats[] = ['value' => $listing->bathrooms, 'label' => 'Kamar mandi'];
        }
    }

    if ($isHouse && $listing->floors) {
        $stats[] = ['value' => $listing->floors, 'label' => 'Lantai'];
    }

    if ($listing->carport) {
        $stats[] = ['value' => $listing->carport, 'label' => 'Carport'];
    }

    // A tile rather than a footnote: the certificate is the first thing a
    // buyer checks, so it carries the same weight as the other specs.
    if ($listing->certificate) {
        $stats[] = ['value' => $listing->certificate, 'label' => 'Kelengkapan surat'];
    }

    $cover = $listing->images->first();
    $photoAlt = $listing->title.($listing->location ? ' di '.$listing->location->name : '');
    $gallery = $listing->images->map(fn ($image) => [
        'src' => asset($image->path),
        'thumb' => asset($image->thumb_path),
        'medium' => asset($image->medium_path),
        'alt' => $image->alt ?: $photoAlt,
        'label' => $image->label,
    ])->values();

    $mapSrc = $listing->mapEmbedSrc();
    $price = 'Rp '.number_format($listing->price, 0, ',', '.');

    // Headline figure: the full number is too long at display size.
    $shortPrice = $listing->price >= 1_000_000_000
        ? 'Rp '.rtrim(rtrim(number_format($listing->price / 1_000_000_000, 2, ',', '.'), '0'), ',').' M'
        : ($listing->price >= 1_000_000
            ? 'Rp '.number_format($listing->price / 1_000_000, 0, ',', '.').' jt'
            : $price);

    $rupiah = fn (float $amount) => 'Rp '.number_format($amount, 0, ',', '.');
    $rupiahShort = fn (float $amount) => $amount >= 1_000_000_000
        ? 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 2, ',', '.'), '0'), ',').' M'
        : 'Rp '.number_format(round($amount / 1_000_000), 0, ',', '.').' jt';

    $eyebrow = collect([$listing->developer?->name, $listing->location?->name, $type, $listing->typeLabel()])->filter()->implode(' · ');

    // The breadcrumb's location level opens the catalog already filtered to
    // this kecamatan; all three levels keep the region picker in step.
    $locationFilterUrl = $listing->location
        ? route('public.listings.index', [
            'provinsi' => $listing->location->province,
            'kota' => $listing->location->city,
            'kecamatan' => $listing->location->name,
        ])
        : null;

    $showKpr = $listing->show_kpr && $listing->status !== 'disewa';

    // The denah is its own block so it can sit above the KPR card on mobile.
    $hasDenah = $listing->floor_plan_path || $listing->rooms->isNotEmpty();

    $dpAmount = (int) round($kpr['price'] * $kpr['minDp'] / 100);
    $months = (int) (($kpr['maxTenor'] >= 15) ? 15 : $kpr['maxTenor']) * 12;
    $monthly = \App\Support\KprCalculator::monthly($kpr['price'] - $dpAmount, $kpr['rate'], $months);

    $fill = fn (float $value, float $min, float $max) => round(max(0, min(100, ($value - $min) / max(1, $max - $min) * 100)), 1);

    // Mirrors the visible breadcrumb. The developer crumb has no page of its
    // own, so it stays out of the schema instead of inventing a URL.
    $breadcrumbItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Semua properti', 'item' => route('public.listings.index')],
    ];

    if ($listing->location && $locationFilterUrl) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $listing->location->name, 'item' => $locationFilterUrl];
    }

    $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => count($breadcrumbItems) + 1, 'name' => $listing->title];
@endphp

@push('head')
    <script type="application/ld+json" nonce="{{ $cspNonce }}">
        {!! json_encode([
            '@'.'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    <section class="bg-sand/60">
        <div class="mx-auto max-w-[82rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <div class="text-sm">
                <a href="{{ route('public.listings.index') }}" class="inline-flex items-center gap-1.5 text-ink-600 transition-colors duration-150 hover:text-ink-950">
                    <span aria-hidden="true">&larr;</span> Kembali ke katalog properti
                </a>
            </div>

            <nav aria-label="Breadcrumb" class="mt-4">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-ink-500">
                    <li><a href="{{ route('public.listings.index') }}" class="hover:text-ink-950">Semua properti</a></li>
                    @if ($listing->developer)
                        <li aria-hidden="true">/</li>
                        <li>{{ $listing->developer->name }}</li>
                    @endif
                    @if ($listing->location)
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ $locationFilterUrl }}" class="hover:text-ink-950">{{ $listing->location->name }}</a></li>
                    @endif
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-medium text-ink-950">{{ $listing->title }}</li>
                </ol>
            </nav>

            <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-x-10">
                <div class="order-1 min-w-0 lg:col-start-1">
                    @if ($cover)
                        <x-photo-gallery :photos="$gallery">
                            <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $listing->statusLabel() }}</span>
                            @if ($listing->isNew())
                                <span class="absolute left-3 top-3 rounded bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Baru</span>
                            @endif
                        </x-photo-gallery>
                    @else
                        <div class="relative flex aspect-[4/3] items-center justify-center bg-white">
                            <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="80" height="56" class="h-14 w-20">
                            <span class="absolute right-3 top-3 rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $listing->statusLabel() }}</span>
                            @if ($listing->isNew())
                                <span class="absolute left-3 top-3 rounded bg-paper px-2 py-0.5 text-xs font-semibold text-ink-950">Baru</span>
                            @endif
                        </div>
                    @endif

                    <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.14em] text-gold-700">{{ $eyebrow }}</p>

                    <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $listing->title }}</h1>

                    @if ($listing->address || $listing->location)
                        <p class="mt-3 text-sm text-ink-500">{{ $listing->address ?: $listing->location->name }}</p>
                    @endif

                    {{-- Only a teaser; the full copy sits below, so short text is not shown twice. --}}
                    @if (Str::length(strip_tags($listing->description)) > 200)
                        <p class="mt-5 max-w-2xl text-sm leading-7 text-ink-600">{{ Str::limit(strip_tags($listing->description), 200) }}</p>

                        <a href="#deskripsi" class="mt-3 inline-flex min-h-11 items-center gap-2 rounded border border-line px-4 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand">
                            Baca selengkapnya
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path d="M12 5v14m0 0 6-6m-6 6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    @endif
                </div>

                {{-- The column stretches to the page, so the visit card can stay
                     pinned while the long copy scrolls past it. --}}
                <aside class="contents lg:col-start-2 lg:row-start-1 lg:block {{ $hasDenah ? 'lg:row-span-5' : 'lg:row-span-4' }}">
                    <div class="contents lg:block lg:h-full lg:space-y-5">
                        <div class="order-2 bg-ink-950 p-6 text-paper">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-paper/60">
                                {{ $listing->developer ? 'Harga developer' : 'Harga' }}
                            </p>

                            <p class="tabular mt-2 text-5xl font-semibold leading-none">{{ $shortPrice }}</p>

                            <p class="mt-3 text-xs leading-5 text-paper/60">
                                {{ $price }} · harga {{ $listing->updated_at->locale('id')->translatedFormat('F Y') }},
                                belum termasuk BPHTB &amp; biaya KPR
                            </p>

                            <div class="mt-5 flex items-center gap-3 border-t border-paper/15 pt-5">
                                {{-- The brand mark is wider than a circle, so the
                                     fallback is fitted inside it instead of cropped. --}}
                                <img
                                    src="{{ $contact['photo'] }}"
                                    alt=""
                                    @class([
                                        'h-14 w-14 shrink-0 rounded-full border border-paper/20',
                                        'object-cover' => $contact['has_photo'],
                                        'bg-paper/10 object-contain p-2' => ! $contact['has_photo'],
                                    ])
                                >

                                <div class="min-w-0">
                                    <p class="font-semibold text-paper">{{ $contact['name'] }}</p>

                                    @if ($contact['title'])
                                        <p class="text-xs text-paper/60">{{ $contact['title'] }}</p>
                                    @endif

                                    <p class="mt-0.5 text-xs break-words text-paper/60">
                                        {{ $contact['phone'] ?: '—' }} | {{ $contact['email'] ?: '—' }}
                                    </p>
                                </div>
                            </div>

                            @if ($whatsappUrl)
                                <div class="mt-5 grid gap-2">
                                    <a href="#kunjungan" class="inline-flex min-h-12 items-center justify-center rounded bg-gold-500 px-4 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                        Jadwalkan kunjungan
                                    </a>

                                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="inline-flex min-h-12 items-center justify-center rounded border border-paper/30 px-4 text-sm font-semibold text-paper transition-colors duration-150 hover:bg-paper/10">
                                        {{ $contactLabel }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if ($showKpr)
                            <div
                                class="order-5 border border-line bg-white p-6"
                                x-data="{
                                    price: {{ $kpr['price'] }},
                                    rate: {{ $kpr['rate'] }},
                                    dp: {{ $kpr['minDp'] }},
                                    tenor: {{ $months / 12 }},
                                    get dpAmount() { return this.price * this.dp / 100 },
                                    get principal() { return this.price - this.dpAmount },
                                    get monthly() {
                                        const r = this.rate / 100 / 12, n = this.tenor * 12;
                                        if (r <= 0 || n <= 0) return 0;
                                        return this.principal * r / (1 - Math.pow(1 + r, -n));
                                    },
                                    get income() { return this.monthly * 3 },
                                    rp: function (value) { return 'Rp ' + Math.round(value).toLocaleString('id-ID') },
                                    rps: function (value) {
                                        if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
                                        return 'Rp ' + Math.round(value / 1000000).toLocaleString('id-ID') + ' jt';
                                    },
                                    get dpFill() { return ((this.dp - {{ $kpr['minDp'] }}) / {{ max(1, $kpr['maxDp'] - $kpr['minDp']) }} * 100) + '%' },
                                    get tenorFill() { return ((this.tenor - 5) / {{ max(1, $kpr['maxTenor'] - 5) }} * 100) + '%' },
                                    get rateFill() { return ((this.rate - 3) / 12 * 100) + '%' },
                                    init() {
                                        ['dp', 'tenor', 'rate'].forEach((key) => {
                                            this.$watch(key, () => {
                                                const target = document.getElementById('kpr-full');
                                                const full = target ? window.Alpine.$data(target) : null;
                                                if (full && key in full) full[key] = this[key];
                                            });
                                        });
                                    }
                                }"
                            >
                                <h2 class="text-lg font-semibold">Simulasi KPR</h2>

                                <div class="mt-5">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-2">
                                        <label for="kpr-dp" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-ink-600">Uang muka</label>
                                        <p class="tabular text-[11px] font-semibold text-ink-950">
                                            <span x-text="dp">{{ $kpr['minDp'] }}</span>% (<span x-text="rps(dpAmount)">{{ $rupiahShort($dpAmount) }}</span>)
                                        </p>
                                    </div>
                                    <input id="kpr-dp" type="range" min="{{ $kpr['minDp'] }}" max="{{ $kpr['maxDp'] }}" step="5" class="range w-full" x-model.number="dp" :style="'--fill: ' + dpFill" style="--fill: {{ $fill($kpr['minDp'], $kpr['minDp'], $kpr['maxDp']) }}%">
                                </div>

                                <div class="mt-4">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-2">
                                        <label for="kpr-tenor" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-ink-600">Tenor</label>
                                        <p class="tabular text-[11px] font-semibold text-ink-950"><span x-text="tenor">{{ $months / 12 }}</span> tahun</p>
                                    </div>
                                    <input id="kpr-tenor" type="range" min="5" max="{{ $kpr['maxTenor'] }}" step="5" class="range w-full" x-model.number="tenor" :style="'--fill: ' + tenorFill" style="--fill: {{ $fill($months / 12, 5, $kpr['maxTenor']) }}%">
                                </div>

                                <div class="mt-4">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-2">
                                        <label for="kpr-rate" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-ink-600">Bunga per tahun</label>
                                        <p class="tabular text-[11px] font-semibold text-ink-950">
                                            <span x-text="rate.toLocaleString('id-ID', { maximumFractionDigits: 2 })">{{ rtrim(rtrim(number_format($kpr['rate'], 2, ',', '.'), '0'), ',') }}</span>%
                                        </p>
                                    </div>
                                    <input id="kpr-rate" type="range" min="3" max="15" step="0.25" class="range w-full" x-model.number="rate" :style="'--fill: ' + rateFill" style="--fill: {{ $fill($kpr['rate'], 3, 15) }}%">
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-2">
                                    <div class="bg-gold-300/15 p-3">
                                        <p class="text-[11px] leading-4 text-ink-500">Cicilan per bulan</p>
                                        <p class="tabular mt-1.5 text-lg font-semibold leading-none" x-text="rp(monthly)">{{ $rupiah($monthly) }}</p>
                                    </div>
                                    <div class="bg-gold-300/15 p-3">
                                        <p class="text-[11px] leading-4 text-ink-500">Penghasilan min.</p>
                                        <p class="tabular mt-1.5 text-lg font-semibold leading-none" x-text="rps(income)">{{ $rupiahShort($monthly * 3) }}</p>
                                    </div>
                                </div>

                                <p class="mt-3 text-xs leading-5 text-ink-500">Asumsi bunga tetap sepanjang tenor. Penghasilan minimal = 3&times; cicilan.</p>

                                <a href="#kpr" class="mt-4 flex min-h-11 items-center justify-center rounded border border-line px-4 text-xs font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand">
                                    Rincian lengkap di simulasi KPR &rarr;
                                </a>
                            </div>
                        @endif

                        @if ($whatsappUrl)
                            {{-- No scheduling backend: the form hands the request to WhatsApp
                                 with the name and date already in the message. --}}
                            <div
                                id="kunjungan"
                                class="order-7 scroll-mt-24 border border-line bg-white p-6 lg:sticky lg:top-24"
                                x-data="{
                                    sent: false,
                                    error: '',
                                    book() {
                                        // The date lives in the picker's nested component, so
                                        // read it from the form instead of $refs.
                                        const name = this.$el.querySelector('input[name=name]').value.trim();
                                        const date = this.$el.querySelector('input[name=visit_date]').value;

                                        if (! date) {
                                            this.error = 'Pilih tanggal kunjungan dulu.';
                                            return;
                                        }

                                        const url = new URL(@js($whatsappUrl));
                                        const pretty = new Date(date + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                                        url.searchParams.set('text', 'Halo, saya ' + name + '. Saya ingin menjadwalkan kunjungan ke ' + @js($listing->title) + ' pada ' + pretty + '. Mohon konfirmasinya.');
                                        window.open(url.toString(), '_blank', 'noopener');
                                        this.sent = true;
                                    }
                                }"
                            >
                                <h2 class="text-lg font-semibold">Jadwalkan kunjungan</h2>
                                <p class="mt-1 text-sm text-ink-500">Pilih tanggal, tim kami mengonfirmasi lewat WhatsApp.</p>

                                <form class="mt-5" @submit.prevent="book()" x-show="! sent">
                                    <label for="visit-name" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-ink-600">Nama</label>
                                    <input id="visit-name" name="name" type="text" autocomplete="name" required placeholder="Nama Anda" class="mt-1.5 h-12 w-full rounded border-line bg-sand px-3 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500">

                                    <p id="visit-date-label" class="mt-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-ink-600">Tanggal kunjungan</p>

                                    <div
                                        class="relative mt-1.5"
                                        x-data="{
                                            open: false,
                                            iso: '',
                                            pretty: '',
                                            min: new Date({{ now()->year }}, {{ now()->month - 1 }}, {{ now()->day }}),
                                            view: new Date({{ now()->year }}, {{ now()->month - 1 }}, 1),
                                            get days() {
                                                const y = this.view.getFullYear(), m = this.view.getMonth();
                                                const cells = [];
                                                for (let i = 0; i < (new Date(y, m, 1).getDay() + 6) % 7; i++) cells.push(null);
                                                for (let d = 1; d <= new Date(y, m + 1, 0).getDate(); d++) cells.push(new Date(y, m, d));
                                                return cells;
                                            },
                                            get label() { return this.view.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) },
                                            get canPrev() {
                                                return this.view.getFullYear() > this.min.getFullYear()
                                                    || (this.view.getFullYear() === this.min.getFullYear() && this.view.getMonth() > this.min.getMonth());
                                            },
                                            ymd(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') },
                                            past(d) { return this.ymd(d) < this.ymd(this.min) },
                                            today(d) { return this.ymd(d) === this.ymd(new Date()) },
                                            chosen(d) { return this.iso === this.ymd(d) },
                                            prev() { this.view = new Date(this.view.getFullYear(), this.view.getMonth() - 1, 1) },
                                            next() { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + 1, 1) },
                                            pick(d) {
                                                this.iso = this.ymd(d);
                                                this.pretty = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                                                this.open = false;
                                                this.$refs.visit.focus();
                                            }
                                        }"
                                        @click.outside="open = false"
                                        @keydown.escape.window="open = false"
                                    >
                                        <button
                                            type="button"
                                            x-ref="visit"
                                            @click="open = !open"
                                            :aria-expanded="open"
                                            aria-haspopup="dialog"
                                            aria-labelledby="visit-date-label"
                                            class="flex h-12 w-full items-center justify-between rounded border border-line bg-sand px-3 text-left text-sm text-ink-950 focus:border-gold-700 focus:ring-gold-500"
                                        >
                                            <span x-text="pretty || 'Pilih tanggal'" :class="! pretty && 'text-ink-500'"></span>
                                            <svg class="h-5 w-5 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                            </svg>
                                        </button>

                                        <div x-cloak x-show="open" role="dialog" aria-label="Pilih tanggal kunjungan" class="absolute left-0 right-0 z-30 mt-2 rounded border border-line bg-white p-3 shadow-float">
                                            <div class="flex items-center justify-between gap-2">
                                                <button type="button" @click="prev()" :disabled="! canPrev" aria-label="Bulan sebelumnya" class="inline-flex h-9 w-9 items-center justify-center rounded text-ink-600 transition-colors duration-150 hover:bg-sand disabled:opacity-30 disabled:hover:bg-transparent">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>

                                                <p class="text-sm font-semibold" x-text="label" aria-live="polite"></p>

                                                <button type="button" @click="next()" aria-label="Bulan berikutnya" class="inline-flex h-9 w-9 items-center justify-center rounded text-ink-600 transition-colors duration-150 hover:bg-sand">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                            </div>

                                            <div class="mt-2 grid grid-cols-7 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-500">
                                                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                                            </div>

                                            <div class="mt-1 grid grid-cols-7 gap-0.5">
                                                <template x-for="(day, index) in days" :key="index">
                                                    <button
                                                        type="button"
                                                        @click="pick(day)"
                                                        :disabled="! day || past(day)"
                                                        :aria-label="day ? day.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : ''"
                                                        :aria-pressed="day && chosen(day)"
                                                        :aria-current="day && today(day) ? 'date' : false"
                                                        x-text="day ? day.getDate() : ''"
                                                        class="tabular inline-flex h-9 items-center justify-center rounded text-sm transition-colors duration-150"
                                                        :class="! day || past(day)
                                                            ? 'text-ink-500/40'
                                                            : (chosen(day)
                                                                ? 'bg-gold-500 font-semibold text-ink-950'
                                                                : (today(day) ? 'text-ink-950 ring-1 ring-inset ring-gold-700 hover:bg-sand' : 'text-ink-950 hover:bg-sand'))"
                                                    ></button>
                                                </template>
                                            </div>
                                        </div>

                                        <input type="hidden" name="visit_date" :value="iso">
                                    </div>

                                    <p x-show="error" x-text="error" class="mt-2 text-xs text-danger"></p>

                                    <button type="submit" class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded bg-gold-500 px-4 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                                        Kirim ke marketing
                                    </button>
                                </form>

                                <p x-cloak x-show="sent" class="mt-5 rounded bg-sand p-4 text-sm text-ink-700">
                                    Jendela WhatsApp terbuka. Kirim pesannya untuk mengonfirmasi jadwal.
                                </p>

                                <p class="mt-3 text-xs text-ink-500">Tim marketing Avenma, tanpa biaya tambahan.</p>
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="order-3 min-w-0 lg:col-start-1">
                    @if ($stats !== [])
                        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($stats as $stat)
                                <div class="border border-line bg-white p-5">
                                    <dd class="tabular text-3xl font-semibold leading-none">{{ $stat['value'] }}</dd>
                                    <dt class="mt-3 text-[11px] uppercase leading-4 tracking-wide text-ink-500">{{ $stat['label'] }}</dt>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>

                @if ($hasDenah)
                    <div class="order-4 min-w-0 lg:col-start-1">
                        <section>
                            <h2 class="text-3xl font-semibold tracking-tight">Denah</h2>

                            @if ($listing->floor_plan_path)
                                <img
                                    src="{{ asset($listing->floor_plan_path) }}"
                                    alt="Denah {{ $listing->title }}"
                                    width="960"
                                    height="1280"
                                    loading="lazy"
                                    decoding="async"
                                    class="mt-4 w-full max-w-[420px] border border-line bg-white object-contain"
                                >
                                <p class="mt-2 text-xs text-ink-500">Skala ilustratif.</p>
                            @else
                                @php
                                    $floors = $listing->rooms->groupBy('floor')->sortKeys();
                                    $caption = $listing->land_area
                                        ? 'Kavling '.$listing->land_area.' m² · skala ilustratif'
                                        : 'Skala ilustratif';
                                @endphp

                                <div class="mt-4 space-y-6">
                                    @foreach ($floors as $floor => $rooms)
                                        <div class="max-w-[420px]">
                                            <p class="text-sm font-medium text-ink-600">Denah lantai {{ $floor }}</p>
                                            <x-floor-plan :rooms="$rooms" :caption="$caption" class="mt-2" />
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>
                @endif

                <div class="order-6 min-w-0 lg:col-start-1">
                    @if ($listing->description)
                        <section id="deskripsi" class="scroll-mt-24">
                            <h2 class="text-3xl font-semibold tracking-tight">Deskripsi dan fasilitas</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink-700">{{ $listing->description }}</p>
                        </section>
                    @endif

                    @if ($mapSrc)
                        <section class="mt-6">
                            <h2 class="text-3xl font-semibold tracking-tight">Lokasi</h2>

                            @if ($listing->address)
                                <p class="mt-2 text-sm text-ink-600">{{ $listing->address }}</p>
                            @endif

                            <div class="relative mt-4">
                                <iframe src="{{ $mapSrc }}" title="Peta lokasi {{ $listing->title }}" class="h-80 w-full border border-line" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                                @if ($mapsUrl = $listing->mapsUrl())
                                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="absolute left-2 top-2 z-10 inline-flex min-h-10 items-center gap-1.5 rounded border border-line bg-white px-3 text-xs font-semibold text-ink-950 shadow-float transition-colors duration-150 hover:bg-sand">
                                        <svg class="h-4 w-4 text-ink-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                        Buka di Google Maps
                                    </a>
                                @endif
                            </div>
                        </section>
                    @endif
                </div>

                <div class="order-8 min-w-0 lg:col-start-1">
                    @if ($showKpr)
                        <section id="kpr" class="scroll-mt-24">
                            <h2 class="text-3xl font-semibold tracking-tight">Simulasi KPR lengkap</h2>
                            <p class="mt-1 text-sm text-ink-500">Ubah harga, tenor, dan bunga. Bukan penawaran resmi bank.</p>

                            <x-kpr-simulation
                                id="kpr-full"
                                class="mt-4"
                                :price="$kpr['price']"
                                :interest="$kpr['rate']"
                                :min-dp="$kpr['minDp']"
                                :max-dp="$kpr['maxDp']"
                                :max-tenor="$kpr['maxTenor']"
                                :cta-url="$whatsappUrl"
                                cta-label="Konsultasi KPR via WhatsApp"
                            />
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('head')
    {{-- The gallery's first photo is the largest paint; fetch it with the document. --}}
    @if ($listing->images->isNotEmpty())
        <link rel="preload" as="image" href="{{ asset($listing->images->first()->path) }}" fetchpriority="high">
    @endif

    {{-- Filled range track, matched to the thumb colour. --}}
    <style>
        .range::-webkit-slider-runnable-track {
            background: linear-gradient(to right, #dfa73a var(--fill, 0%), #e4e1db var(--fill, 0%));
        }

        .range::-moz-range-track {
            background: linear-gradient(to right, #dfa73a var(--fill, 0%), #e4e1db var(--fill, 0%));
        }
    </style>
@endpush
