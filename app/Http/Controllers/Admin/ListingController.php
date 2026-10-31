<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Models\Developer;
use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\ListingOption;
use App\Models\User;
use App\Services\ImageService;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['properti', 'lokasi', 'status']);
        $sort = (string) $request->query('urut');

        $listings = Listing::query()
            ->with(['location', 'creator', 'images'])
            ->when($request->user()->role !== User::ROLE_ADMIN, fn ($query) => $query->where('created_by', $request->user()->id))
            ->search($filters)
            ->sorted($sort)
            ->paginate(20)
            ->withQueryString();

        return view('admin.listings.index', [
            'listings' => $listings,
            'filters' => $filters,
            'sort' => $sort,
            'featured' => $request->user()->role === User::ROLE_ADMIN ? $this->featuredSummary() : null,
            'heroListings' => $this->heroListingIds($listings->getCollection()->pluck('id')->all()),
        ]);
    }

    /**
     * Rows on this page whose photo is picked for the home hero, so the delete
     * dialog can warn before the photo disappears from the front page.
     *
     * @param  array<int, int>  $listingIds
     * @return Collection<int, int>
     */
    private function heroListingIds(array $listingIds): Collection
    {
        return HomeGalleryPhoto::query()
            ->whereHas('image', fn ($query) => $query->whereIn('listing_id', $listingIds))
            ->with('image:id,listing_id')
            ->get()
            ->pluck('image.listing_id')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * The home page shows six listings, featured ones first. When more than
     * six are flagged the oldest ones never appear, so the admin needs to see
     * which ones to release.
     *
     * @return array{count: int, shown: int, oldest: ?Listing}
     */
    private function featuredSummary(): array
    {
        $query = Listing::query()->where('is_featured', true)->published();

        return [
            'count' => (clone $query)->count(),
            'shown' => Listing::HOME_LIMIT,
            'oldest' => (clone $query)->oldest('created_at')->oldest('id')->first(),
        ];
    }

    public function create(): View
    {
        return view('admin.listings.create', ['listing' => new Listing] + $this->formOptions());
    }

    public function store(ListingRequest $request): RedirectResponse
    {
        $data = $this->withoutAdminFields($request);

        $listing = DB::transaction(function () use ($request, $data) {
            $listing = new Listing($data);
            $listing->created_by = $request->user()->id;
            // Blank means "use my own number", as agreed for agen-created listings.
            $listing->contact_phone = ($data['contact_phone'] ?? null) ?: $request->user()->phone;
            Slug::save($listing, $data['title'], 'properti');

            $this->storePhotos($listing, (array) $request->file('photos', []));

            return $listing;
        });

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->with('status', 'Listing dibuat. Isi label tiap foto di bawah.');
    }

    public function edit(Listing $listing): View
    {
        Gate::authorize('update', $listing);

        $listing->load('images');

        return view('admin.listings.edit', ['listing' => $listing] + $this->formOptions());
    }

    public function update(ListingRequest $request, Listing $listing): RedirectResponse
    {
        Gate::authorize('update', $listing);

        $data = $this->withoutAdminFields($request);

        DB::transaction(function () use ($request, $listing, $data) {
            $listing->fill($data);
            Slug::save($listing, $data['title'], 'properti');

            // Keys are image ids; the query is scoped to this listing, so a
            // posted id from another listing changes nothing.
            foreach ($data['images'] ?? [] as $id => $fields) {
                $listing->images()->whereKey($id)->update([
                    'label' => $fields['label'] ?? null,
                    'alt' => $fields['alt'] ?? null,
                    'sort_order' => $fields['sort_order'] ?? 1,
                ]);
            }

            $this->storePhotos($listing, (array) $request->file('photos', []));
        });

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        Gate::authorize('delete', $listing);

        // The photo row survives a soft delete, so the hero loses the slide.
        // An agen cannot open the Pages screen, hence the message instead of a
        // block; an admin is warned in the dialog before pressing delete.
        $inHero = $listing->hasHeroPhoto();

        $listing->delete();

        $response = redirect()
            ->route('admin.listings.index')
            ->with('status', 'Listing dihapus.');

        if ($inHero && $request->user()->role !== User::ROLE_ADMIN) {
            $response->with('warning', 'Salah satu foto di listing ini sedang digunakan pada website, laporkan pada admin agar foto dapat diganti!');
        }

        return $response;
    }

    public function destroyPhoto(Listing $listing, ListingImage $image): RedirectResponse
    {
        Gate::authorize('update', $listing);

        abort_unless($image->listing_id === $listing->id, 404);

        Storage::disk('uploads')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Foto dihapus.');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storePhotos(Listing $listing, array $files): void
    {
        $sort = (int) $listing->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $path = $this->images->store($file, 'images/listings/'.$listing->id);

            if (! $path) {
                continue;
            }

            $listing->images()->create(['path' => $path, 'sort_order' => $sort++]);
        }
    }

    /**
     * Only an admin may flag a listing as featured; agen keeps whatever the
     * admin set because the key is dropped before fill.
     *
     * @return array<string, mixed>
     */
    private function withoutAdminFields(ListingRequest $request): array
    {
        $data = $request->validated();

        if ($request->user()->role !== User::ROLE_ADMIN) {
            unset($data['is_featured']);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'developers' => Developer::orderBy('name')->pluck('name', 'id'),
            'listingTypes' => ListingOption::options('listing_type'),
            'propertyTypes' => ListingOption::options('property_type'),
            'statuses' => ListingOption::options('status'),
        ];
    }
}
