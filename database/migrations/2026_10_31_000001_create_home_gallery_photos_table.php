<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_image_id')->unique()->constrained('listing_images')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_gallery_photos');
    }
};
