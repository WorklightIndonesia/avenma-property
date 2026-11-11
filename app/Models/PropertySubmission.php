<?php

namespace App\Models;

use App\Support\RecordsActivity;
use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A property an owner offers to Avenma for sale or rent. The admin reads it,
 * contacts the owner, and may turn it into a listing.
 */
class PropertySubmission extends Model
{
    use RecordsActivity;

    /** Percent of the transaction price, fixed by the client. */
    public const COMMISSION_RATES = ['jual' => 2.5, 'sewa' => 5.0];

    /** The value that lets the owner type a type or document we do not list. */
    public const OTHER = 'lainnya';

    public const OFFERS = ['jual' => 'Jual', 'sewa' => 'Sewa'];

    public const STATUSES = ['baru' => 'Baru', 'dihubungi' => 'Dihubungi', 'selesai' => 'Selesai'];

    public const FACINGS = [
        'utara' => 'Utara',
        'timur' => 'Timur',
        'selatan' => 'Selatan',
        'barat' => 'Barat',
        'tidak_tahu' => 'Tidak tahu',
    ];

    protected $fillable = [
        'owner_name',
        'phone',
        'property_type',
        'property_type_other',
        'address',
        'offer',
        'land_area',
        'building_area',
        'bedrooms',
        'bathrooms',
        'facing',
        'certificate',
        'certificate_other',
        'price',
        'commission_rate',
        'status',
        'listing_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'land_area' => 'integer',
            'building_area' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'commission_rate' => 'float',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function commissionAmount(): int
    {
        return (int) round($this->price * $this->commission_rate / 100);
    }

    /** "2,5%" — the comma is the Indonesian decimal mark. */
    public static function commissionLabel(string $offer): string
    {
        return str_replace('.', ',', (string) (self::COMMISSION_RATES[$offer] ?? 0)).'%';
    }

    public function offerLabel(): string
    {
        return self::OFFERS[$this->offer] ?? ucfirst((string) $this->offer);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function typeLabel(): string
    {
        if ($this->property_type === self::OTHER && filled($this->property_type_other)) {
            return $this->property_type_other;
        }

        return ListingOption::options('property_type')[$this->property_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->property_type));
    }

    public function facingLabel(): ?string
    {
        return self::FACINGS[$this->facing] ?? null;
    }

    public function certificateLabel(): ?string
    {
        if ($this->certificate === self::OTHER && filled($this->certificate_other)) {
            return $this->certificate_other;
        }

        return ListingOption::options('certificate')[$this->certificate] ?? null;
    }

    public function whatsappUrl(): ?string
    {
        return WhatsApp::url(
            $this->phone,
            'Halo '.$this->owner_name.', saya dari Avenma Properti. Terima kasih sudah menitipkan properti di '.$this->address.'. Boleh saya tanyakan beberapa detail lagi?'
        );
    }

    /**
     * Admin table lookup. Text matches part of the owner name or the address;
     * blank and unknown values are ignored, like the other admin filters.
     */
    public function scopeSearch(Builder $query, array $filters): Builder
    {
        $text = is_string($filters['q'] ?? null) ? trim($filters['q']) : '';

        return $query
            ->when($text !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('owner_name', 'like', "%{$text}%")
                ->orWhere('address', 'like', "%{$text}%")))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['offer'] ?? null, fn (Builder $q, string $offer) => $q->where('offer', $offer));
    }
}
