<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('floor')->default(1);
            $table->string('label', 40);
            // Cell coordinates on a fixed grid; the public view draws from them.
            $table->unsignedTinyInteger('x');
            $table->unsignedTinyInteger('y');
            $table->unsignedTinyInteger('w');
            $table->unsignedTinyInteger('h');
            $table->string('tone', 10)->default('biasa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_rooms');
    }
};
