<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeveloperRequest;
use App\Models\Developer;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Developers are listed and added on Pengaturan Listing; only the edit screen
 * lives here, because that form is too tall for the settings page.
 */
class DeveloperController extends Controller
{
    public function edit(Developer $developer): View
    {
        return view('admin.developers.edit', ['developer' => $developer]);
    }

    public function update(DeveloperRequest $request, Developer $developer): RedirectResponse
    {
        $developer->fill($request->validated());
        Slug::save($developer, $request->validated()['name'], 'developer');

        return redirect()
            ->route('admin.listing-settings.index')
            ->with('status', 'Perubahan disimpan.');
    }
}
