<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('phone', 20);
            $table->string('property_type');
            $table->text('address');
            $table->string('offer', 10);
            $table->unsignedInteger('land_area')->nullable();
            $table->unsignedInteger('building_area')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('facing', 20)->nullable();
            $table->string('certificate', 32)->nullable();
            $table->unsignedBigInteger('price');
            // Percent kept at submission time, so an old row survives a rate change.
            $table->decimal('commission_rate', 4, 2);
            $table->string('status', 20)->default('baru');
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
