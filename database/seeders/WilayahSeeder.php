<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Loads every kecamatan from database/data/wilayah.csv (Kemendagri codes,
 * regenerate with database/data/build-wilayah.mjs). The six free-text areas
 * the demo listings used before are repointed to their real kecamatan and
 * removed; the guard is `code`, so a second run leaves the official rows alone.
 */
class WilayahSeeder extends Seeder
{
    /** Legacy slug => [kecamatan, kota/kabupaten]. */
    private const LEGACY = [
        'bintaro' => ['Pondok Aren', 'Kota Tangerang Selatan'],
        'bsd' => ['Serpong', 'Kota Tangerang Selatan'],
        'serpong' => ['Serpong', 'Kota Tangerang Selatan'],
        'cipete' => ['Cilandak', 'Kota Administrasi Jakarta Selatan'],
        'kemang' => ['Mampang Prapatan', 'Kota Administrasi Jakarta Selatan'],
        'sudirman' => ['Setiabudi', 'Kota Administrasi Jakarta Selatan'],
    ];

    public function run(): void
    {
        $this->loadCsv(database_path('data/wilayah.csv'));
        $this->remapLegacyAreas();
    }

    private function loadCsv(string $path): void
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot read {$path}");
        }

        fgetcsv($handle); // header
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            [$code, $province, $city, $name] = $row;

            $rows[] = [
                'code' => $code,
                'name' => $name,
                'city' => $city,
                'province' => $province,
                'slug' => $code,
            ];

            if (count($rows) === 500) {
                $this->flush($rows);
                $rows = [];
            }
        }

        fclose($handle);
        $this->flush($rows);
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function flush(array $rows): void
    {
        if ($rows !== []) {
            Location::upsert($rows, ['code'], ['name', 'province', 'city']);
        }
    }

    private function remapLegacyAreas(): void
    {
        foreach (self::LEGACY as $slug => [$district, $city]) {
            $legacy = Location::where('slug', $slug)->whereNull('code')->first();

            if (! $legacy) {
                continue;
            }

            $target = Location::where('name', $district)->where('city', $city)->firstOrFail();

            Listing::withTrashed()->where('location_id', $legacy->id)->update(['location_id' => $target->id]);
            $legacy->delete();
        }
    }
}
