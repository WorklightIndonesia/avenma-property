<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeveloperRequest;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        $developers = Developer::query()
            ->withCount('listings')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.developers.index', ['developers' => $developers]);
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
