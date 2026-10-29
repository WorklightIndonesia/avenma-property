<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_content_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('heading')->nullable();
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['page_content_id', 'key']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('email')->nullable()->after('whatsapp');
            $table->string('address')->nullable()->after('email');
            $table->string('instagram')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['email', 'address', 'instagram']);
        });

        Schema::dropIfExists('page_sections');
    }
};
