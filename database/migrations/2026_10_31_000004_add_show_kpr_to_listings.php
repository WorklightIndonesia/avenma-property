<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('show_kpr')->default(true)->after('is_published');
        });

        // Existing content: keep the simulator on house listings only.
        DB::table('listings')
            ->where('property_type', 'not like', 'rumah%')
            ->update(['show_kpr' => false]);
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('show_kpr');
        });
    }
};
