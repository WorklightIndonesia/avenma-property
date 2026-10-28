<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'province',
        'city',
        'code',
        'slug',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
