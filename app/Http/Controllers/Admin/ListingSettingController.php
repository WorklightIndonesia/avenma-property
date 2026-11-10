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
 * enum fields are extended with rows in listing_options.
 */
class ListingSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.listing-settings.index', [
            'groups' => ListingOption::GROUPS,
            'options' => collect(ListingOption::GROUPS)
                ->mapWithKeys(function ($label, $group) {
                    $query = ListingOption::query()->where('group', $group);

                    return [$group => $group === 'electricity_power'
                        ? $query->get()->sortBy(fn (ListingOption $option) => (int) $option->slug)->values()
                        : $query->orderBy('label')->get()];
                }),
            'developers' => Developer::query()->withCount('listings')->orderBy('name')->get(),
            'groupHints' => [
                'water_source' => 'Sumber air yang muncul di formulir listing dan filter katalog.',
                'electricity_power' => 'Kapasitas yang muncul di filter katalog. Isian daya tiap listing tetap bebas dalam VA.',
                'certificate' => 'Jenis bukti kepemilikan yang muncul di formulir listing dan filter katalog.',
            ],
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

        $slug = $data['group'] === 'electricity_power'
            ? (string) preg_replace('/\D+/', '', $data['label'])
            : Str::slug($data['label']);

        if ($slug === '' || ($data['group'] === 'electricity_power' && (int) $slug < 1)) {
            return back()->withErrors(['label' => $data['group'] === 'electricity_power'
                ? 'Nama opsi harus mengandung angka kapasitas.'
                : 'Nama opsi harus mengandung huruf atau angka.']);
        }

        if (array_key_exists($slug, ListingOption::options($data['group']))) {
            return back()->withErrors(['label' => "Opsi \"{$data['label']}\" sudah ada."]);
        }

        $option = new ListingOption([
            'group' => $data['group'],
            'label' => $data['label'],
        ]);

        // A capacity is filtered by number, so its slug must be the digits in
        // the label ("7.700 VA" → "7700"), not words joined by hyphens.
        if ($data['group'] === 'electricity_power') {
            $option->slug = $slug;
            $option->save();
        } else {
            Slug::save($option, $data['label'], 'opsi', ['group' => $data['group']]);
        }

        return back()->with('status', "Opsi \"{$data['label']}\" ditambahkan.");
    }

    /**
     * The slug is the value stored on listings, so a rename only changes the
     * label; every listing that already picked this option keeps working.
     */
    public function updateOption(Request $request, ListingOption $option): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
        ], [
            'label.required' => 'Nama opsi wajib diisi.',
        ]);

        $slug = $option->group === 'electricity_power'
            ? (string) preg_replace('/\D+/', '', $data['label'])
            : Str::slug($data['label']);

        if ($slug === '' || ($option->group === 'electricity_power' && (int) $slug < 1)) {
            return back()->withErrors(['option_label' => $option->group === 'electricity_power'
                ? 'Nama opsi harus mengandung angka kapasitas.'
                : 'Nama opsi harus mengandung huruf atau angka.']);
        }

        $duplicate = ListingOption::query()
            ->where('group', $option->group)
            ->where('slug', $slug)
            ->where('id', '!=', $option->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['option_label' => "Opsi \"{$data['label']}\" sudah ada."]);
        }

        $option->update(['label' => $data['label']]);

        return back()->with('status', "Opsi \"{$option->label}\" diperbarui.");
    }

    public function destroyOption(ListingOption $option): RedirectResponse
    {
        $column = $option->group;

        // The capacity list only feeds the catalog filter, so no listing row
        // points at it. Every other group is a value stored on listings.
        $used = $column === 'electricity_power'
            ? 0
            : Listing::withTrashed()->where($column, $option->slug)->count();

        if ($used > 0) {
            return back()->withErrors([
                'option' => "Opsi {$option->label} dipakai {$used} listing. Pindahkan listing itu dulu.",
            ]);
        }

        $option->delete();

        return back()->with('status', 'Opsi dihapus.');
    }
}
