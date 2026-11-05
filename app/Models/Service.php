<?php

namespace App\Models;

use App\Support\RecordsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'title',
        'tags',
        'description',
        'cta_label',
        'message',
        'sort_order',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Tags are stored as one comma-separated line: the list is short, and a
     * second table would only add a join to a page that reads it once.
     *
     * @return list<string>
     */
    public function tagsList(): array
    {
        return collect(explode(',', (string) $this->tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }
}
