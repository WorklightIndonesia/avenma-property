<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KprSetting extends Model
{
    protected $fillable = [
        'interest_rate',
        'min_dp_percent',
        'max_tenor_years',
    ];

    protected function casts(): array
    {
        return [
            'interest_rate' => 'decimal:2',
            'min_dp_percent' => 'decimal:2',
        ];
    }
}
