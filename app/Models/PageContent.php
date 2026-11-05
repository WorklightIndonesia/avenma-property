<?php

namespace App\Models;

use App\Support\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageContent extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'key',
        'title',
        'title_en',
        'content',
        'content_en',
        'meta_title',
        'meta_description',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function section(string $key): ?PageSection
    {
        return $this->sections->firstWhere('key', $key);
    }
}
