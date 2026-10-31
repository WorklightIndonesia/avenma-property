<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeGalleryPhoto;
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

        $isHome = $page->key === 'home';

        return view('admin.pages.edit', [
            'page' => $page,
            'skeleton' => config("pages.{$page->key}.sections"),
            'sections' => $page->sections->keyBy('key'),
            'galleryImages' => $isHome
                ? ListingImage::query()->with('listing')->orderBy('listing_id')->orderBy('sort_order')->get()
                : collect(),
            'galleryIds' => $isHome
                ? HomeGalleryPhoto::query()->orderBy('sort_order')->pluck('listing_image_id')->all()
                : [],
        ]);
    }

    public function update(Request $request, PageContent $page): RedirectResponse
    {
        abort_unless(array_key_exists($page->key, config('pages')), 404);

        $skeleton = config("pages.{$page->key}.sections");
        $imageKeys = collect($skeleton)->filter(fn ($meta) => $meta['image'] ?? false)->keys();

        $data = $request->validate($this->rules($skeleton, $imageKeys), [
            'sections.*.cta_url.regex' => 'Tujuan tombol harus diawali http://, https://, atau / untuk alamat di situs ini.',
            'sections.*.cta_url_2.regex' => 'Tujuan tombol kedua harus diawali http://, https://, atau / untuk alamat di situs ini.',
            'sections.*.image.mimetypes' => 'Gambar harus JPEG, PNG, atau WebP.',
            'sections.*.image.max' => 'Ukuran gambar maksimal 5 MB.',
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
                    $this->storeImage($section, $request->file("sections.$key.image"), $request->boolean("sections.$key.remove_image"));
                    $section->image_alt = $input['image_alt'] ?? null;
                }

                $section->page_content_id = $page->id;
                $section->save();
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
            'gallery' => ['nullable', 'array'],
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
                    "sections.$key.image_alt" => 'Alt text wajib diisi selama ada gambar.',
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

    private function storeImage(PageSection $section, $file, bool $remove): void
    {
        if ($file === null && ! $remove) {
            return;
        }

        if (filled($section->image)) {
            Storage::disk('uploads')->delete($section->image);
        }

        $section->image = $file ? $this->images->store($file, 'images/pages/'.$section->page_content_id) : null;
    }
}
