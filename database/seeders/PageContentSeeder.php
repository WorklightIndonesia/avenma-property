<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

/**
 * Draft copy for the pages in config/pages.php. Replace it through Admin > Halaman;
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
                'meta_description' => 'Katalog properti primer dan sekunder di Jabodetabek, dengan lokasi resmi, luas, harga, dan simulasi KPR. Bandingkan tanpa membuka tab lain.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Properti yang tepat, untuk setiap rencana.',
                        'body' => '<p>Jelajahi rumah, apartemen, ruko, dan kavling, atau percayakan jual beli, sewa, dan pengelolaan properti Anda kepada tim kami.</p>',
                        'image_alt' => 'Foto properti utama Avenma',
                        'cta_label' => 'Lihat Properti',
                        'cta_url' => '/properti',
                        'cta_label_2' => 'Layanan Kami',
                        'cta_url_2' => '/layanan',
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
                        'body' => '<p>WhatsApp adalah jalur tercepat. Pesan Anda langsung sampai ke tim yang memegang listing terkait. Sertakan nama properti atau link halamannya agar kami bisa menjawab tanpa bertanya balik.</p>'
                            .'<p>Untuk kerja sama developer atau pemasangan listing, tuliskan kebutuhan Anda pada pesan pertama.</p>',
                    ],
                ],
            ],

            [
                'key' => 'layanan',
                'title' => 'Layanan',
                'meta_title' => 'Layanan Properti',
                'meta_description' => 'Jual beli, sewa, pemasaran, konsultasi, dan pengelolaan properti. Satu tim Avenma Property untuk semuanya.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Dari menawarkan sampai mengurus properti.',
                        'body' => '<p>Jual beli, sewa, pemasaran, konsultasi, dan pengelolaan properti harian. Satu tim yang bisa Anda hubungi untuk semuanya.</p>',
                        'cta_label' => 'Diskusikan via WhatsApp',
                    ],
                ],
            ],
        ];
    }
}
