<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\SiteSetting;
use Illuminate\View\View;

/**
 * The four public pages are fixed routes. Their copy lives in page_contents
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

        return $this->render('home', ['listings' => $listings]);
    }

    public function about(): View
    {
        return $this->render('tentang-kami');
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
