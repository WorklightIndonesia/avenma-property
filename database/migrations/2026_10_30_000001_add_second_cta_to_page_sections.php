<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The hero carries two buttons (see the design): one primary action and one
     * secondary. Sections further down use the first pair only.
     */
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->string('cta_label_2')->nullable()->after('cta_url');
            $table->string('cta_url_2')->nullable()->after('cta_label_2');
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn(['cta_label_2', 'cta_url_2']);
        });
    }
};
