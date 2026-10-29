<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

/**
 * Draft copy for the four public pages. Replace it through Admin > Halaman;
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
                        'cta_url_2' => '/kontak',
                    ],
                ],
            ],

            [
                'key' => 'tentang-kami',
                'title' => 'Tentang Kami',
                'meta_title' => 'Tentang Avenma Properti',
                'meta_description' => 'Avenma Properti membantu pembeli membandingkan properti primer dan sekunder dengan informasi yang jelas.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Properti yang dipilih dengan alasan.',
                        'body' => '<p>Avenma Properti membantu Anda membandingkan properti primer dan sekunder tanpa menebak-nebak: lokasi, luas, harga, dan perkiraan angsuran ada di satu halaman.</p>',
                        'image_alt' => 'Kantor Avenma Properti',
                    ],
                    'cerita' => [
                        'heading' => 'Siapa Avenma',
                        'body' => '<p>Avenma Properti adalah bagian dari Worklight yang bergerak di pemasaran properti primer dan sekunder. Kami bekerja sama dengan developer dan pemilik properti untuk menampilkan pilihan yang benar-benar tersedia.</p>'
                            .'<p>Setiap listing diperiksa sebelum tayang. Bila ada data yang belum pasti, kami menuliskannya sebagai perkiraan alih-alih mengarang angka.</p>',
                    ],
                    'nilai' => [
                        'heading' => 'Yang kami pegang',
                        'body' => '<ul><li><strong>Jelas.</strong> Harga, luas, dan status ditulis apa adanya.</li>'
                            .'<li><strong>Lengkap.</strong> Lokasi dan peta membantu Anda membayangkan jarak ke tempat kerja dan sekolah.</li>'
                            .'<li><strong>Terbuka.</strong> Simulasi KPR adalah perkiraan, bukan janji bank, dan kami menyebutkannya di setiap tampilan angka.</li></ul>',
                    ],
                    'cta' => [
                        'heading' => 'Mulai dari katalog',
                        'body' => '<p>Bandingkan properti yang tersedia, lalu hubungi kami bila ada yang ingin Anda lihat langsung.</p>',
                        'cta_label' => 'Lihat Properti',
                        'cta_url' => '/properti',
                    ],
                ],
            ],

            [
                'key' => 'kontak',
                'title' => 'Kontak',
                'meta_title' => 'Hubungi Avenma Properti',
                'meta_description' => 'Hubungi Avenma Properti untuk pertanyaan properti, jadwal survei, dan simulasi KPR.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Hubungi kami.',
                        'body' => '<p>Tim Avenma menjawab pertanyaan tentang properti, jadwal survei, dan simulasi KPR pada hari kerja.</p>',
                        'image_alt' => 'Ruang rapat Avenma Properti',
                    ],
                    'kontak' => [
                        'heading' => 'Cara menghubungi',
                        'body' => '<p>WhatsApp adalah jalur tercepat: pesan Anda langsung sampai ke tim yang memegang listing terkait. Sertakan nama properti atau tautan halamannya agar kami bisa menjawab tanpa bertanya balik.</p>'
                            .'<p>Untuk kerja sama developer atau pemasangan listing, tuliskan kebutuhan Anda pada pesan pertama.</p>',
                    ],
                ],
            ],

            [
                'key' => 'syarat-ketentuan',
                'title' => 'Syarat & Ketentuan',
                'meta_title' => 'Syarat dan Ketentuan - Avenma Properti',
                'meta_description' => 'Ketentuan penggunaan situs dan informasi properti Avenma Properti.',
                'sections' => [
                    'hero' => [
                        'heading' => 'Syarat dan ketentuan.',
                        'body' => '<p>Ketentuan ini berlaku saat Anda menggunakan situs Avenma Properti dan informasi di dalamnya.</p>',
                        'image_alt' => 'Dokumen dan meja kerja',
                    ],
                    'ketentuan' => [
                        'body' => '<h2>Informasi properti</h2>'
                            .'<p>Kami menyusun informasi properti dengan teliti, tetapi harga, ketersediaan, dan spesifikasi dapat berubah tanpa pemberitahuan. Angka pada simulasi KPR adalah perkiraan dan bukan penawaran bank; biaya provisi, asuransi, dan pajak tidak termasuk.</p>'
                            .'<h2>Penggunaan situs</h2>'
                            .'<ol><li>Anda tidak menyalahgunakan situs, termasuk mengambil data secara otomatis dalam jumlah besar.</li>'
                            .'<li>Anda tidak mengirimkan informasi palsu saat menghubungi kami.</li>'
                            .'<li>Tautan ke situs lain di luar kendali kami dan tunduk pada ketentuan situs tersebut.</li></ol>'
                            .'<h2>Hak cipta</h2>'
                            .'<p>Foto, teks, dan materi promosi di situs ini milik Avenma Properti atau pihak pemberi lisensi. Penggunaan ulang untuk keperluan komersial memerlukan izin tertulis.</p>'
                            .'<h2>Perubahan</h2>'
                            .'<p>Ketentuan dapat diperbarui sewaktu-waktu. Versi yang berlaku adalah yang tampil di halaman ini.</p>',
                    ],
                ],
            ],
        ];
    }
}
