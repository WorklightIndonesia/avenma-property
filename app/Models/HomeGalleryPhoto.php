<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One photo picked for the home hero gallery, in the order the admin chose.
 */
class HomeGalleryPhoto extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'listing_image_id',
        'sort_order',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(ListingImage::class, 'listing_image_id');
    }
}
