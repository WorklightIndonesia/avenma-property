<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('maps_lat', 10, 7)->nullable()->after('maps_url');
            $table->decimal('maps_lng', 10, 7)->nullable()->after('maps_lat');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['maps_lat', 'maps_lng']);
        });
    }
};
