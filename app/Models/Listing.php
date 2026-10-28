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

    public const LISTING_TYPES = ['primary' => 'Primer', 'secondary' => 'Sekunder'];

    public const STATUSES = ['dijual' => 'Dijual', 'disewa' => 'Disewakan'];

    public const NEW_DAYS = 30;

    public const PER_PAGE_OPTIONS = [6, 12, 24];

    protected $fillable = [
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
        'floors',
        'address',
        'latitude',
        'longitude',
        'contact_phone',
        'is_featured',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isNew(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gt(now()->subDays(self::NEW_DAYS));
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
     * The map iframe source. A picked point wins; without one the address text
     * is searched, which lands on the street, not the front door.
     */
    public function mapEmbedSrc(): ?string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://maps.google.com/maps?q='.$this->latitude.','.$this->longitude.'&z=17&output=embed';
        }

        $query = trim((string) $this->address);

        return $query !== '' ? 'https://maps.google.com/maps?q='.rawurlencode($query).'&output=embed' : null;
    }

    /**
     * Unknown keys and values match nothing rather than failing, so a stale URL
     * shows the empty state instead of an error. Region filters match on names:
     * a kecamatan name is unique inside its kota/kabupaten.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['provinsi'] ?? null, fn (Builder $q, string $name) => $q
                ->whereHas('location', fn (Builder $location) => $location->where('province', $name)))
            ->when($filters['kota'] ?? null, fn (Builder $q, string $name) => $q
                ->whereHas('location', fn (Builder $location) => $location->where('city', $name)))
            ->when($filters['kecamatan'] ?? null, fn (Builder $q, string $name) => $q
                ->whereHas('location', fn (Builder $location) => $location->where('name', $name)))
            ->when($filters['tipe'] ?? null, fn (Builder $q, string $type) => $q
                ->where('property_type', $type))
            ->when($filters['penawaran'] ?? null, fn (Builder $q, string $type) => $q
                ->where('listing_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q
                ->where('status', $status))
            ->when($filters['kamar'] ?? null, fn (Builder $q, string $value) => $q
                ->where('bedrooms', '>=', (int) $value))
            ->when($filters['mandi'] ?? null, fn (Builder $q, string $value) => $q
                ->where('bathrooms', '>=', (int) $value))
            ->when($filters['lt'] ?? null, fn (Builder $q, string $value) => $q
                ->where('land_area', '>=', (int) $value))
            ->when($filters['lb'] ?? null, fn (Builder $q, string $value) => $q
                ->where('building_area', '>=', (int) $value))
            ->when($filters['lantai'] ?? null, fn (Builder $q, string $value) => $q
                ->where('floors', '>=', (int) $value))
            ->when($filters['developer'] ?? null, fn (Builder $q, string $value) => $q
                ->where('developer_id', (int) $value))
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
}
