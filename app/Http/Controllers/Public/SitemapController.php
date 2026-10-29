<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Listing;
use App\Models\PageContent;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        // A page in the skeleton without a row would 404, so only advertise
        // the ones that exist.
        $existing = PageContent::query()
            ->whereIn('key', array_keys(config('pages')))
            ->pluck('key');

        foreach (config('pages') as $key => $page) {
            if (! $existing->contains($key)) {
                continue;
            }

            $urls[] = [
                'loc' => url($page['path']),
                'changefreq' => $key === 'home' ? 'weekly' : 'monthly',
                'priority' => $key === 'home' ? '1.0' : '0.6',
            ];
        }

        $urls[] = ['loc' => route('public.listings.index'), 'changefreq' => 'daily', 'priority' => '0.9'];
        $urls[] = ['loc' => route('public.blog.index'), 'changefreq' => 'weekly', 'priority' => '0.7'];

        foreach (Listing::query()->published()->get() as $listing) {
            $urls[] = [
                'loc' => route('public.listings.show', $listing),
                'lastmod' => $listing->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach (BlogPost::query()->published()->get() as $post) {
            $urls[] = [
                'loc' => route('public.blog.show', $post),
                'lastmod' => ($post->published_at ?? $post->updated_at)?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return response()
            ->view('public.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
