<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The photo order picker now starts at 1; shift the seeded 0-based rows
     * so their relative order survives the change.
     */
    public function up(): void
    {
        DB::table('listing_images')->increment('sort_order');
    }

    public function down(): void
    {
        DB::table('listing_images')->decrement('sort_order');
    }
};
