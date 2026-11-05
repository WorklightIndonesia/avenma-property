<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Agent extends Model
{
    /** Shown when a listing has no agent, and the photo used in its place. */
    public const FALLBACK_NAME = 'Avenma Properti';

    public const FALLBACK_PHOTO = 'images/brand/avenma-mark-paper-112.png';

    protected $fillable = [
        'name',
        'title',
        'phone',
        'email',
        'photo_path',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * The card is a 56px circle, so the 480px variant is plenty. Seeded photos
     * that were already smaller than that have no variant of their own.
     */
    public function photoUrl(): string
    {
        if (! $this->photo_path) {
            return asset(self::FALLBACK_PHOTO);
        }

        $thumb = ImageService::thumbPath($this->photo_path);

        return asset(Storage::disk('uploads')->exists($thumb) ? $thumb : $this->photo_path);
    }
}
