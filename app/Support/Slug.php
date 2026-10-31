<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * One rule for turning a title into a unique web address, so the four screens
 * that save one cannot drift apart.
 */
class Slug
{
    /**
     * Regenerated on every save; duplicates get -2, -3, and so on.
     *
     * @param  array<string, mixed>  $scope  extra columns the slug is unique within
     */
    public static function unique(string $model, string $title, string $fallback, ?int $ignoreId = null, array $scope = []): string
    {
        $base = Str::slug($title) ?: $fallback;
        $slug = $base;
        $suffix = 2;

        while (self::taken($model, $slug, $ignoreId, $scope)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $scope
     */
    private static function taken(string $model, string $slug, ?int $ignoreId, array $scope): bool
    {
        $query = $model::query()->where('slug', $slug)->where($scope);

        // Only a soft-deleting model keeps tombstones; a listing that comes
        // back must keep the address it was published under.
        if (in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
