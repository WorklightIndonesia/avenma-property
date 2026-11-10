<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedInteger('electricity_power')->nullable()->after('carport');
            $table->string('water_source', 50)->nullable()->after('electricity_power');
            $table->string('other_specs', 255)->nullable()->after('water_source');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['electricity_power', 'water_source', 'other_specs']);
        });
    }
};
