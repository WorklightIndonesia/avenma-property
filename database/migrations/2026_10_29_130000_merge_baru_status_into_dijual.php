<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Baru" is now derived from created_at (last 30 days), so the old status
     * value only ever meant a new launch that was for sale.
     */
    public function up(): void
    {
        DB::table('listings')->where('status', 'baru')->update(['status' => 'dijual']);
    }

    public function down(): void
    {
        // The old rows cannot be told apart from ordinary sales anymore.
    }
};
