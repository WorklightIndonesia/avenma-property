<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The CMS now has one login. Agen accounts become agent records managed from
 * the Agen screen, so their rows are removed here; the content they wrote is
 * handed to the admin first, otherwise the nullOnDelete foreign keys would
 * blank every byline and the Agen column.
 */
return new class extends Migration
{
    public function up(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        if ($admin === null) {
            return;
        }

        $agen = DB::table('users')->where('role', 'agen')->pluck('id');

        if ($agen->isEmpty()) {
            return;
        }

        DB::table('listings')->whereIn('created_by', $agen)->update(['created_by' => $admin]);
        DB::table('blog_posts')->whereIn('created_by', $agen)->update(['created_by' => $admin]);

        DB::table('users')->whereIn('id', $agen)->delete();
    }

    public function down(): void
    {
        // Deleted accounts cannot be rebuilt; the agent records carry the data.
    }
};
