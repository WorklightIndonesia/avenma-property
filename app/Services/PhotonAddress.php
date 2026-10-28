<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Suggestions for the address field, each already matched to an official
 * kecamatan so the browser never stores a name the map service invented.
 *
 * Photon repeats raw OSM tags: `city` can read "Jakarta", a comma-joined
 * kelurahan chain, or an English name, and `state` is often the island
 * ("Jawa"). Every fragment is matched against the region table, and a level is
 * only filled when it names exactly one row — otherwise the agent picks it.
 */
class PhotonAddress
{
    /** OSM tags that arrive in English. */
    private const ALIAS = [
        'south tangerang' => 'tangerang selatan',
        'north jakarta' => 'jakarta utara',
        'west jakarta' => 'jakarta barat',
        'east jakarta' => 'jakarta timur',
        'central jakarta' => 'jakarta pusat',
        'south jakarta' => 'jakarta selatan',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 3) {
            return [];
        }

        $key = 'photon-'.md5(mb_strtolower($term));

        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        try {
            $response = Http::timeout(4)
                ->withUserAgent('AvenmaProperty/1.0 (listing address picker)')
                ->get('https://photon.komoot.io/api/', ['q' => $term, 'limit' => 8]);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $results = collect($response->json('features', []))
            ->filter(fn (array $feature) => ($feature['properties']['countrycode'] ?? null) === 'ID')
            ->map(fn (array $feature) => $this->row($feature))
            ->filter()
            ->take(5)
            ->values()
            ->all();

        // Failures returned above, so one timeout cannot hide a street for hours.
        Cache::put($key, $results, now()->addHours(6));

        return $results;
    }

    /**
     * @param  array<string, mixed>  $feature
     * @return array<string, mixed>|null
     */
    private function row(array $feature): ?array
    {
        $props = $feature['properties'] ?? [];
        $label = trim((string) ($props['name'] ?? ''));
        $lat = $feature['geometry']['coordinates'][1] ?? null;
        $lng = $feature['geometry']['coordinates'][0] ?? null;

        if ($label === '' || $lat === null) {
            return null;
        }

        return [
            'label' => $label,
            'street' => trim(($props['street'] ?? '').' '.($props['housenumber'] ?? '')),
            // Shown only when nothing matches the official table.
            'context' => collect([$props['district'] ?? null, $props['city'] ?? null, $props['state'] ?? null])
                ->filter()
                ->implode(', '),
            'lat' => $lat,
            'lng' => $lng,
        ] + $this->resolve($props);
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array{province: ?string, city: ?string, district: ?string, location_id: ?int}
     */
    private function resolve(array $props): array
    {
        $province = $this->uniqueName('province', $this->tokens($props['state'] ?? null));

        $city = null;
        $cityTokens = $this->tokens($props['city'] ?? null);

        if ($cityTokens !== []) {
            $city = $this->uniqueName('city', $cityTokens, $province ? ['province' => $province] : []);

            if ($city) {
                $province ??= Location::where('city', $city)->value('province');
            }
        }

        $location = null;
        $districtTokens = $this->tokens($props['district'] ?? null);

        if ($districtTokens !== []) {
            $hits = $this->candidates('name', $districtTokens, $city
                ? ['city' => $city]
                : ($province ? ['province' => $province] : []));

            // Two hits mean the name repeats across kota/kabupaten; guessing
            // would file the listing under the wrong city, so leave it empty.
            if ($hits->count() === 1) {
                $location = $hits->first();
                $province = $location->province;
                $city = $location->city;
            }
        }

        return [
            'province' => $province,
            'city' => $city,
            'district' => $location?->name,
            'location_id' => $location?->id,
        ];
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $scope
     */
    private function uniqueName(string $column, array $tokens, array $scope = []): ?string
    {
        if ($tokens === []) {
            return null;
        }

        $names = $this->candidates($column, $tokens, $scope)->pluck($column)->unique();

        return $names->count() === 1 ? $names->first() : null;
    }

    /**
     * Rows whose column survives both passes: SQL narrows by substring, PHP
     * keeps the exact key matches when there are any, so "serpong" does not
     * also pull in "Serpong Utara".
     *
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $scope
     * @return Collection<int, Location>
     */
    private function candidates(string $column, array $tokens, array $scope = []): Collection
    {
        $rows = Location::query()
            ->when($scope, fn (Builder $query) => $query->where($scope))
            ->where(function (Builder $query) use ($column, $tokens) {
                foreach ($tokens as $token) {
                    $query->orWhere($column, 'like', '%'.$token.'%');
                }
            })
            ->get(['id', 'name', 'province', 'city']);

        $exact = $rows->filter(fn (Location $row) => in_array($this->key($row->{$column}), $tokens, true));

        return $exact->isNotEmpty()
            ? $exact
            : $rows->filter(fn (Location $row) => $this->contains($this->key($row->{$column}), $tokens));
    }

    /**
     * @return array<int, string>
     */
    private function tokens(mixed $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn (string $part) => $this->key($part))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function contains(string $key, array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (str_contains($key, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Names are compared without their administrative prefix, so "South
     * Tangerang", "Kota Tangerang Selatan" and "tangerang selatan" agree.
     */
    private function key(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = preg_replace('/\b(kota|kabupaten|administrasi|dki|daerah|khusus|ibukota|city|regency)\b/', ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        return self::ALIAS[$value] ?? $value;
    }
}
