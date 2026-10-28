<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `locations` becomes the official Kemendagri region table: one row per
 * kecamatan, with its kota/kabupaten and province. `name` stays the kecamatan
 * name, which is what every existing view already prints.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('city')->nullable()->after('province');
            $table->string('code', 20)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['city', 'code']);
        });
    }
};
