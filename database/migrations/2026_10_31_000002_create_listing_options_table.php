<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_options', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30);
            $table->string('label', 60);
            $table->string('slug', 60);
            $table->timestamps();

            $table->unique(['group', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_options');
    }
};
