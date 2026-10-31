<?php

namespace Database\Seeders;

use App\Models\Developer;
use App\Models\KprSetting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $developers = [
            'Bintaro Development',
            'Graha Nusantara',
            'Cipete Properti',
            'Kemang Properti',
            'Beverly Group',
        ];

        foreach ($developers as $name) {
            Developer::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        KprSetting::firstOrCreate([], [
            'interest_rate' => 7.50,
            'min_dp_percent' => 20.00,
            'max_tenor_years' => 20,
        ]);

        // shortcut: placeholder office number, replace through the settings panel before hosting
        SiteSetting::firstOrCreate([], [
            'whatsapp' => '087736786969',
            'maps_url' => 'https://maps.app.goo.gl/yvhXuc3mnWkUjua56',
        ]);
    }
}
