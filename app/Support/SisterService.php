<?php

namespace App\Support;

use App\Models\SiteSetting;

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
                'title' => 'Virtual Office',
                'description' => 'Alamat bisnis legal tanpa perlu sewa kantor.',
                'message' => 'Halo Avenma, saya ingin bertanya soal Virtual Office. Saya butuh alamat bisnis untuk [PT/CV] di [kota]. Mohon info paket dan harganya.',
            ],
            [
                'title' => 'IT & Business Solutions',
                'description' => 'Website, aplikasi, AI, dan otomasi untuk bisnis.',
                'message' => 'Halo Avenma, saya ingin bertanya soal IT & Business Solutions. Saya butuh [website/aplikasi/otomasi] untuk bisnis di bidang [bidang]. Mohon info langkah berikutnya.',
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
