<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The five properties the client sent in November 2026. Photos live in
 * public/images/properties/<slug>/ and are numbered so 01 is the cover.
 */
class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrFail();
        $agents = Agent::query()->orderBy('id')->get();

        foreach ($this->listings() as $data) {
            $listing = Listing::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'location_id' => Location::where('name', $data['kecamatan'])
                        ->where('city', $data['city'])
                        ->firstOrFail()
                        ->id,
                    // The land listing stays unassigned so the Avenma fallback
                    // is visible without deleting an agent.
                    'agent_id' => $data['agent'] === null || $agents->isEmpty()
                        ? null
                        : $agents[$data['agent'] % $agents->count()]->id,
                    'created_by' => $owner->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'listing_type' => 'secondary',
                    'property_type' => $data['property_type'],
                    'show_kpr' => $data['property_type'] !== 'kavling',
                    'certificate' => $data['certificate'],
                    'status' => 'dijual',
                    'price' => $data['price'],
                    'bedrooms' => $data['bedrooms'],
                    'bathrooms' => $data['bathrooms'],
                    'carport' => $data['carport'],
                    'land_area' => $data['land_area'],
                    'building_area' => $data['building_area'],
                    'floors' => $data['floors'],
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'is_featured' => $data['featured'],
                    'is_published' => true,
                ]
            );

            $this->photos($listing);
        }
    }

    /**
     * The folder is the source of truth, so adding a photo means dropping a
     * numbered file in and re-running. Variants are written by the conversion
     * pass, not here.
     */
    private function photos(Listing $listing): void
    {
        $files = glob(public_path('images/properties/'.$listing->slug.'/[0-9][0-9].webp')) ?: [];
        sort($files);

        foreach ($files as $sort => $file) {
            $listing->images()->updateOrCreate(
                ['path' => 'images/properties/'.$listing->slug.'/'.basename($file)],
                [
                    'alt' => $listing->title.' — foto '.($sort + 1),
                    'sort_order' => $sort + 1,
                ]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listings(): array
    {
        return [
            [
                'slug' => 'tanah-strategis-kebon-jeruk',
                'title' => 'Tanah Strategis Kebon Jeruk',
                'kecamatan' => 'Kebon Jeruk',
                'city' => 'Kota Administrasi Jakarta Barat',
                'property_type' => 'kavling',
                'price' => 40_200_000_000,
                'land_area' => 1340,
                'building_area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'carport' => null,
                'floors' => null,
                'certificate' => null,
                'address' => 'Kebon Jeruk, Jakarta Barat',
                'latitude' => -6.1938,
                'longitude' => 106.7690,
                'agent' => null,
                'featured' => false,
                'description' => <<<'TEXT'
                Tanah seluas 1.340 m² di Kebon Jeruk, Jakarta Barat, dijual dengan harga Rp 30 juta per meter. Lokasinya dikelilingi tiga stasiun televisi, sehingga cocok untuk kantor, studio, atau usaha yang butuh akses cepat dari jalan utama.

                Bentuk lahan relatif rata dan sudah berpagar keliling. Akses masuk dari jalan lingkungan yang cukup untuk kendaraan operasional.

                Lokasi terdekat:
                - 5 menit ke transportasi Kebon Jeruk
                - 6 menit ke tol Kebon Jeruk
                - 8 menit ke Universitas Esa Unggul
                - 12 menit ke Mal Taman Anggrek
                - 14 menit ke Central Park
                TEXT,
            ],
            [
                'slug' => 'rumah-3-lantai-petojo',
                'title' => 'Rumah 3 Lantai Petojo',
                'kecamatan' => 'Gambir',
                'city' => 'Kota Administrasi Jakarta Pusat',
                'property_type' => 'rumah_kosongan',
                'price' => 3_700_000_000,
                'land_area' => 171,
                'building_area' => 510,
                'bedrooms' => 4,
                'bathrooms' => 5,
                'carport' => null,
                'floors' => 3,
                'certificate' => 'SHM',
                'address' => 'Petojo, Gambir, Jakarta Pusat',
                'latitude' => -6.1678,
                'longitude' => 106.8145,
                'agent' => 0,
                'featured' => false,
                'description' => <<<'TEXT'
                Rumah tiga lantai di Petojo, Jakarta Pusat, dengan luas tanah 171 m² (9 x 19) dan luas bangunan 510 m². Lokasinya di dalam gang yang tenang, tetapi tetap dekat ke pusat kota.

                Bangunan perlu renovasi. Dapur sudah furnished, dan tata ruangnya memungkinkan empat kamar tidur serta lima kamar mandi. Sertifikat SHM.

                Lokasi terdekat:
                - 5 menit ke RS Tarakan
                - 9 menit ke Stasiun Duri
                - 10 menit ke Mal Taman Anggrek
                - 10 menit ke RS Sumber Waras
                - 12 menit ke Universitas Kristen Indonesia
                - 15 menit ke Universitas Esa Unggul
                - 20 menit ke gerbang Tol Dalam Kota
                - 40 menit ke Bandara Soekarno-Hatta
                TEXT,
            ],
            [
                'slug' => 'rumah-pondok-indah-dekat-pim',
                'title' => 'Rumah Pondok Indah Dekat PIM',
                'kecamatan' => 'Kebayoran Lama',
                'city' => 'Kota Administrasi Jakarta Selatan',
                'property_type' => 'rumah_siap_huni',
                'price' => 25_000_000_000,
                'land_area' => 427,
                'building_area' => 444,
                'bedrooms' => 5,
                'bathrooms' => 3,
                'carport' => 1,
                'floors' => null,
                'certificate' => 'SHM',
                'address' => 'Pondok Indah, Kebayoran Lama, Jakarta Selatan',
                'latitude' => -6.2665,
                'longitude' => 106.7833,
                'agent' => 1,
                'featured' => false,
                'description' => <<<'TEXT'
                Rumah di Pondok Indah, Jakarta Selatan, dengan luas tanah 427 m² dan luas bangunan 444 m². Lima kamar tidur, tiga kamar mandi, dan garasi untuk satu mobil.

                Rumah menghadap selatan, semi furnished, dengan daya listrik 13.200 watt. Jalan di depan cukup untuk dua mobil berpapasan. Sertifikat SHM.

                Lokasi terdekat:
                - 4 menit ke Pondok Indah Mall
                - 7 menit ke MRT Cipete Raya
                - 10 menit ke Terminal Pondok Pinang
                - 10 menit ke Jakarta Intercultural School
                - 10 menit ke gerbang tol Lebak Bulus
                - 12 menit ke Siloam Hospital
                TEXT,
            ],
            [
                'slug' => 'rumah-renovasi-kayu-putih',
                'title' => 'Rumah Renovasi Kayu Putih',
                'kecamatan' => 'Pulogadung',
                'city' => 'Kota Administrasi Jakarta Timur',
                'property_type' => 'rumah_siap_huni',
                'price' => 3_000_000_000,
                'land_area' => 135,
                'building_area' => 200,
                'bedrooms' => 4,
                'bathrooms' => 4,
                'carport' => 1,
                'floors' => 2,
                'certificate' => 'SHM',
                'address' => 'Kayu Putih, Pulogadung, Jakarta Timur',
                'latitude' => -6.1830,
                'longitude' => 106.8870,
                'agent' => 2,
                'featured' => false,
                'description' => <<<'TEXT'
                Rumah 2,5 lantai di Kayu Putih, Jakarta Timur, sudah direnovasi dan siap huni. Luas tanah 135 m², luas bangunan 200 m², dengan empat kamar tidur dan empat kamar mandi.

                Semi furnished, carport untuk satu mobil, dan jalan depan cukup untuk dua mobil. Air PAM, listrik 3.500 watt, sertifikat SHM.

                Lokasi terdekat:
                - 2 menit ke Universitas Jayabaya
                - 3 menit ke RS EMC Pulomas
                - 4 menit ke Sekolah Don Bosco 2
                - 5 menit ke RS Columbia Asia
                - 6 menit ke Universitas Negeri Jakarta
                - 6 menit ke Stasiun LRT Velodrome
                - 7 menit ke tol Pulomas
                TEXT,
            ],
            [
                'slug' => 'rumah-2-lantai-kelapa-gading',
                'title' => 'Rumah 2 Lantai Kelapa Gading',
                'kecamatan' => 'Kelapa Gading',
                'city' => 'Kota Administrasi Jakarta Utara',
                'property_type' => 'rumah_siap_huni',
                'price' => 1_800_000_000,
                'land_area' => 90,
                'building_area' => 135,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'carport' => 2,
                'floors' => 2,
                'certificate' => 'SHM',
                'address' => 'Kelapa Gading, Jakarta Utara',
                'latitude' => -6.1570,
                'longitude' => 106.9055,
                'agent' => 3,
                'featured' => true,
                'description' => <<<'TEXT'
                Rumah dua lantai di Kelapa Gading, Jakarta Utara, siap huni. Luas tanah 90 m² (6 x 15) dan luas bangunan 135 m², dengan tiga kamar tidur dan tiga kamar mandi.

                Semi furnished, menghadap selatan, carport untuk dua mobil. Air PAM, listrik 2.200 watt, sertifikat SHM.

                Lokasi terdekat:
                - 5 menit ke Mal Kelapa Gading
                - 5 menit ke LRT Boulevard Selatan
                - 5 menit ke Sekolah Al-Azhar
                - 5 menit ke BPK Penabur
                - 8 menit ke Raffles Christian School
                - 8 menit ke gerbang tol Kelapa Gading
                TEXT,
            ],
        ];
    }
}
