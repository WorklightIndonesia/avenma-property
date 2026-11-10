<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The spec choices used to be Listing constants. They become rows here
        // so Pengaturan Listing can add, rename and remove them like any option.
        $now = now();
        $rows = [];

        foreach ([
            'water_source' => [
                'pdam' => 'PDAM',
                'sumur_bor' => 'Sumur Bor',
                'sumur_gali' => 'Sumur Gali',
                'mata_air' => 'Mata Air',
                'wtp' => 'Water Treatment Plant',
                'lainnya' => 'Lainnya',
            ],
            'electricity_power' => [
                '900' => '900 VA',
                '1300' => '1.300 VA',
                '2200' => '2.200 VA',
                '3500' => '3.500 VA',
                '4400' => '4.400 VA',
                '5500' => '5.500 VA',
                '6600' => '6.600 VA',
            ],
        ] as $group => $options) {
            foreach ($options as $slug => $label) {
                $rows[] = [
                    'group' => $group,
                    'label' => $label,
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('listing_options')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        DB::table('listing_options')->whereIn('group', ['water_source', 'electricity_power'])->delete();
    }
};
