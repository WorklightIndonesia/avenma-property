<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable()->after('description');
            $table->string('email', 190)->nullable()->after('whatsapp');
        });

        // Rows saved before this column existed get a placeholder number and
        // address, so the edit form opens with something to replace rather than
        // an empty contact. The numbers are random on purpose: nobody's real
        // number is invented here.
        foreach (DB::table('developers')->select('id', 'slug')->get() as $developer) {
            DB::table('developers')->where('id', $developer->id)->update([
                'whatsapp' => '08'.str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'email' => $developer->slug.'@example.com',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'email']);
        });
    }
};
