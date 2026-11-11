<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Production already has its pages, so PageContentSeeder will not run there
     * again. This inserts the new page only when it is missing, and never
     * touches copy an admin has edited.
     */
    public function up(): void
    {
        $now = now();

        $pageId = DB::table('page_contents')->where('key', 'titip')->value('id');

        if (! $pageId) {
            $pageId = DB::table('page_contents')->insertGetId([
                'key' => 'titip',
                'title' => 'Titip Properti',
                'meta_title' => 'Titip Jual atau Sewa Properti',
                'meta_description' => 'Titipkan properti Anda untuk dijual atau disewakan lewat Avenma Properti. Isi data properti, tim kami menghubungi lewat WhatsApp.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('page_sections')->insertOrIgnore([[
            'page_content_id' => $pageId,
            'key' => 'hero',
            'heading' => 'Titipkan properti Anda kepada Avenma',
            'body' => '<p>Isi data properti di samping. Tim Avenma akan menghubungi Anda lewat WhatsApp untuk memastikan detail, foto, dan langkah berikutnya.</p>'
                .'<p>Ketentuan komisi: <strong>jual 2,5%</strong> dan <strong>sewa 5%</strong> dari harga transaksi.</p>',
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]]);
    }

    public function down(): void
    {
        $pageId = DB::table('page_contents')->where('key', 'titip')->value('id');

        if ($pageId) {
            DB::table('page_sections')->where('page_content_id', $pageId)->delete();
            DB::table('page_contents')->where('id', $pageId)->delete();
        }
    }
};
