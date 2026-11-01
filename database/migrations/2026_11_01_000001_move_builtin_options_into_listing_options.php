<?php

use App\Models\Listing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Built-in choices used to live only in Listing constants, which left
        // them marked "Bawaan" and impossible to remove. They become rows here
        // so every option is managed the same way: deletable while unused.
        $now = now();
        $rows = [];

        foreach ([
            'listing_type' => Listing::LISTING_TYPES,
            'property_type' => Listing::PROPERTY_TYPES,
            'status' => Listing::STATUSES,
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
        // These rows look the same as the ones added by hand, and removing them
        // would blank the labels of listings that already point at them.
    }
};
