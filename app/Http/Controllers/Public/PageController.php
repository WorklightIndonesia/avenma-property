<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\HomeGalleryPhoto;
use App\Models\KprSetting;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\SiteSetting;
use Illuminate\View\View;

/**
 * The three public pages are fixed routes. Their copy lives in page_contents
 * and page_sections; the order of the sections comes from config/pages.php.
 */
class PageController extends Controller
{
    public function home(): View
    {
        $listings = Listing::query()
            ->with(['location', 'developer', 'images'])
            ->published()
            ->orderByDesc('is_featured')
            ->latest()
            ->take(Listing::HOME_LIMIT)
            ->get();

        // The hero shows the photos the admin picked, in that order. With none
        // picked it falls back to the covers of the listings shown below, so the
        // page stays stable instead of shuffling on every visit.
        $heroSlides = HomeGalleryPhoto::query()
            ->with('image.listing')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (HomeGalleryPhoto $photo) => [
                'url' => route('public.listings.show', $photo->image->listing),
                'image' => asset($photo->image->path),
                'alt' => $photo->image->alt ?: $photo->image->listing->title,
                'label' => $photo->image->listing->title,
            ]);

        if ($heroSlides->isEmpty()) {
            $heroSlides = $listings
                ->filter(fn (Listing $listing) => $listing->images->isNotEmpty())
                ->map(fn (Listing $listing) => [
                    'url' => route('public.listings.show', $listing),
                    'image' => asset($listing->images->first()->path),
                    'alt' => $listing->images->first()->alt ?: $listing->title,
                    'label' => $listing->title,
                ])
                ->values();
        }

        // Home leaves the price blank so the visitor types their own budget;
        // rate and DP come from kpr_settings.
        $kprSettings = KprSetting::query()->first();

        $kpr = [
            'price' => 0,
            'rate' => (float) ($kprSettings?->interest_rate ?? 7.5),
            'minDp' => (float) ($kprSettings?->min_dp_percent ?? 20),
            'maxDp' => 70,
            'maxTenor' => (int) ($kprSettings?->max_tenor_years ?? 20),
        ];

        $recentPosts = BlogPost::query()
            ->with('creator')
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return $this->render('home', [
            'listings' => $listings,
            'heroSlides' => $heroSlides,
            'kpr' => $kpr,
            'recentPosts' => $recentPosts,
        ]);
    }

    public function contact(): View
    {
        return $this->render('kontak');
    }

    public function terms(): View
    {
        return $this->render('syarat-ketentuan');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function render(string $key, array $extra = []): View
    {
        $page = PageContent::query()->with('sections')->where('key', $key)->firstOrFail();

        return view('public.pages.show', array_merge([
            'page' => $page,
            'sections' => config("pages.$key.sections"),
            'settings' => SiteSetting::current(),
            'pageKey' => $key,
        ], $extra));
    }
}
