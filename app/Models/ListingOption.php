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
        return static::query()
            ->where('group', $group)
            ->orderBy('label')
            ->pluck('label', 'slug')
            ->all();
    }
}
