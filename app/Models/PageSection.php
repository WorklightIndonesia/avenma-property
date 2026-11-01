<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Views render this column raw, so every writer — not only the admin
     * controller — has to go through the sanitizer.
     */
    protected function body(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => HtmlSanitizer::clean($value));
    }

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
