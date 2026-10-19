<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = [
        'key',
        'title',
        'title_en',
        'content',
        'content_en',
        'meta_title',
        'meta_description',
    ];
}
