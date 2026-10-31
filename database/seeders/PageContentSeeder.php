<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

/**
 * Draft copy for the three public pages. Replace it through Admin > Halaman;
 * the seeder only runs on a fresh database.
 */
class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            $sections = $page['sections'];
            unset($page['sections']);

            $content = PageContent::updateOrCreate(['key' => $page['key']], $page);

            $order = 0;

            foreach ($sections as $key => $section) {
                $content->sections()->updateOrCreate(['key' => $key], $section + ['sort_order' => $order++]);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'key' => 'home',
                'title' => 'Beranda',
                'meta_title' => 'Avenma Properti - Katalog Properti Pilihan',
                'meta_description' => 'Katalog properti primer dan sekunder pilihan di Jabodetabek, lengkap dengan informasi lokasi dan simulasi KPR.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Temukan properti yang tepat untuk Anda.',
                        'body' => '<p>Katalog properti pilihan di Jabodetabek, lengkap dengan informasi lokasi dan simulasi KPR.</p>',
                        'image_alt' => 'Foto properti utama Avenma',
                        'cta_label' => 'Lihat Properti',
                        'cta_url' => '/properti',
                        'cta_label_2' => 'Hubungi Kami',
                        'cta_url_2' => '/hubungi-kami',
                    ],
                ],
            ],

            [
                'key' => 'kontak',
                'title' => 'Kontak',
                'meta_title' => 'Hubungi Avenma Properti',
                'meta_description' => 'Hubungi Avenma Properti untuk pertanyaan properti, jadwal survei, dan simulasi KPR.',
                'sections' => [
                    'kontak' => [
                        'heading' => 'Hubungi Kami',
                        'body' => '<p>WhatsApp adalah jalur tercepat: pesan Anda langsung sampai ke tim yang memegang listing terkait. Sertakan nama properti atau tautan halamannya agar kami bisa menjawab tanpa bertanya balik.</p>'
                            .'<p>Untuk kerja sama developer atau pemasangan listing, tuliskan kebutuhan Anda pada pesan pertama.</p>',
                    ],
                ],
            ],
        ];
    }
}
