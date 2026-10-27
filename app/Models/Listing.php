<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use SoftDeletes;

    /**
     * Price filter buckets in rupiah. One source for both the select options and the query range.
     */
    public const PRICE_BUCKETS = [
        'under-500' => ['label' => 'Di bawah Rp 500 juta', 'min' => null, 'max' => 500_000_000],
        '500-1000' => ['label' => 'Rp 500 juta – 1 miliar', 'min' => 500_000_000, 'max' => 1_000_000_000],
        '1000-2000' => ['label' => 'Rp 1 – 2 miliar', 'min' => 1_000_000_000, 'max' => 2_000_000_000],
        '2000-5000' => ['label' => 'Rp 2 – 5 miliar', 'min' => 2_000_000_000, 'max' => 5_000_000_000],
        'over-5000' => ['label' => 'Di atas Rp 5 miliar', 'min' => 5_000_000_000, 'max' => null],
    ];

    public const SORTS = [
        'terbaru' => 'Terbaru',
        'termurah' => 'Harga terendah',
        'termahal' => 'Harga tertinggi',
    ];

    public const PER_PAGE_OPTIONS = [6, 12, 24];

    protected $fillable = [
        'category_id',
        'location_id',
        'developer_id',
        'created_by',
        'title',
        'title_en',
        'slug',
        'description',
        'description_en',
        'listing_type',
        'property_type',
        'status',
        'price',
        'bedrooms',
        'bathrooms',
        'carport',
        'land_area',
        'building_area',
        'address',
        'contact_phone',
        'map_embed',
        'is_featured',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Unknown keys and values match nothing rather than failing, so a stale URL
     * shows the empty state instead of an error.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['lokasi'] ?? null, fn (Builder $q, string $slug) => $q
                ->whereHas('location', fn (Builder $location) => $location->where('slug', $slug)))
            ->when($filters['tipe'] ?? null, fn (Builder $q, string $type) => $q
                ->where('property_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q
                ->where('status', $status))
            ->when(self::PRICE_BUCKETS[$filters['harga'] ?? ''] ?? null, function (Builder $q, array $bucket) {
                $q->when($bucket['min'], fn (Builder $q, int $min) => $q->where('price', '>=', $min))
                    ->when($bucket['max'], fn (Builder $q, int $max) => $q->where('price', '<', $max));
            });
    }

    public function scopeSorted(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'termurah' => $query->orderBy('price')->orderByDesc('id'),
            'termahal' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->latest('id'),
        };
    }

    /**
     * @return array<string, string> value => label
     */
    public static function typeOptions(): array
    {
        return static::query()->distinct()->orderBy('property_type')->pluck('property_type')
            ->mapWithKeys(fn (string $type) => [$type => ucfirst($type)])
            ->all();
    }

    /**
     * @return array<string, string> value => label
     */
    public static function statusOptions(): array
    {
        return static::query()->distinct()->orderBy('status')->pluck('status')
            ->mapWithKeys(fn (string $status) => [$status => ucfirst($status)])
            ->all();
    }
}
