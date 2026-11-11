<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertySubmissionRequest;
use App\Models\ListingOption;
use App\Models\PageContent;
use App\Models\PropertySubmission;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The titip properti page and its form. The copy lives in page_contents like
 * every other page; the field list is fixed here because the admin fills a
 * listing from it.
 */
class SubmissionController extends Controller
{
    public function create(): View
    {
        $page = PageContent::query()->with('sections')->where('key', 'titip')->firstOrFail();

        return view('public.pages.show', [
            'page' => $page,
            'sections' => config('pages.titip.sections'),
            'settings' => SiteSetting::current(),
            'pageKey' => 'titip',
            'propertyTypes' => ListingOption::options('property_type'),
            'certificates' => ListingOption::options('certificate'),
        ]);
    }

    public function store(PropertySubmissionRequest $request): RedirectResponse
    {
        // A filled honeypot means a bot. It gets the same answer, so it cannot
        // tell that nothing was saved.
        if (filled($request->input('website'))) {
            return $this->thanks();
        }

        $data = $request->validated();
        $data['commission_rate'] = PropertySubmission::COMMISSION_RATES[$data['offer']];

        // The typed text only belongs to the row while "Lainnya" is chosen.
        $data['property_type_other'] = $data['property_type'] === PropertySubmission::OTHER
            ? ($data['property_type_other'] ?? null)
            : null;
        $data['certificate_other'] = ($data['certificate'] ?? null) === PropertySubmission::OTHER
            ? ($data['certificate_other'] ?? null)
            : null;

        PropertySubmission::create($data);

        return $this->thanks();
    }

    private function thanks(): RedirectResponse
    {
        return redirect()
            ->route('public.submissions.create')
            ->with('status', 'Terima kasih. Data properti Anda sudah kami terima; tim Avenma akan menghubungi Anda lewat WhatsApp.');
    }
}
