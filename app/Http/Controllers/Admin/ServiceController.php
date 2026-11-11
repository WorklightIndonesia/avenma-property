<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The service rows behind /layanan. The page skeleton (hero, the "Layanan
 * lain" block) stays in the views; this screen owns the list itself.
 */
class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'serviceGroups' => collect(Service::CATEGORIES)
                ->map(fn (string $label, string $key) => [
                    'key' => $key,
                    'label' => $label,
                    'services' => Service::query()->where('category', $key)->ordered()->get(),
                ])
                ->values(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', ['service' => new Service]);
    }

    public function store(Request $request): RedirectResponse
    {
        $service = new Service($this->validated($request));
        $service->sort_order = (int) Service::query()->max('sort_order') + 1;
        $service->save();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->fill($this->validated($request))->save();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan dihapus.');
    }

    /**
     * Swaps the row with its neighbour. The list is short, so a full reorder
     * screen would cost more than two buttons.
     */
    public function move(Service $service, string $direction): RedirectResponse
    {
        $neighbour = Service::query()
            ->where('category', $service->category)
            ->when($direction === 'up', fn ($query) => $query
                ->where('sort_order', '<', $service->sort_order)
                ->orderByDesc('sort_order'))
            ->when($direction === 'down', fn ($query) => $query
                ->where('sort_order', '>', $service->sort_order)
                ->orderBy('sort_order'))
            ->first();

        if ($neighbour !== null) {
            [$service->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $service->sort_order];
            $service->save();
            $neighbour->save();
        }

        return redirect()->route('admin.services.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $valid = $request->validate([
            'category' => ['nullable', Rule::in(array_keys(Service::CATEGORIES))],
            'title' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'message' => ['nullable', 'string'],
        ], [
            'title.required' => 'Nama layanan wajib diisi.',
            'description.required' => 'Penjelasan layanan wajib diisi.',
        ]);

        $valid['category'] ??= 'properti';

        return $valid;
    }
}
