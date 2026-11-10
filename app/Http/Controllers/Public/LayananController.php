<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\WhatsAppTemplate;
use App\Support\WhatsApp;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * One page for every service line. The rows live in the services table and are
 * edited from Admin > Layanan; the hero copy comes from the page CMS.
 */
class LayananController extends Controller
{
    public function index(): View
    {
        $page = PageContent::query()->with('sections')->where('key', 'layanan')->first();

        return view('public.layanan.index', [
            'page' => $page,
            'hero' => $page?->section('hero'),
            'heroCta' => $this->whatsapp(WhatsAppTemplate::message('layanan_default')),
            // shortcut: the anchor is derived from the title, so renaming a
            // service changes its link; add a services.slug column when a
            // shared link must survive a rename.
            'services' => Service::query()->ordered()->get()->mapWithKeys(fn (Service $service) => [
                Str::slug($service->title) => [
                    'title' => $service->title,
                    'tags' => $service->tagsList(),
                    'description' => $service->description,
                    'cta' => $service->cta_label ?: 'Diskusikan via WhatsApp',
                    'ctaUrl' => $this->whatsapp($service->message ?: WhatsAppTemplate::message('layanan_default')),
                ],
            ]),
        ]);
    }

    private function whatsapp(string $message): string
    {
        return WhatsApp::url(SiteSetting::whatsappNumber(), $message)
            ?? route('public.pages.contact');
    }
}
