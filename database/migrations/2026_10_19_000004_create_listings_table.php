<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('developer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->longText('description');
            $table->longText('description_en')->nullable();

            $table->string('listing_type')->default('primary');
            $table->string('property_type')->default('rumah');
            $table->string('status')->default('dijual');

            $table->unsignedBigInteger('price');
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedInteger('land_area')->nullable();
            $table->unsignedInteger('building_area')->nullable();

            $table->text('address')->nullable();
            $table->text('map_embed')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['listing_type', 'property_type', 'status']);
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
