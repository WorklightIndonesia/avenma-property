<?php

namespace App\Models;

use App\Support\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

/**
 * The choices behind a listing field. Every option, built-in or added by hand,
 * is a row here, so an unused one can be removed from Pengaturan Listing.
 */
class ListingOption extends Model
{
    use RecordsActivity;

    public const GROUPS = [
        'property_type' => 'Tipe properti',
        'listing_type' => 'Jenis penawaran',
        'status' => 'Status',
        'water_source' => 'Air',
        'electricity_power' => 'Daya listrik',
        'certificate' => 'Legalitas',
    ];

    protected $fillable = [
        'group',
        'label',
        'slug',
    ];

    /**
     * @return array<string, string> value => label
     */
    public static function options(string $group): array
    {
        $options = static::query()
            ->where('group', $group)
            ->orderBy('label')
            ->pluck('label', 'slug')
            ->all();

        // Capacities read as numbers, so 900 comes before 1.300, not after it.
        if ($group === 'electricity_power') {
            uksort($options, fn ($a, $b) => (int) $a <=> (int) $b);
        }

        return $options;
    }
}
