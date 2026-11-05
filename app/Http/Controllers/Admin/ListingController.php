<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Models\Agent;
use App\Models\Developer;
use App\Models\HomeGalleryPhoto;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\ListingOption;
use App\Models\Location;
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
            ->with(['location', 'agent', 'images'])
            ->search($filters)
            ->sorted($sort)
            ->paginate(20)
            ->withQueryString();

        return view('admin.listings.index', [
            'listings' => $listings,
            'filters' => $filters,
            'sort' => $sort,
            'featured' => $this->featuredSummary(),
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
        return view('admin.listings.create', [
            'listing' => tap(new Listing)->setRelation('rooms', collect()),
            'currentLocation' => $this->currentLocation(null),
        ] + $this->formOptions());
    }

    public function store(ListingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $listing = DB::transaction(function () use ($request, $data) {
            $listing = new Listing($data);
            $listing->created_by = $request->user()->id;
            Slug::save($listing, $data['title'], 'properti');

            $this->storePhotos($listing, (array) $request->file('photos', []));
            $this->syncRooms($listing, (array) ($data['rooms'] ?? []));
            $this->storeFloorPlan($request, $listing);

            return $listing;
        });

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->withFragment('foto')
            ->with('status', 'Listing dibuat. Isi label tiap foto di bawah.');
    }

    public function edit(Listing $listing): View
    {
        Gate::authorize('update', $listing);

        $listing->load(['images', 'rooms']);

        return view('admin.listings.edit', ['listing' => $listing, 'currentLocation' => $this->currentLocation($listing)] + $this->formOptions());
    }

    public function update(ListingRequest $request, Listing $listing): RedirectResponse
    {
        Gate::authorize('update', $listing);

        $data = $request->validated();

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
            $this->syncRooms($listing, (array) ($data['rooms'] ?? []));
            $this->storeFloorPlan($request, $listing);
        });

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        Gate::authorize('delete', $listing);

        $listing->delete();

        return redirect()
            ->route('admin.listings.index')
            ->with('status', 'Listing dihapus.');
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
     * The denah is replaced whole: the editor always posts every room, so a
     * diff would cost more than it saves on a listing with a couple of rooms.
     *
     * @param  array<int, array<string, mixed>>  $rooms
     */
    private function syncRooms(Listing $listing, array $rooms): void
    {
        $listing->rooms()->delete();

        foreach ($rooms as $room) {
            $listing->rooms()->create([
                'floor' => $room['floor'],
                'label' => $room['label'],
                'x' => $room['x'],
                'y' => $room['y'],
                'w' => $room['w'],
                'h' => $room['h'],
                'tone' => $room['tone'],
            ]);
        }
    }

    /**
     * A developer's own denah image. It wins over the drawn rooms on the page,
     * so an agent uploads one when the plan already exists as a picture.
     */
    private function storeFloorPlan(ListingRequest $request, Listing $listing): void
    {
        if ($request->boolean('remove_floor_plan')) {
            $this->deleteFloorPlanFile($listing->floor_plan_path);
            $listing->floor_plan_path = null;
        }

        if ($file = $request->file('floor_plan')) {
            $this->deleteFloorPlanFile($listing->floor_plan_path);
            $listing->floor_plan_path = $this->images->store($file, 'images/floor-plans/'.$listing->id);
        }

        if ($listing->isDirty()) {
            $listing->save();
        }
    }

    private function deleteFloorPlanFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('uploads')->delete([
            $path,
            ImageService::thumbPath($path),
            ImageService::mediumPath($path),
        ]);
    }

    /**
     * The region the form opens on: the one posted back after a failed save,
     * then the listing's own row. Loaded here so the view holds no queries.
     */
    private function currentLocation(?Listing $listing): ?Location
    {
        return Location::find(old('location_id', $listing?->location_id));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'developers' => Developer::orderBy('name')->pluck('name', 'id'),
            'agents' => Agent::orderBy('name')->pluck('name', 'id'),
            'listingTypes' => ListingOption::options('listing_type'),
            'propertyTypes' => ListingOption::options('property_type'),
            'statuses' => ListingOption::options('status'),
        ];
    }
}
