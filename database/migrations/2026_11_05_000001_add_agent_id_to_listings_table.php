<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('developer_id')->constrained()->nullOnDelete();
            $table->dropColumn('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('contact_phone')->nullable()->after('address');
            $table->dropConstrainedForeignId('agent_id');
        });
    }
};
