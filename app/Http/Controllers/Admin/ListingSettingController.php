<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeveloperRequest;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\ListingOption;
use App\Support\Slug;
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
            'options' => collect(ListingOption::GROUPS)
                ->mapWithKeys(fn ($label, $group) => [
                    $group => ListingOption::query()->where('group', $group)->orderBy('label')->get(),
                ]),
            'developers' => Developer::query()->withCount('listings')->orderBy('name')->get(),
        ]);
    }

    public function storeDeveloper(DeveloperRequest $request): RedirectResponse
    {
        $developer = new Developer($request->validated());
        Slug::save($developer, $request->validated()['name'], 'developer');

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

        $option = new ListingOption([
            'group' => $data['group'],
            'label' => $data['label'],
        ]);

        Slug::save($option, $data['label'], 'opsi', ['group' => $data['group']]);

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
}
