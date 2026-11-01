<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'alt',
        'label',
        'sort_order',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** The 480px strip variant when it exists, else the original file. */
    public function getThumbPathAttribute(): string
    {
        return $this->variantOrOriginal(ImageService::thumbPath((string) $this->path));
    }

    /** The 960px card and hero variant when it exists, else the original. */
    public function getMediumPathAttribute(): string
    {
        return $this->variantOrOriginal(ImageService::mediumPath((string) $this->path));
    }

    /**
     * Older rows have no variant until the backfill runs, so a missing file
     * must fall back to the original rather than render a broken image.
     */
    private function variantOrOriginal(string $variant): string
    {
        if ($this->path === null || $this->path === '' || ! Storage::disk('uploads')->exists($variant)) {
            return (string) $this->path;
        }

        return $variant;
    }
}
