<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Hubungi Kami" now opens the office WhatsApp instead of the contact
     * page, through the /hubungi-kami redirect so the number stays in Settings.
     */
    public function up(): void
    {
        foreach (['cta_label' => 'cta_url', 'cta_label_2' => 'cta_url_2'] as $label => $url) {
            DB::table('page_sections')
                ->where($label, 'Hubungi Kami')
                ->where($url, '/kontak')
                ->update([$url => '/hubungi-kami']);
        }
    }

    public function down(): void
    {
        // The rows cannot be told apart from a link the admin set on purpose.
    }
};
