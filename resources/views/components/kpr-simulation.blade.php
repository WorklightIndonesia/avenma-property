@props([
    'price' => 0,
    'interest' => 7.5,
    'minDp' => 20,
    'maxDp' => 70,
    'maxTenor' => 20,
    'heading' => 'Hitung angsuran Anda',
    'subheading' => null,
    'ctaUrl' => null,
    'ctaLabel' => 'Konsultasi KPR via WhatsApp',
    'settingsNote' => true,
])

@php
    $price = max(0, (int) $price);
    $minDp = max(0, min(70, (float) $minDp));
    $maxDp = max($minDp, min(90, (float) $maxDp));
    $interest = min(15, max(3, (float) $interest));

    $tenorOptions = collect([5, 10, 15, 20])->filter(fn ($years) => $years <= max(5, (int) $maxTenor))->values();
    $tenor = $tenorOptions->contains(15) ? 15 : $tenorOptions->max();

    // Initial figures are rendered server side so the panel is correct before Alpine loads.
    // The same annuity runs again in the browser on every change; keep both in step.
    $dpAmount = (int) round($price * $minDp / 100);
    $principal = $price - $dpAmount;
    $months = (int) $tenor * 12;
    $monthly = (float) \App\Support\KprCalculator::monthly($principal, $interest, $months);
    $totalPayment = $dpAmount + $monthly * $months;

    $rupiah = fn (float $amount) => 'Rp '.number_format($amount, 0, ',', '.');
    $percent = fn (float $value) => rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    $id = 'kpr-'.substr(md5($price.$interest.$minDp), 0, 6);
@endphp

<div
    {{ $attributes->merge(['class' => 'border border-line bg-white p-5 sm:p-6']) }}
    x-data="{
        price: {{ $price }},
        rate: {{ $interest }},
        rateType: 'fixed',
        dp: {{ $minDp }},
        tenor: {{ $tenor }},
        get dpAmount() { return this.price * this.dp / 100 },
        get principal() { return this.price - this.dpAmount },
        get rateLabel() { return this.rate.toLocaleString('id-ID', { maximumFractionDigits: 2 }) },
        get monthly() {
            const r = this.rate / 100 / 12, n = this.tenor * 12;
            if (r <= 0 || n <= 0) return 0;
            return this.principal * r / (1 - Math.pow(1 + r, -n));
        },
        get totalInterest() { return this.monthly * this.tenor * 12 - this.principal },
        get totalPayment() { return this.dpAmount + this.monthly * this.tenor * 12 },
        rp: function (value) { return 'Rp ' + Math.round(value).toLocaleString('id-ID') },
        setPrice: function (event) {
            const el = event.target;
            const caretDigits = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
            const digits = el.value.replace(/\D/g, '');
            this.price = digits === '' ? 0 : Math.min(parseInt(digits, 10), 999999999999);
            const text = this.price === 0 ? '' : this.price.toLocaleString('id-ID');
            el.value = text;
            let pos = 0, seen = 0;
            while (pos < text.length && seen < caretDigits) {
                if (/\d/.test(text[pos])) seen++;
                pos++;
            }
            el.setSelectionRange(pos, pos);
        }
    }"
>
    <p class="text-2xl font-semibold tracking-tight">{{ $heading }}</p>
    <p class="mt-1 max-w-xl text-sm text-ink-500">
        {{ $subheading ?? 'Perkiraan angsuran bulanan dengan metode anuitas.' }}
    </p>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
            <div>
                <div>
                    <label for="{{ $id }}-price" class="text-sm font-semibold">Harga properti</label>
                    <div class="mt-1 flex items-baseline gap-2 rounded border border-line bg-white px-3 py-2 focus-within:border-gold-700 focus-within:ring-2 focus-within:ring-gold-500/30">
                        <span class="text-sm text-ink-500">Rp</span>
                        <input
                            id="{{ $id }}-price"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            value="{{ $price > 0 ? number_format($price, 0, ',', '.') : '' }}"
                            @input="setPrice($event)"
                            class="tabular w-full min-w-0 border-0 bg-transparent p-0 text-lg font-semibold text-ink-950 focus:outline-none focus:ring-0"
                            aria-describedby="{{ $id }}-price-help"
                            placeholder="Masukkan harga properti"
                        >
                    </div>
                    <p id="{{ $id }}-price-help" class="mt-1 text-xs text-ink-500">{{ $price > 0 ? 'Terisi dari listing. Ubah untuk mencoba harga lain.' : 'Masukkan harga properti untuk melihat angsuran.' }}</p>
                </div>

                <div class="mt-6">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <label for="{{ $id }}-dp" class="text-sm font-semibold">Uang muka (DP)</label>
                        <p class="tabular text-sm font-semibold"><span x-text="dp">{{ $percent($minDp) }}</span>% · <span x-text="rp(dpAmount)">{{ $rupiah($dpAmount) }}</span></p>
                    </div>
                    <input id="{{ $id }}-dp" type="range" min="{{ $minDp }}" max="{{ $maxDp }}" step="5" class="range mt-2 w-full" x-model.number="dp" aria-describedby="{{ $id }}-dp-help">
                    <p id="{{ $id }}-dp-help" class="mt-1 text-xs text-ink-500">Minimum {{ $percent($minDp) }}%. Maksimum {{ $percent($maxDp) }}%.</p>
                </div>

                <div class="mt-6">
                    <p id="{{ $id }}-tenor-label" class="text-sm font-semibold">Tenor (tahun)</p>
                    <div class="mt-2 flex flex-wrap gap-2" role="group" aria-labelledby="{{ $id }}-tenor-label">
                        @foreach ($tenorOptions as $years)
                            <button
                                type="button"
                                class="h-11 flex-1 rounded border text-sm font-semibold transition-colors duration-150"
                                :class="tenor === {{ $years }} ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                                :aria-pressed="tenor === {{ $years }}"
                                @click="tenor = {{ $years }}"
                            >{{ $years }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 border-t border-line pt-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <label for="{{ $id }}-rate" class="text-sm font-semibold">Suku bunga</label>
                        <p class="tabular text-sm font-semibold"><span x-text="rateLabel">{{ $percent($interest) }}</span>% per tahun</p>
                    </div>
                    <input id="{{ $id }}-rate" type="range" min="3" max="15" step="0.25" class="range mt-2 w-full" x-model.number="rate" aria-describedby="{{ $id }}-rate-help">
                    <div class="mt-2 flex flex-wrap gap-2" role="group" aria-label="Tipe suku bunga">
                        <button
                            type="button"
                            class="h-11 rounded border px-4 text-sm font-semibold transition-colors duration-150"
                            :class="rateType === 'fixed' ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                            :aria-pressed="rateType === 'fixed'"
                            @click="rateType = 'fixed'"
                        >Tetap</button>
                        <button
                            type="button"
                            class="h-11 rounded border px-4 text-sm font-semibold transition-colors duration-150"
                            :class="rateType === 'floating' ? 'border-ink-950 bg-ink-950 text-paper' : 'border-line text-ink-600 hover:border-ink-950'"
                            :aria-pressed="rateType === 'floating'"
                            @click="rateType = 'floating'"
                        >Mengambang</button>
                    </div>
                    <p id="{{ $id }}-rate-help" class="mt-2 text-xs text-ink-500" x-text="rateType === 'fixed' ? 'Bunga tetap selama tenor. Angka asumsi internal, bukan penawaran bank.' : 'Bunga mengambang: angsuran bisa berubah bila suku bunga pasar naik.'">Bunga tetap selama tenor. Angka asumsi internal, bukan penawaran bank.</p>
                </div>
            </div>

            <div class="bg-ink-950 p-5 text-paper sm:p-6">
                <p class="text-sm text-paper/60">Angsuran per bulan</p>
                <p class="tabular mt-2 text-3xl font-semibold leading-none sm:text-4xl" x-text="price > 0 ? rp(monthly) : '—'">{{ $price > 0 ? $rupiah($monthly) : '—' }}</p>
                <p class="mt-2 text-sm text-paper/60"><span x-text="tenor">{{ $tenor }}</span> tahun · <span x-text="rateType === 'fixed' ? 'bunga tetap' : 'bunga mengambang'">bunga tetap</span></p>

                <dl class="mt-6 space-y-3 border-t border-paper/15 pt-5 text-sm">
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-paper/60">Uang muka</dt>
                        <dd class="tabular font-semibold" x-text="rp(dpAmount)">{{ $rupiah($dpAmount) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-paper/60">Pokok pinjaman</dt>
                        <dd class="tabular font-semibold" x-text="rp(principal)">{{ $rupiah($principal) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-paper/60">Total bunga</dt>
                        <dd class="tabular font-semibold" x-text="rp(totalInterest)">{{ $rupiah($monthly * $months - $principal) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 border-t border-paper/15 pt-3">
                        <dt class="font-semibold">Total pembayaran</dt>
                        <dd class="tabular font-semibold" x-text="rp(totalPayment)">{{ $rupiah($totalPayment) }}</dd>
                    </div>
                </dl>

                @if ($ctaUrl)
                    <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" class="mt-6 flex min-h-12 items-center justify-center rounded bg-gold-500 px-5 py-3 text-center text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white">
                        {{ $ctaLabel }}
                    </a>
                @else
                    <span class="mt-6 flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-center text-sm font-semibold text-ink-950">{{ $ctaLabel }}</span>
                @endif

                <p class="mt-3 text-xs leading-relaxed text-paper/50">Perkiraan saja. Angsuran sebenarnya ditentukan bank dan mencakup provisi, asuransi, serta pajak.</p>
            </div>
        </div>

        @if ($settingsNote)
            <p class="mt-4 text-sm text-ink-500">Metode anuitas: angsuran tetap tiap bulan, bunga dihitung dari sisa pokok. Angka awal bunga dan DP minimum mengikuti <code class="text-xs">kpr_settings</code>.</p>
        @endif
</div>
