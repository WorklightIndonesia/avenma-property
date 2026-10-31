<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'whatsapp',
        'email',
        'address',
        'maps_url',
        'maps_lat',
        'maps_lng',
        'instagram',
    ];

    protected function casts(): array
    {
        return [
            'maps_lat' => 'float',
            'maps_lng' => 'float',
        ];
    }

    /**
     * One row per site. A new instance is returned before the first save so
     * every caller can read the properties without a null check.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new self;
    }

    /**
     * Named apart from the `whatsapp` column: a method called whatsapp() makes
     * Eloquent treat $setting->whatsapp as a relationship.
     */
    public static function whatsappNumber(): ?string
    {
        return static::query()->value('whatsapp');
    }

    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return strlen($digits) >= 10 ? 'https://wa.me/'.$digits : null;
    }

    public function instagramUrl(): ?string
    {
        $handle = trim((string) $this->instagram);

        return $handle === '' ? null : 'https://instagram.com/'.$handle;
    }
}
