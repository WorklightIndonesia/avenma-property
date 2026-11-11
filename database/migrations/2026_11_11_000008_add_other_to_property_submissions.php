<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The dropdowns list the options the catalog filters on, but an owner may
     * hold something else. "Lainnya" keeps that text beside the slug instead
     * of forcing a wrong choice.
     */
    public function up(): void
    {
        Schema::table('property_submissions', function (Blueprint $table) {
            $table->string('property_type_other', 100)->nullable()->after('property_type');
            $table->string('certificate_other', 100)->nullable()->after('certificate');
        });
    }

    public function down(): void
    {
        Schema::table('property_submissions', function (Blueprint $table) {
            $table->dropColumn(['property_type_other', 'certificate_other']);
        });
    }
};
