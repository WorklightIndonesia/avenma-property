<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\ListingOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The option lists behind a listing. Developers live in their own table; the
 * three enum fields are extended with rows in listing_options.
 */
class ListingSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.listing-settings.index', [
            'groups' => ListingOption::GROUPS,
            'custom' => collect(ListingOption::GROUPS)
                ->mapWithKeys(fn ($label, $group) => [$group => ListingOption::custom($group)]),
            'options' => collect(ListingOption::GROUPS)
                ->mapWithKeys(fn ($label, $group) => [$group => ListingOption::options($group)]),
            'developers' => Developer::query()->withCount('listings')->orderBy('name')->get(),
        ]);
    }

    public function storeDeveloper(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama developer wajib diisi.',
        ]);

        $developer = new Developer($data);
        $developer->slug = $this->developerSlug($data['name']);
        $developer->save();

        return back()->with('status', "Developer \"{$developer->name}\" ditambahkan.");
    }

    public function destroyDeveloper(Developer $developer): RedirectResponse
    {
        $used = $developer->listings()->withTrashed()->count();

        if ($used > 0) {
            return back()->withErrors([
                'option' => "Developer {$developer->name} dipakai {$used} listing. Pindahkan listing itu dulu.",
            ]);
        }

        $developer->delete();

        return back()->with('status', 'Developer dihapus.');
    }

    public function storeOption(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(array_keys(ListingOption::GROUPS))],
            'label' => ['required', 'string', 'max:60'],
        ], [
            'label.required' => 'Nama opsi wajib diisi.',
        ]);

        $slug = Str::slug($data['label']);

        if ($slug === '') {
            return back()->withErrors(['label' => 'Nama opsi harus mengandung huruf atau angka.']);
        }

        if (array_key_exists($slug, ListingOption::options($data['group']))) {
            return back()->withErrors(['label' => "Opsi \"{$data['label']}\" sudah ada."]);
        }

        ListingOption::create([
            'group' => $data['group'],
            'label' => $data['label'],
            'slug' => $slug,
        ]);

        return back()->with('status', "Opsi \"{$data['label']}\" ditambahkan.");
    }

    public function destroyOption(ListingOption $option): RedirectResponse
    {
        $column = $option->group;

        $used = Listing::withTrashed()->where($column, $option->slug)->count();

        if ($used > 0) {
            return back()->withErrors([
                'option' => "Opsi {$option->label} dipakai {$used} listing. Pindahkan listing itu dulu.",
            ]);
        }

        $option->delete();

        return back()->with('status', 'Opsi dihapus.');
    }

    /**
     * Regenerated on every save; duplicates get -2, -3, and so on.
     */
    private function developerSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'developer';
        $slug = $base;
        $suffix = 2;

        while (Developer::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
