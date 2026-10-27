<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'whatsapp',
    ];

    public static function whatsapp(): ?string
    {
        return static::query()->value('whatsapp');
    }
}
