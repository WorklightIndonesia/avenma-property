<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\PageContent;
use App\Models\PageSection;
use App\Services\ImageService;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admin-only copy editing. The page skeleton (which sections exist, in what
 * order, and which one carries an image) comes from config/pages.php; this
 * controller never creates or reorders sections.
 */
class PageContentController extends Controller
{
    /** Slides beyond this are unusable in the hero and slow the home page down. */
    public const MAX_GALLERY = 6;

    public function __construct(private readonly ImageService $images) {}

    public function index(): View
    {
        return view('admin.pages.index', [
            'skeleton' => config('pages'),
            'pages' => PageContent::query()->withCount('sections')->get()->keyBy('key'),
        ]);
    }

    public function edit(PageContent $page): View
    {
        abort_unless(array_key_exists($page->key, config('pages')), 404);

        $page->load('sections');

        return view('admin.pages.edit', [
            'page' => $page,
            'skeleton' => config("pages.{$page->key}.sections"),
            'sections' => $page->sections->keyBy('key'),
            'galleries' => $page->key === 'home' ? $this->gallery() : null,
        ]);
    }

    /**
     * The hero picker, one listing per page: every photo of every listing at
     * once was a heavy query and a huge admin page. The picks travel in the
     * query string, because paging reloads the document.
     *
     * @return array<string, mixed>
     */
    private function gallery(): array
    {
        $query = request()->query('q');
        $search = is_string($query) ? trim($query) : '';

        $paginator = Listing::query()
            ->whereHas('images')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('images', fn ($images) => $images
                    ->where('alt', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%"))))
            ->with('images')
            ->orderByDesc('id')
            ->paginate(1, ['*'], 'galeri_page')
            ->withQueryString();

        $listing = $paginator->first();
        $picked = $this->pickedPhotos();
        $onPage = $listing?->images->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
        $others = array_values(array_diff($picked, $onPage));

        // Picks made on other pages come first, then every photo of this page:
        // a chip exists for all of them, hidden until it is ticked, so the strip
        // can show a pick without the script building markup.
        $carried = ListingImage::query()
            ->with('listing:id,title')
            ->whereIn('id', $others)
            ->get()
            ->sortBy(fn (ListingImage $image) => array_search($image->id, $others, true))
            ->map(fn (ListingImage $image) => $this->chip($image))
            // Plain collection: merging an Eloquent one walks the models by key.
            ->toBase();

        return [
            'listing' => $listing,
            'paginator' => $paginator,
            'search' => $search,
            'max' => self::MAX_GALLERY,
            // Picks made on other pages, carried as hidden fields so saving this
            // page does not drop them.
            'others' => $others,
            'picked' => $picked,
            'chips' => $carried
                ->merge(($listing?->images ?? collect())->map(fn (ListingImage $image) => $this->chip($image, $listing)))
                ->values(),
        ];
    }

    /**
     * @return array{id: int, src: string, title: string, label: string}
     */
    private function chip(ListingImage $image, ?Listing $listing = null): array
    {
        return [
            'id' => (int) $image->id,
            'src' => $image->path,
            'title' => $listing?->title ?? $image->listing?->title ?? 'Properti',
            'label' => $image->label ?: ($image->alt ?: 'Tanpa label'),
        ];
    }

    /**
     * What the picker starts with. A failed save wins over the URL, so nothing
     * ticked is lost to the redirect.
     *
     * @return list<int>
     */
    private function pickedPhotos(): array
    {
        if (old('gallery') !== null) {
            return $this->existingPicks(collect(old('gallery'))->map(fn ($id) => (int) $id)->unique()->values()->all());
        }

        // No `pilih` means "show what is saved"; an empty one is a selection the
        // admin cleared on purpose.
        if (! request()->query->has('pilih')) {
            return $this->existingPicks(HomeGalleryPhoto::query()
                ->orderBy('sort_order')
                ->pluck('listing_image_id')
                ->map(fn ($id) => (int) $id)
                ->all());
        }

        $query = request()->query('pilih');

        return $this->existingPicks(collect(explode(',', is_string($query) ? $query : ''))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all());
    }

    /**
     * Drops picks whose photo is gone, so a stale link cannot post an id that
     * fails validation with no message on the page.
     *
     * @param  list<int>  $picked
     * @return list<int>
     */
    private function existingPicks(array $picked): array
    {
        if ($picked === []) {
            return [];
        }

        $existing = ListingImage::query()
            ->whereIn('id', $picked)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_intersect($picked, $existing));
    }

    public function update(Request $request, PageContent $page): RedirectResponse
    {
        abort_unless(array_key_exists($page->key, config('pages')), 404);

        $skeleton = config("pages.{$page->key}.sections");
        $imageKeys = collect($skeleton)->filter(fn ($meta) => $meta['image'] ?? false)->keys();

        $data = $request->validate($this->rules($skeleton, $imageKeys), [
            'sections.*.cta_url.regex' => 'Tulis alamat lengkap (https://...) atau bagian situs ini, misalnya /properti.',
            'sections.*.cta_url_2.regex' => 'Tulis alamat lengkap (https://...) atau bagian situs ini, misalnya /kontak.',
            'sections.*.image.mimetypes' => 'Gambar harus JPEG, PNG, atau WebP.',
            'sections.*.image.max' => 'Ukuran gambar maksimal 5 MB.',
            'gallery.max' => 'Galeri beranda maksimal '.self::MAX_GALLERY.' foto. Lepas beberapa dulu.',
        ]);

        $this->requireAltText($request, $page, $imageKeys);

        DB::transaction(function () use ($request, $page, $data, $skeleton, $imageKeys) {
            $page->fill([
                'title' => $data['title'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ])->save();

            if ($page->key === 'home') {
                $this->syncGallery($data['gallery'] ?? []);
            }

            $order = 0;

            foreach (array_keys($skeleton) as $key) {
                $input = $data['sections'][$key] ?? [];
                $section = $page->sections()->firstOrNew(['key' => $key]);

                $section->fill([
                    'heading' => $input['heading'] ?? null,
                    'body' => HtmlSanitizer::clean($input['body'] ?? null),
                    'cta_label' => $input['cta_label'] ?? null,
                    'cta_url' => $input['cta_url'] ?? null,
                    'cta_label_2' => $input['cta_label_2'] ?? null,
                    'cta_url_2' => $input['cta_url_2'] ?? null,
                    'sort_order' => $order++,
                ]);

                if ($imageKeys->contains($key)) {
                    $previousImage = $this->storeImage($section, $request->file("sections.$key.image"), $request->boolean("sections.$key.remove_image"));
                    $section->image_alt = $input['image_alt'] ?? null;
                } else {
                    $previousImage = null;
                }

                $section->page_content_id = $page->id;
                $section->save();

                // Deleted only after the new path is saved, so a failed write
                // cannot leave the section without an image.
                if ($previousImage !== null && $previousImage !== $section->image) {
                    Storage::disk('uploads')->delete($previousImage);
                }
            }
        });

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Halaman disimpan.');
    }

    /**
     * @param  array<string, array<string, mixed>>  $skeleton
     * @param  Collection<int, string>  $imageKeys
     * @return array<string, mixed>
     */
    private function rules(array $skeleton, $imageKeys): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'gallery' => ['nullable', 'array', 'max:'.self::MAX_GALLERY],
            'gallery.*' => ['integer', 'exists:listing_images,id'],
        ];

        foreach (array_keys($skeleton) as $key) {
            $rules["sections.$key.heading"] = ['nullable', 'string', 'max:255'];
            $rules["sections.$key.body"] = ['nullable', 'string'];

            $buttons = (int) ($skeleton[$key]['cta'] ?? 0);

            if ($buttons >= 1) {
                $rules["sections.$key.cta_label"] = ['nullable', 'string', 'max:60'];
                $rules["sections.$key.cta_url"] = ['nullable', 'string', 'regex:/^(https?:\/\/|\/)/i', 'max:2048'];
            }

            if ($buttons >= 2) {
                $rules["sections.$key.cta_label_2"] = ['nullable', 'string', 'max:60'];
                $rules["sections.$key.cta_url_2"] = ['nullable', 'string', 'regex:/^(https?:\/\/|\/)/i', 'max:2048'];
            }
        }

        foreach ($imageKeys as $key) {
            $rules["sections.$key.image"] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'];
            $rules["sections.$key.image_alt"] = ['nullable', 'string', 'max:255'];
            $rules["sections.$key.remove_image"] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    /**
     * @param  Collection<int, string>  $imageKeys
     */
    private function requireAltText(Request $request, PageContent $page, $imageKeys): void
    {
        foreach ($imageKeys as $key) {
            $existing = $page->sections->firstWhere('key', $key);
            $kept = filled($existing?->image) && ! $request->boolean("sections.$key.remove_image");

            if (($request->hasFile("sections.$key.image") || $kept) && blank($request->input("sections.$key.image_alt"))) {
                throw ValidationException::withMessages([
                    "sections.$key.image_alt" => 'Teks alternatif wajib diisi selama ada gambar.',
                ]);
            }
        }
    }

    /**
     * Replaces the hero gallery with the checked photos, keeping the order the
     * admin picked them in.
     *
     * @param  list<int|string>  $ids
     */
    private function syncGallery(array $ids): void
    {
        $selected = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();

        // A text-only save can post the same picks in another order; leaving
        // the stored order alone stops the hero from reshuffling.
        $stored = HomeGalleryPhoto::query()
            ->orderBy('sort_order')
            ->pluck('listing_image_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (collect($stored)->sort()->values()->all() === $selected->sort()->values()->all()) {
            return;
        }

        // Nothing checked means "use the fallback", so every picked photo goes.
        HomeGalleryPhoto::query()
            ->when($selected->isNotEmpty(), fn ($query) => $query->whereNotIn('listing_image_id', $selected->all()))
            ->delete();

        foreach ($selected as $position => $imageId) {
            HomeGalleryPhoto::updateOrCreate(
                ['listing_image_id' => $imageId],
                ['sort_order' => $position],
            );
        }
    }

    /**
     * Stores the new file and reports the path it replaced, which the caller
     * deletes once the row is saved.
     */
    private function storeImage(PageSection $section, $file, bool $remove): ?string
    {
        if ($file === null && ! $remove) {
            return null;
        }

        $previous = filled($section->image) ? $section->image : null;

        $section->image = $file ? $this->images->store($file, 'images/pages/'.$section->page_content_id) : null;

        return $previous;
    }
}
