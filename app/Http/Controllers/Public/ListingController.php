<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\KprSetting;
use App\Models\Listing;
use App\Models\Location;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $regions = $this->regions();

        $developerOptions = Developer::query()
            ->whereHas('listings', fn ($query) => $query->published())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        // A value that is not in the tree (old link, hand-edited URL) is
        // dropped, so the panel never shows a kota outside its provinsi.
        $provinsi = $this->allowed($regions['provinces'], $request->query('provinsi'));
        $kota = $this->allowed($regions['cities'][$provinsi] ?? [], $request->query('kota'));
        $kecamatan = $this->allowed($regions['districts'][$provinsi.'|'.$kota] ?? [], $request->query('kecamatan'));

        $filters = [
            'provinsi' => $provinsi,
            'kota' => $kota,
            'kecamatan' => $kecamatan,
            'tipe' => (string) $request->query('tipe', ''),
            'penawaran' => array_key_exists((string) $request->query('penawaran'), Listing::LISTING_TYPES)
                ? (string) $request->query('penawaran')
                : '',
            'harga' => (string) $request->query('harga', ''),
            'status' => (string) $request->query('status', ''),
            'kamar' => $this->minimum($request->query('kamar')),
            'mandi' => $this->minimum($request->query('mandi')),
            'lt' => $this->minimum($request->query('lt')),
            'lb' => $this->minimum($request->query('lb')),
            'lantai' => $this->minimum($request->query('lantai')),
            'developer' => array_key_exists((string) $request->query('developer'), $developerOptions)
                ? (string) $request->query('developer')
                : '',
        ];

        $sort = (string) $request->query('urut', 'terbaru');
        $sort = array_key_exists($sort, Listing::SORTS) ? $sort : 'terbaru';

        $perPage = (int) $request->query('per', (string) Listing::PER_PAGE_OPTIONS[0]);
        $perPage = in_array($perPage, Listing::PER_PAGE_OPTIONS, true) ? $perPage : Listing::PER_PAGE_OPTIONS[0];

        $listings = Listing::query()
            ->published()
            ->filter($filters)
            ->sorted($sort)
            ->with(['location', 'developer', 'images'])
            ->paginate($perPage)
            ->withQueryString();

        return view('public.listings.index', [
            'listings' => $listings,
            'regions' => $regions,
            'typeOptions' => Listing::typeOptions(),
            'listingTypeOptions' => Listing::LISTING_TYPES,
            'statusOptions' => Listing::STATUSES,
            'developerOptions' => $developerOptions,
            'filters' => $filters,
            'sort' => $sort,
            'perPage' => $perPage,
            'chips' => $this->chips($filters, $sort, $developerOptions),
        ]);
    }

    public function show(Request $request, Listing $listing): View
    {
        abort_unless($listing->is_published, 404);

        $listing->load(['location', 'developer', 'creator', 'images']);

        // Listing phone wins; blank falls back to the office number in settings.
        $phone = $listing->contact_phone ?: SiteSetting::whatsapp();

        return view('public.listings.show', [
            'listing' => $listing,
            'whatsappUrl' => $this->whatsappUrl($phone, $listing),
            'contactLabel' => $listing->contact_phone ? 'Hubungi Agen' : 'Hubungi Avenma',
            'kpr' => $this->kpr($listing),
        ]);
    }

    /**
     * Settings that seed the KPR widget. The listing price comes from the row,
     * the rest from kpr_settings; the widget recalculates in the browser.
     *
     * @return array<string, mixed>
     */
    private function kpr(Listing $listing): array
    {
        $settings = KprSetting::query()->first();

        return [
            'price' => max(1, (int) $listing->price),
            'rate' => (float) ($settings?->interest_rate ?? 7.5),
            'minDp' => (float) ($settings?->min_dp_percent ?? 20),
            'maxDp' => 70,
            'maxTenor' => (int) ($settings?->max_tenor_years ?? 20),
        ];
    }

    private function whatsappUrl(?string $phone, Listing $listing): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        if (strlen($digits) < 10) {
            return null;
        }

        $message = 'Halo, saya tertarik dengan properti "'.$listing->title.'" ('.route('public.listings.show', $listing).'). Mohon informasi lebih lanjut.';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    /**
     * Regions that have at least one published listing, with counts, shaped for
     * the three pickers: provinces, then cities by province, then kecamatan by
     * "province|city".
     *
     * @return array{provinces: list<array{name: string, count: int}>, cities: array<string, list<array{name: string, count: int}>>, districts: array<string, list<array{name: string, count: int}>>}
     */
    private function regions(): array
    {
        $rows = Location::query()
            ->whereHas('listings', fn ($query) => $query->published())
            ->withCount(['listings as total' => fn ($query) => $query->published()])
            ->get();

        $provinces = [];
        $cities = [];
        $districts = [];

        foreach ($rows as $row) {
            $provinces[$row->province] = ($provinces[$row->province] ?? 0) + $row->total;
            $cities[$row->province][$row->city] = ($cities[$row->province][$row->city] ?? 0) + $row->total;
            $districts[$row->province.'|'.$row->city][$row->name] = $row->total;
        }

        return [
            'provinces' => $this->options($provinces),
            'cities' => array_map(fn (array $group) => $this->options($group), $cities),
            'districts' => array_map(fn (array $group) => $this->options($group), $districts),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{name: string, count: int}>
     */
    private function options(array $counts): array
    {
        ksort($counts);

        $options = [];

        foreach ($counts as $name => $count) {
            $options[] = ['name' => $name, 'count' => $count];
        }

        return $options;
    }

    /**
     * @param  list<array{name: string, count: int}>  $options
     */
    private function allowed(array $options, mixed $value): string
    {
        $value = (string) $value;

        return in_array($value, array_column($options, 'name'), true) ? $value : '';
    }

    /**
     * Spec filters take "at least this many": anything not a positive number
     * means "no filter".
     */
    private function minimum(mixed $value): string
    {
        $value = (int) $value;

        return $value > 0 ? (string) $value : '';
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<int, string>  $developerOptions
     * @return list<array{key: string, label: string}>
     */
    private function chips(array $filters, string $sort, array $developerOptions): array
    {
        $chips = [];

        foreach (['provinsi', 'kota', 'kecamatan'] as $key) {
            if ($filters[$key] !== '') {
                $chips[] = ['key' => $key, 'label' => $filters[$key]];
            }
        }

        if ($filters['tipe'] !== '') {
            $chips[] = ['key' => 'tipe', 'label' => ucfirst($filters['tipe'])];
        }

        if ($filters['penawaran'] !== '') {
            $chips[] = ['key' => 'penawaran', 'label' => Listing::LISTING_TYPES[$filters['penawaran']]];
        }

        if ($filters['harga'] !== '') {
            $chips[] = ['key' => 'harga', 'label' => Listing::PRICE_BUCKETS[$filters['harga']]['label'] ?? $filters['harga']];
        }

        if ($filters['status'] !== '') {
            $chips[] = ['key' => 'status', 'label' => Listing::STATUSES[$filters['status']] ?? ucfirst($filters['status'])];
        }

        if ($filters['kamar'] !== '') {
            $chips[] = ['key' => 'kamar', 'label' => $filters['kamar'].'+ kamar tidur'];
        }

        if ($filters['mandi'] !== '') {
            $chips[] = ['key' => 'mandi', 'label' => $filters['mandi'].'+ kamar mandi'];
        }

        if ($filters['lt'] !== '') {
            $chips[] = ['key' => 'lt', 'label' => 'LT ≥ '.$filters['lt'].' m²'];
        }

        if ($filters['lb'] !== '') {
            $chips[] = ['key' => 'lb', 'label' => 'LB ≥ '.$filters['lb'].' m²'];
        }

        if ($filters['lantai'] !== '') {
            $chips[] = ['key' => 'lantai', 'label' => $filters['lantai'].'+ lantai'];
        }

        if ($filters['developer'] !== '') {
            $chips[] = ['key' => 'developer', 'label' => $developerOptions[(int) $filters['developer']] ?? 'Developer'];
        }

        if ($sort !== 'terbaru') {
            $chips[] = ['key' => 'urut', 'label' => Listing::SORTS[$sort]];
        }

        return $chips;
    }
}
