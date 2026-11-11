<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * /layanan is split into three sections. Existing rows were all property
     * services, so the default files them there without a backfill.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category', 32)->default('properti')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
