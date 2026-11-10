<?php

namespace App\Models;

use App\Support\RecordsActivity;
use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'whatsapp',
        'email',
        'address',
        'maps_url',
        'maps_lat',
        'maps_lng',
        'instagram',
        'threads',
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
        return WhatsApp::url($this->whatsapp);
    }

    public function instagramUrl(): ?string
    {
        $handle = trim((string) $this->instagram);

        return $handle === '' ? null : 'https://instagram.com/'.$handle;
    }

    public function threadsUrl(): ?string
    {
        $handle = trim((string) $this->threads);

        return $handle === '' ? null : 'https://www.threads.com/@'.$handle;
    }
}
