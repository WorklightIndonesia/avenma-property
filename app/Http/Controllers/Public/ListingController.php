<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Location;
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

        $listings = Listing::query()
            ->published()
            ->filter($filters)
            ->sorted($sort)
            ->with(['location', 'developer'])
            ->paginate(6)
            ->withQueryString();

        $locationNames = Location::orderBy('name')->pluck('name', 'slug');

        return view('public.listings.index', [
            'listings' => $listings,
            'locations' => $locationNames,
            'typeOptions' => Listing::typeOptions(),
            'statusOptions' => Listing::statusOptions(),
            'filters' => $filters,
            'sort' => $sort,
            'chips' => $this->chips($filters, $sort, $locationNames->all()),
        ]);
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
