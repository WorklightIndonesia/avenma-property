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
        // the ones that exist. The row also carries the copy's edit time.
        $pages = PageContent::query()
            ->whereIn('key', array_keys(config('pages')))
            ->get(['key', 'updated_at'])
            ->keyBy('key');

        foreach (config('pages') as $key => $page) {
            $row = $pages->get($key);

            if (! $row) {
                continue;
            }

            $urls[] = [
                'loc' => url($page['path']),
                'lastmod' => $row->updated_at?->toAtomString(),
                'changefreq' => $key === 'home' ? 'weekly' : 'monthly',
                'priority' => $key === 'home' ? '1.0' : '0.6',
            ];
        }

        $listings = Listing::query()->published()->get();
        $posts = BlogPost::query()->published()->get();

        // The index pages change whenever their newest entry does, so they
        // carry the newest stamp instead of none.
        $urls[] = [
            'loc' => route('public.listings.index'),
            'lastmod' => $listings->max('updated_at')?->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('public.blog.index'),
            'lastmod' => $posts->max('updated_at')?->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];

        // The services live in config/layanan.php, so there is no row to date.
        $urls[] = ['loc' => route('public.layanan.index'), 'changefreq' => 'monthly', 'priority' => '0.7'];

        foreach ($listings as $listing) {
            $urls[] = [
                'loc' => route('public.listings.show', $listing),
                'lastmod' => $listing->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('public.blog.show', $post),
                // Edits matter to a crawler: published_at stays in the past
                // after the article is rewritten.
                'lastmod' => $post->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return response()
            ->view('public.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Generated instead of a static file because APP_URL is still open; the
     * Sitemap line must be absolute.
     */
    public function robots(): Response
    {
        return response(
            "User-agent: *\nDisallow: /admin\n\nSitemap: ".route('public.sitemap')."\n",
        )->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
