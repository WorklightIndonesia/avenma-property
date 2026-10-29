<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeveloperRequest;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['nama']);
        $sort = (string) $request->query('urut');
        $name = trim((string) ($filters['nama'] ?? ''));

        $developers = Developer::query()
            ->withCount('listings')
            ->when($name !== '', fn ($query) => $query->where('name', 'like', "%{$name}%"))
            ->when(in_array($sort, ['tersedikit', 'terbanyak'], true), fn ($query) => $query
                ->orderBy('listings_count', $sort === 'terbanyak' ? 'desc' : 'asc')
                ->orderBy('name'), fn ($query) => $query->orderBy('name'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.developers.index', [
            'developers' => $developers,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    public function create(): View
    {
        return view('admin.developers.create', ['developer' => new Developer]);
    }

    public function store(DeveloperRequest $request): RedirectResponse
    {
        $developer = new Developer($request->validated());
        $developer->slug = $this->slug($request->validated()['name']);
        $developer->save();

        return redirect()
            ->route('admin.developers.index')
            ->with('status', 'Developer dibuat.');
    }

    public function edit(Developer $developer): View
    {
        return view('admin.developers.edit', ['developer' => $developer]);
    }

    public function update(DeveloperRequest $request, Developer $developer): RedirectResponse
    {
        $developer->fill($request->validated());
        $developer->slug = $this->slug($request->validated()['name'], $developer);
        $developer->save();

        return redirect()
            ->route('admin.developers.index')
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Developer $developer): RedirectResponse
    {
        // developer_id is nullOnDelete in the schema, so the database would
        // silently unassign listings instead of refusing the delete.
        $count = $developer->listings()->count();

        if ($count > 0) {
            return back()->withErrors([
                'developer' => "Developer masih dipakai {$count} listing. Pindahkan atau hapus listing itu dulu.",
            ]);
        }

        $developer->delete();

        return redirect()
            ->route('admin.developers.index')
            ->with('status', 'Developer dihapus.');
    }

    /**
     * Regenerated on every save; duplicates get -2, -3, and so on.
     */
    private function slug(string $name, ?Developer $developer = null): string
    {
        $base = Str::slug($name) ?: 'developer';
        $slug = $base;
        $suffix = 2;

        while (Developer::query()
            ->where('slug', $slug)
            ->when($developer, fn ($query) => $query->whereKeyNot($developer->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
