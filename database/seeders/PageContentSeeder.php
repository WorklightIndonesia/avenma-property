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
                        'heading' => 'Temukan properti impian, wujudkan ruang masa depan bagi keluarga Anda.',
                        'body' => '<p>Mulai perjalanan Anda bersama Avenma melalui beragam pilihan properti, layanan konsultasi desain arsitektur, pengurusan legalitas properti, hingga pelaksanaan konstruksi profesional. Semua hadir dalam satu solusi terpadu untuk membantu mewujudkan kebutuhan dan impian Anda.</p>',
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
                        'heading' => 'Dari Properti Impian hingga Hunian Idaman, Semua dalam Satu Layanan',
                        'body' => '<p>Kami menghadirkan layanan desain arsitektur dan konstruksi untuk membantu Anda mewujudkan hunian yang sesuai dengan kebutuhan, karakter, dan gaya hidup Anda.</p>'
                            .'<p>Melalui layanan konsultasi profesional, kami mendampingi setiap tahap perencanaan, mulai dari desain arsitektur, penataan layout, perencanaan struktur, hingga sistem Mechanical, Electrical &amp; Plumbing (MEP), serta pelaksanaan konstruksi.</p>'
                            .'<blockquote>Satu Tim, Satu Perencanaan, Satu Solusi</blockquote>'
                            .'<p>Dengan dukungan tim yang terintegrasi, proses desain hingga pembangunan dapat ditangani dalam satu koordinasi yang menyeluruh. Anda tidak perlu repot menghubungkan berbagai pihak secara terpisah. Kami siap mendampingi perjalanan Anda, mulai dari menemukan properti yang tepat, merancang hunian impian, hingga mewujudkannya menjadi tempat tinggal yang nyaman dan bernilai.</p>',
                        'cta_label' => 'Diskusikan via WhatsApp',
                    ],
                ],
            ],
        ];
    }
}
