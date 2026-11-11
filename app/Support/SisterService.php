<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Models\WhatsAppTemplate;

/**
 * The two service lines Avenma does not run itself. The navbar, the footer and
 * the "Layanan lain" block on /layanan all read this list, so the wording and
 * the WhatsApp template only live in one place.
 */
class SisterService
{
    /** @return array<int, array{title: string, description: string, message: string}> */
    public static function all(): array
    {
        return [
            [
                'title' => 'Virtual Office (VO)',
                'description' => 'Alamat bisnis legal tanpa perlu sewa kantor.',
                'message' => WhatsAppTemplate::message('sister_virtual_office'),
            ],
            [
                'title' => 'IT & Business Solution',
                'description' => 'Website, aplikasi, AI, dan otomasi untuk bisnis.',
                'message' => WhatsAppTemplate::message('sister_it_solutions'),
            ],
        ];
    }

    /**
     * Each row plus a wa.me link that opens with its template filled in. Falls
     * back to the contact page, like the service rows, when no number is set.
     *
     * @return array<int, array{title: string, description: string, message: string, url: string}>
     */
    public static function links(): array
    {
        $number = SiteSetting::whatsappNumber();

        return array_map(fn (array $service) => $service + [
            'url' => WhatsApp::url($number, $service['message']) ?? route('public.pages.contact'),
        ], self::all());
    }
}
