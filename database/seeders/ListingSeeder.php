<?php

namespace Database\Seeders;

use App\Models\Category;
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
                'category' => 'Rumah',
                'location' => 'Bintaro',
                'developer' => 'Bintaro Development',
                'price' => 1_250_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 120],
                'status' => 'dijual',
                'listing_type' => 'primary',
                'property_type' => 'rumah',
                'address' => 'Jl. Bintaro Utama IV No. 12, Bintaro, Tangerang Selatan',
                'description' => 'Rumah dua lantai di dalam cluster dengan akses satu gerbang. Pasar modern dan stasiun Bintaro berada dalam 10 menit berkendara. Sertifikat SHM, hadap utara.',
            ],
            [
                'title' => 'Apartemen Sudirman Park',
                'category' => 'Apartemen',
                'location' => 'Sudirman',
                'developer' => null,
                'price' => 850_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 68],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen',
                'address' => 'Jl. K.H. Mas Mansyur, Sudirman, Jakarta Pusat',
                'description' => 'Unit dua kamar di lantai menengah dengan pemandangan kota. Fasilitas kolam renang, gym, dan akses langsung ke area perkantoran Sudirman.',
            ],
            [
                'title' => 'Townhouse Serpong Garden',
                'category' => 'Townhouse',
                'location' => 'Serpong',
                'developer' => 'Graha Nusantara',
                'price' => 1_480_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 150],
                'status' => 'baru',
                'listing_type' => 'primary',
                'property_type' => 'rumah',
                'address' => 'Serpong Garden, Serpong, Tangerang Selatan',
                'description' => 'Townhouse baru tiga lantai dengan dua carport dan taman kecil di belakang. Cluster berpagar dengan keamanan 24 jam, dekat akses tol BSD.',
            ],
            [
                'title' => 'Rumah Minimalis Cipete',
                'category' => 'Rumah',
                'location' => 'Cipete',
                'developer' => 'Cipete Properti',
                'price' => 2_350_000_000,
                'specs' => ['bedrooms' => 3, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 180],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'rumah',
                'address' => 'Jl. Cipete Raya, Cipete, Jakarta Selatan',
                'description' => 'Rumah satu lantai yang sudah direnovasi di kawasan Cipete. Halaman depan cukup untuk dua mobil, jarak ke MRT Cipete sekitar 800 meter.',
            ],
            [
                'title' => 'Apartemen Kemang Village',
                'category' => 'Apartemen',
                'location' => 'Kemang',
                'developer' => 'Kemang Properti',
                'price' => 1_750_000_000,
                'specs' => ['bedrooms' => 2, 'bathrooms' => 2, 'carport' => 1, 'land_area' => 85],
                'status' => 'dijual',
                'listing_type' => 'secondary',
                'property_type' => 'apartemen',
                'address' => 'Jl. Pangeran Antasari, Kemang, Jakarta Selatan',
                'description' => 'Unit dua kamar fully furnished di kawasan Kemang. Terhubung dengan mal dan area komersial, cocok untuk hunian maupun sewa jangka panjang.',
            ],
            [
                'title' => 'Rumah Taman Beverly',
                'category' => 'Rumah',
                'location' => 'BSD',
                'developer' => 'Beverly Group',
                'price' => 1_950_000_000,
                'specs' => ['bedrooms' => 4, 'bathrooms' => 3, 'carport' => 2, 'land_area' => 165],
                'status' => 'baru',
                'listing_type' => 'primary',
                'property_type' => 'rumah',
                'address' => 'Taman Beverly, BSD, Tangerang Selatan',
                'description' => 'Rumah baru dua lantai di cluster Taman Beverly. Empat kamar tidur, dua carport, dan akses langsung ke jalan utama BSD.',
            ],
        ];

        foreach ($listings as $data) {
            Listing::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'category_id' => Category::where('name', $data['category'])->value('id'),
                    'location_id' => Location::where('name', $data['location'])->value('id'),
                    'developer_id' => $data['developer']
                        ? Developer::where('name', $data['developer'])->value('id')
                        : null,
                    'created_by' => $owner->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'listing_type' => $data['listing_type'],
                    'property_type' => $data['property_type'],
                    'status' => $data['status'],
                    'price' => $data['price'],
                    'bedrooms' => $data['specs']['bedrooms'],
                    'bathrooms' => $data['specs']['bathrooms'],
                    'carport' => $data['specs']['carport'],
                    'land_area' => $data['specs']['land_area'],
                    'address' => $data['address'],
                ]
            );
        }
    }
}
