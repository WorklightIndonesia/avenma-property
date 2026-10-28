<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories duplicated property_type (rumah/apartemen/townhouse) and were
     * never shown or filtered on. property_type stays as the single field.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('listings', function (Blueprint $table) {
            // Nullable on rollback: existing rows have no category to restore.
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
        });
    }
};
