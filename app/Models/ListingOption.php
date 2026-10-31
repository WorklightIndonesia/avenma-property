<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Extra choices the admin or an agen added to a listing field. The values the
 * system ships with stay in the Listing constants; these rows extend them.
 */
class ListingOption extends Model
{
    public const GROUPS = [
        'property_type' => 'Tipe properti',
        'listing_type' => 'Jenis penawaran',
        'status' => 'Status',
    ];

    protected $fillable = [
        'group',
        'label',
        'slug',
    ];

    /**
     * System values plus the rows added in Pengaturan Listing.
     *
     * @return array<string, string> value => label
     */
    public static function options(string $group): array
    {
        $base = match ($group) {
            'property_type' => Listing::PROPERTY_TYPES,
            'listing_type' => Listing::LISTING_TYPES,
            'status' => Listing::STATUSES,
            default => [],
        };

        $custom = static::query()
            ->where('group', $group)
            ->orderBy('label')
            ->pluck('label', 'slug')
            ->all();

        return array_merge($base, $custom);
    }

    /**
     * Slugs added by hand, which are the ones that can be removed again.
     *
     * @return Collection<int, self>
     */
    public static function custom(string $group)
    {
        return static::query()->where('group', $group)->orderBy('label')->get();
    }
}
