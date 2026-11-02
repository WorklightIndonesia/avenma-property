<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingRoom extends Model
{
    /** The denah is drawn on a fixed grid, so every plan keeps the same proportions. */
    public const COLS = 12;

    public const ROWS = 16;

    /** Upper bound on one listing's denah, so the editor stays usable. */
    public const MAX = 24;

    public const TONES = [
        'biasa' => 'Biasa',
        'utama' => 'Ruang utama',
        'luar' => 'Luar (carport / taman)',
    ];

    protected $fillable = ['floor', 'label', 'x', 'y', 'w', 'h', 'tone'];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'x' => 'integer',
            'y' => 'integer',
            'w' => 'integer',
            'h' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
