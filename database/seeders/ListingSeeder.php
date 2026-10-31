<?php

namespace Database\Seeders;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'agenbudi@gmail.com')->firstOrFail();

        $listings = [
            [
                'title' => 'Rumah Cluster Bintaro',
                'kecamatan' => 'Pondok Aren',
                'city' => 'Kota Tangerang Selatan',
                'developer' => 'Bintaro Development',
                'price' => 1_250_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 120],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'certificate' => 'Surat Hak Milik',
                'address' => 'Jl. Bintaro Utama IV No. 12, Pondok Aren, Tangerang Selatan',
                'description' => 'Rumah dua lantai di dalam cluster dengan akses satu gerbang. Pasar modern dan stasiun Bintaro berada dalam 10 menit berkendara. Sertifikat SHM, hadap utara.',
            ],
            [
                'title' => 'Apartemen Sudirman Park',
                'kecamatan' => 'Setiabudi',
                'city' => 'Kota Administrasi Jakarta Selatan',
                'developer' => null,
                'price' => 850_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 2, 'carport' => 1, 'building_area' => 68],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen_siap_huni',
                'address' => 'Jl. K.H. Mas Mansyur, Setiabudi, Jakarta Selatan',
                'description' => 'Unit dua kamar di lantai menengah dengan pemandangan kota. Fasilitas kolam renang, gym, dan akses langsung ke area perkantoran Sudirman.',
            ],
            [
                'title' => 'Townhouse Serpong Garden',
                'kecamatan' => 'Serpong',
                'city' => 'Kota Tangerang Selatan',
                'developer' => 'Graha Nusantara',
                'price' => 1_480_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 150],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_kosongan',
                'certificate' => 'Surat Balik Nama',
                'address' => 'Serpong Garden, Serpong, Tangerang Selatan',
                'description' => 'Townhouse baru tiga lantai dengan dua carport dan taman kecil di belakang. Cluster berpagar dengan keamanan 24 jam, dekat akses tol BSD.',
            ],
            [
                'title' => 'Rumah Minimalis Cipete',
                'kecamatan' => 'Cilandak',
                'city' => 'Kota Administrasi Jakarta Selatan',
                'developer' => 'Cipete Properti',
                'price' => 2_350_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 180],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Cipete Raya, Cilandak, Jakarta Selatan',
                'description' => 'Rumah satu lantai yang sudah direnovasi di kawasan Cipete. Halaman depan cukup untuk dua mobil, jarak ke MRT Cipete sekitar 800 meter.',
            ],
            [
                'title' => 'Apartemen Kemang Village',
                'kecamatan' => 'Mampang Prapatan',
                'city' => 'Kota Administrasi Jakarta Selatan',
                'developer' => 'Kemang Properti',
                'price' => 1_750_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 2, 'carport' => 1, 'building_area' => 85],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen_siap_huni',
                'address' => 'Jl. Pangeran Antasari, Mampang Prapatan, Jakarta Selatan',
                'description' => 'Unit dua kamar fully furnished di kawasan Kemang. Terhubung dengan mal dan area komersial, cocok untuk hunian maupun sewa jangka panjang.',
            ],
            [
                'title' => 'Rumah Taman Beverly',
                'kecamatan' => 'Serpong',
                'city' => 'Kota Tangerang Selatan',
                'developer' => 'Beverly Group',
                'price' => 1_950_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 165],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Taman Beverly, Serpong, Tangerang Selatan',
                'description' => 'Rumah baru dua lantai di cluster Taman Beverly. Empat kamar tidur, dua carport, dan akses langsung ke jalan utama BSD.',
            ],
            [
                'title' => 'Rumah Boulevard Kelapa Gading',
                'kecamatan' => 'Kelapa Gading',
                'city' => 'Kota Administrasi Jakarta Utara',
                'developer' => null,
                'price' => 3_200_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 200],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Boulevard Raya Blok QJ 5, Kelapa Gading, Jakarta Utara',
                'description' => 'Rumah tiga lantai di jalan boulevard dengan akses langsung ke mal dan jalan tol. Lantai dasar dipakai sebagai ruang usaha, lantai atas untuk hunian.',
            ],
            [
                'title' => 'Apartemen Menteng Residences',
                'kecamatan' => 'Menteng',
                'city' => 'Kota Administrasi Jakarta Pusat',
                'developer' => null,
                'price' => 2_400_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 2, 'carport' => 1, 'building_area' => 92],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen_siap_huni',
                'address' => 'Jl. Cikini Raya No. 72, Menteng, Jakarta Pusat',
                'description' => 'Unit dua kamar di kawasan Menteng dengan pemandangan taman kota. Bangunan lama yang sudah direnovasi, plafon tinggi, dan satu parkir tetap.',
            ],
            [
                'title' => 'Rumah Senopati Kebayoran',
                'kecamatan' => 'Kebayoran Baru',
                'city' => 'Kota Administrasi Jakarta Selatan',
                'developer' => null,
                'price' => 8_500_000_000,
                'specs' => ['bedrooms' => 5, 'bathrooms' => 4, 'carport' => 2, 'land_area' => 320],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Senopati No. 41, Kebayoran Baru, Jakarta Selatan',
                'description' => 'Rumah dua lantai di jalan utama Senopati, cocok untuk hunian maupun kantor. Lima kamar tidur, halaman belakang, dan dua carport di dalam pagar.',
            ],
            [
                'title' => 'Rumah Tanjung Duren',
                'kecamatan' => 'Grogol Petamburan',
                'city' => 'Kota Administrasi Jakarta Barat',
                'developer' => null,
                'price' => 950_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 96],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Tanjung Duren Raya No. 18, Grogol Petamburan, Jakarta Barat',
                'description' => 'Rumah satu lantai di dalam gang lebar, dekat kampus dan pasar Tanjung Duren. Sudah renovasi atap dan lantai, sertifikat SHM.',
            ],
            [
                'title' => 'Rumah Duren Sawit Baru',
                'kecamatan' => 'Duren Sawit',
                'city' => 'Kota Administrasi Jakarta Timur',
                'developer' => null,
                'price' => 780_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 84],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Duren Sawit Baru No. 7, Duren Sawit, Jakarta Timur',
                'description' => 'Rumah baru siap huni di kawasan Duren Sawit. Tiga kamar tidur, dapur bersih terpisah, dan jarak ke halte TransJakarta sekitar 600 meter.',
            ],
            [
                'title' => 'Apartemen Pluit Selatan',
                'kecamatan' => 'Penjaringan',
                'city' => 'Kota Administrasi Jakarta Utara',
                'developer' => null,
                'price' => 65_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 1, 'carport' => 1, 'building_area' => 45],
                'status' => 'disewa',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen_siap_huni',
                'address' => 'Jl. Pluit Selatan Raya No. 2, Penjaringan, Jakarta Utara',
                'description' => 'Unit dua kamar semi furnished dengan harga sewa satu tahun. Dekat akses tol Pluit dan area perkantoran, sudah termasuk biaya pemeliharaan.',
            ],
            [
                'title' => 'Rumah Ciledug Permai',
                'kecamatan' => 'Ciledug',
                'city' => 'Kota Tangerang',
                'developer' => null,
                'price' => 620_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 90],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Raden Patah No. 88, Ciledug, Tangerang',
                'description' => 'Rumah satu lantai di kompleks perumahan Ciledug, jalan lingkungan sudah beton. Dekat akses JORR dan pasar Ciledug.',
            ],
            [
                'title' => 'Rumah Boulevard Karawaci',
                'kecamatan' => 'Karawaci',
                'city' => 'Kota Tangerang',
                'developer' => null,
                'price' => 1_150_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 132],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Boulevard Diponegoro No. 105, Karawaci, Tangerang',
                'description' => 'Rumah baru dua lantai di cluster kecil berisi 24 unit. Dekat kampus dan rumah sakit, keamanan satu gerbang.',
            ],
            [
                'title' => 'Rumah Juanda Ciputat',
                'kecamatan' => 'Ciputat',
                'city' => 'Kota Tangerang Selatan',
                'developer' => 'Cipete Properti',
                'price' => 890_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 105],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Ir. H. Juanda No. 23, Ciputat, Tangerang Selatan',
                'description' => 'Rumah satu lantai di jalan Juanda, dekat kampus UIN dan pasar Ciputat. Halaman belakang luas, sumur bor dan PDAM aktif.',
            ],
            [
                'title' => 'Rumah Margonda Beji',
                'kecamatan' => 'Beji',
                'city' => 'Kota Depok',
                'developer' => null,
                'price' => 1_050_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 110],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Margonda Raya No. 312, Beji, Depok',
                'description' => 'Rumah dua lantai di Margonda dengan akses langsung ke jalur utama Depok–Jakarta. Dekat stasiun UI dan kampus.',
            ],
            [
                'title' => 'Apartemen Kartini Sukmajaya',
                'kecamatan' => 'Sukmajaya',
                'city' => 'Kota Depok',
                'developer' => null,
                'price' => 420_000_000,
                'specs' => ['bedrooms' => 1, 'bathrooms' => 1, 'carport' => 1, 'building_area' => 32],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen_siap_huni',
                'address' => 'Jl. Kartini No. 9, Sukmajaya, Depok',
                'description' => 'Studio satu kamar di lantai lima dengan balkon kecil. Cocok untuk mahasiswa atau pekerja yang naik KRL setiap hari.',
            ],
            [
                'title' => 'Rumah Perjuangan Bekasi Utara',
                'kecamatan' => 'Bekasi Utara',
                'city' => 'Kota Bekasi',
                'developer' => null,
                'price' => 730_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 88],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Perjuangan No. 56, Bekasi Utara, Bekasi',
                'description' => 'Rumah satu lantai di kawasan Perjuangan, dekat pusat perbelanjaan dan akses tol Bekasi Barat. Sertifikat sudah pecah.',
            ],
            [
                'title' => 'Rumah Sholeh Iskandar',
                'kecamatan' => 'Tanah Sareal',
                'city' => 'Kota Bogor',
                'developer' => null,
                'price' => 1_350_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 160],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. K.H. Sholeh Iskandar No. 14, Tanah Sareal, Bogor',
                'description' => 'Rumah baru dua lantai dengan view Gunung Salak dari lantai atas. Cluster berisi 18 unit, bebas banjir, akses langsung ke Jalan Sholeh Iskandar.',
            ],
            [
                'title' => 'Rumah Semeru Bogor Barat',
                'kecamatan' => 'Bogor Barat',
                'city' => 'Kota Bogor',
                'developer' => null,
                'price' => 560_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 1, 'carport' => 1, 'land_area' => 75],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah_siap_huni',
                'address' => 'Jl. Dr. Semeru No. 30, Bogor Barat, Bogor',
                'description' => 'Rumah satu lantai di kawasan Semeru, dekat pasar Bogor dan terminal Bubulak. Cocok untuk pasangan muda atau rumah sewa.',
            ],
        ];

        // shortcut: Pexels sample photos stand in until the client sends real files (Task 14)
        $photos = [
            'rumah-cluster-bintaro' => [
                ['pexels-1396122.webp', 'Tampak depan', 'Rumah dua lantai dengan dua carport di Bintaro'],
                ['pexels-1643383.webp', 'Ruang keluarga', 'Ruang keluarga dengan sofa dan meja kopi'],
                ['pexels-1080721.webp', 'Dapur', 'Dapur putih dengan pulau dan kursi bar'],
                ['pexels-1648776.webp', 'Ruang santai', 'Ruang keluarga dengan sofa cokelat dan jendela besar'],
                ['pexels-1457842.webp', 'Ruang TV', 'Ruang keluarga dengan TV dan jendela lebar'],
                ['pexels-1571460.webp', 'Area tangga', 'Ruang keluarga dengan tangga kayu dan jendela tinggi'],
            ],
            'apartemen-sudirman-park' => [
                ['pexels-1571460.webp', 'Ruang keluarga', 'Ruang keluarga apartemen dengan tangga dan jendela tinggi'],
                ['pexels-2724749.webp', 'Dapur', 'Dapur apartemen dengan kabinet putih'],
                ['pexels-1457842.webp', 'Ruang TV', 'Ruang keluarga dengan TV dan jendela lebar'],
            ],
            'townhouse-serpong-garden' => [
                ['pexels-259588.webp', 'Tampak depan', 'Townhouse batu dua lantai dengan jalan masuk'],
                ['pexels-1648776.webp', 'Ruang santai', 'Ruang keluarga dengan sofa cokelat'],
                ['pexels-2724749.webp', 'Dapur', 'Dapur dengan kabinet putih'],
                ['pexels-1571460.webp', 'Area tangga', 'Ruang keluarga dengan tangga kayu'],
            ],
            'rumah-minimalis-cipete' => [
                ['pexels-106399.webp', 'Tampak depan', 'Rumah dengan halaman depan dan garasi'],
                ['pexels-1643383.webp', 'Ruang keluarga', 'Ruang keluarga dengan sofa abu-abu'],
                ['pexels-1457842.webp', 'Ruang TV', 'Ruang keluarga dengan TV dan jendela lebar'],
                ['pexels-1080721.webp', 'Dapur', 'Dapur putih dengan pulau dan kursi bar'],
            ],
            'apartemen-kemang-village' => [
                ['pexels-1571460.webp', 'Ruang keluarga', 'Ruang keluarga apartemen dengan tangga dan jendela tinggi'],
                ['pexels-1080721.webp', 'Dapur', 'Dapur apartemen dengan kabinet putih'],
                ['pexels-2724749.webp', 'Dapur tamu', 'Dapur apartemen dengan pulau'],
                ['pexels-1457842.webp', 'Ruang TV', 'Ruang keluarga dengan TV dan jendela lebar'],
            ],
            'rumah-taman-beverly' => [
                ['pexels-259600.webp', 'Tampak depan', 'Rumah bata dua lantai dengan taman'],
                ['pexels-1029599.webp', 'Taman depan', 'Rumah putih dengan pagar dan halaman'],
                ['pexels-1648776.webp', 'Ruang santai', 'Ruang keluarga dengan sofa cokelat'],
                ['pexels-1643383.webp', 'Ruang keluarga', 'Ruang keluarga dengan sofa abu-abu'],
            ],
        ];

        // Listings added later reuse the same pool: three photos each, chosen by
        // property type so the label still matches the picture.
        $defaults = [
            'rumah_siap_huni' => [
                ['pexels-323780.webp', 'Tampak depan', 'Rumah dengan halaman dan garasi'],
                ['pexels-1643383.webp', 'Ruang keluarga', 'Ruang keluarga dengan sofa abu-abu'],
                ['pexels-1080721.webp', 'Dapur', 'Dapur putih dengan pulau'],
            ],
            'apartemen_siap_huni' => [
                ['pexels-1571460.webp', 'Ruang keluarga', 'Ruang keluarga apartemen dengan jendela tinggi'],
                ['pexels-2724749.webp', 'Dapur', 'Dapur apartemen dengan kabinet putih'],
                ['pexels-1457842.webp', 'Ruang TV', 'Ruang keluarga dengan TV dan jendela lebar'],
            ],
        ];

        foreach ($listings as $data) {
            $listing = Listing::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'location_id' => Location::where('name', $data['kecamatan'])
                        ->where('city', $data['city'])
                        ->firstOrFail()
                        ->id,
                    'developer_id' => $data['developer']
                        ? Developer::where('name', $data['developer'])->value('id')
                        : null,
                    'created_by' => $owner->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'listing_type' => $data['listing_type'],
                    'property_type' => $data['property_type'],
                    'show_kpr' => str_starts_with($data['property_type'], 'rumah'),
                    'certificate' => $data['certificate'] ?? null,
                    'status' => $data['status'],
                    'price' => $data['price'],
                    'bedrooms' => $data['specs']['bedrooms'],
                    'bathrooms' => $data['specs']['bathrooms'],
                    'carport' => $data['specs']['carport'],
                    'land_area' => $data['specs']['land_area'] ?? null,
                    'building_area' => $data['specs']['building_area'] ?? null,
                    'address' => $data['address'],
                ]
            );

            $images = $photos[$listing->slug] ?? $defaults[$data['property_type']] ?? $defaults['rumah_siap_huni'];

            foreach ($images as $sort => [$file, $label, $alt]) {
                $listing->images()->updateOrCreate(
                    ['path' => 'images/properties/'.$file],
                    ['label' => $label, 'alt' => $alt, 'sort_order' => $sort + 1]
                );
            }
        }
    }
}
