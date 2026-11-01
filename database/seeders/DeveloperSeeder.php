<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;

/**
 * The developers the sample listings point at. firstOrCreate keys on the name
 * ListingSeeder looks up, and keeps a re-seed from overwriting contact details
 * an admin has corrected.
 */
class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $developers = [
            ['name' => 'Bintaro Development', 'slug' => 'bintaro-development', 'whatsapp' => '081234567001', 'email' => 'bintaro.development@example.com'],
            ['name' => 'Graha Nusantara', 'slug' => 'graha-nusantara', 'whatsapp' => '081234567002', 'email' => 'graha.nusantara@example.com'],
            ['name' => 'Cipete Properti', 'slug' => 'cipete-properti', 'whatsapp' => '081234567003', 'email' => 'cipete.properti@example.com'],
            ['name' => 'Kemang Properti', 'slug' => 'kemang-properti', 'whatsapp' => '081234567004', 'email' => 'kemang.properti@example.com'],
            ['name' => 'Beverly Group', 'slug' => 'beverly-group', 'whatsapp' => '081234567005', 'email' => 'beverly.group@example.com'],
        ];

        foreach ($developers as $developer) {
            Developer::firstOrCreate(['name' => $developer['name']], $developer);
        }
    }
}
