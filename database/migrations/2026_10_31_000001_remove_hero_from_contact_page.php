<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The contact page now opens straight into the two-column contact block,
     * so the hero copy has no place to render.
     */
    public function up(): void
    {
        $pageId = DB::table('page_contents')->where('key', 'kontak')->value('id');

        if ($pageId === null) {
            return;
        }

        DB::table('page_sections')
            ->where('page_content_id', $pageId)
            ->where('key', 'hero')
            ->delete();

        DB::table('page_sections')
            ->where('page_content_id', $pageId)
            ->where('key', 'kontak')
            ->where('heading', 'Cara menghubungi')
            ->update(['heading' => 'Hubungi Kami']);
    }

    public function down(): void
    {
        // The deleted hero copy cannot be recreated, only the heading renamed back.
        $pageId = DB::table('page_contents')->where('key', 'kontak')->value('id');

        if ($pageId === null) {
            return;
        }

        DB::table('page_sections')
            ->where('page_content_id', $pageId)
            ->where('key', 'kontak')
            ->where('heading', 'Hubungi Kami')
            ->update(['heading' => 'Cara menghubungi']);
    }
};
