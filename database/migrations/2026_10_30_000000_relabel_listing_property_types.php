<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The client split the old five types into the current ten. Old rows carry
     * a value that no longer exists, so remap them to the closest new type.
     */
    private const UP = [
        'rumah' => 'rumah_siap_huni',
        'townhouse' => 'rumah_siap_huni',
        'apartemen' => 'apartemen_siap_huni',
        'ruko' => 'ruko_usaha',
        'tanah' => 'kavling',
    ];

    private const DOWN = [
        'rumah_siap_huni' => 'rumah',
        'rumah_kosongan' => 'rumah',
        'rumah_kost' => 'rumah',
        'apartemen_siap_huni' => 'apartemen',
        'apartemen_kosongan' => 'apartemen',
        'ruko_usaha' => 'ruko',
        'ruko_siap_huni' => 'ruko',
        'kios' => 'ruko',
        'gudang' => 'ruko',
        'kavling' => 'tanah',
    ];

    public function up(): void
    {
        foreach (self::UP as $old => $new) {
            DB::table('listings')->where('property_type', $old)->update(['property_type' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::DOWN as $new => $old) {
            DB::table('listings')->where('property_type', $new)->update(['property_type' => $old]);
        }
    }
};
