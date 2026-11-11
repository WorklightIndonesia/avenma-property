<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertySubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertySubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'status', 'offer']);

        $submissions = PropertySubmission::query()
            ->search($filters)
            ->with('listing')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.submissions.index', [
            'submissions' => $submissions,
            'filters' => $filters,
        ]);
    }

    public function show(PropertySubmission $submission): View
    {
        return view('admin.submissions.show', ['submission' => $submission]);
    }

    public function update(Request $request, PropertySubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(PropertySubmission::STATUSES))],
            'commission_rate' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
        ]);

        $submission->update($data);

        return back()->with('status', 'Pengajuan diperbarui.');
    }
}
