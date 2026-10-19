<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpr_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('interest_rate', 5, 2)->default(7.50);
            $table->decimal('min_dp_percent', 5, 2)->default(20.00);
            $table->unsignedTinyInteger('max_tenor_years')->default(20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpr_settings');
    }
};
