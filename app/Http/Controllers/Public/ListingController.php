<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
        $filters = [
            'lokasi' => (string) $request->query('lokasi', ''),
            'tipe' => (string) $request->query('tipe', ''),
            'harga' => (string) $request->query('harga', ''),
            'status' => (string) $request->query('status', ''),
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

        $locationNames = Location::orderBy('name')->pluck('name', 'slug');

        return view('public.listings.index', [
            'listings' => $listings,
            'locations' => $locationNames,
            'typeOptions' => Listing::typeOptions(),
            'statusOptions' => Listing::statusOptions(),
            'filters' => $filters,
            'sort' => $sort,
            'perPage' => $perPage,
            'chips' => $this->chips($filters, $sort, $locationNames->all()),
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
     * @param  array<string, string>  $filters
     * @param  array<string, string>  $locationNames
     * @return list<array{key: string, label: string}>
     */
    private function chips(array $filters, string $sort, array $locationNames): array
    {
        $chips = [];

        if ($filters['lokasi'] !== '') {
            $chips[] = ['key' => 'lokasi', 'label' => $locationNames[$filters['lokasi']] ?? $filters['lokasi']];
        }

        if ($filters['tipe'] !== '') {
            $chips[] = ['key' => 'tipe', 'label' => ucfirst($filters['tipe'])];
        }

        if ($filters['harga'] !== '') {
            $chips[] = ['key' => 'harga', 'label' => Listing::PRICE_BUCKETS[$filters['harga']]['label'] ?? $filters['harga']];
        }

        if ($filters['status'] !== '') {
            $chips[] = ['key' => 'status', 'label' => ucfirst($filters['status'])];
        }

        if ($sort !== 'terbaru') {
            $chips[] = ['key' => 'urut', 'label' => Listing::SORTS[$sort]];
        }

        return $chips;
    }
}
