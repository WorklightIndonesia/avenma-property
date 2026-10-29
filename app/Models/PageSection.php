<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = [
        'key',
        'heading',
        'body',
        'image',
        'image_alt',
        'cta_label',
        'cta_url',
        'cta_label_2',
        'cta_url_2',
        'sort_order',
    ];

    public function pageContent(): BelongsTo
    {
        return $this->belongsTo(PageContent::class);
    }

    public function isEmpty(): bool
    {
        return trim((string) $this->heading) === ''
            && trim((string) $this->body) === ''
            && blank($this->image)
            && blank($this->cta_label);
    }
}
