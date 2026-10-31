<?php

namespace Database\Seeders;

use App\Models\KprSetting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        KprSetting::firstOrCreate([], [
            'interest_rate' => 7.50,
            'min_dp_percent' => 20.00,
            'max_tenor_years' => 20,
        ]);

        SiteSetting::firstOrCreate([], [
            'whatsapp' => '081299900387',
            'maps_url' => 'https://maps.app.goo.gl/yvhXuc3mnWkUjua56',
            'address' => 'Jl. Raya Kelapa Hybrida Blok PD 14 No.28, Pegangsaan Dua, Kelapa Gading, Jakarta Utara 14250',
            'instagram' => 'avenma.properti',
            'email' => 'avenma.properti@gmail.com',
        ]);
    }
}
