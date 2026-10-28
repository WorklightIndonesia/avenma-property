<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A listing now stores the point the agent picked from the address suggestions.
 * `map_embed` is gone: the map is built from these coordinates, or from the
 * address text when there is no point, so a pasted iframe has nothing to do.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->dropColumn('map_embed');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
            $table->text('map_embed')->nullable();
        });
    }
};
