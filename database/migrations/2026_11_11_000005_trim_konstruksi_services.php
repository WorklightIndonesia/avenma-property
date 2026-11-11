<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Konstruksi sells two services, not three: the fit-out row is dropped and
     * the other two are named the way the office names them.
     */
    public function up(): void
    {
        DB::table('services')
            ->where('category', 'konstruksi')
            ->where('title', 'Bangun Rumah Baru')
            ->update(['title' => 'Pembangunan Baru', 'updated_at' => now()]);

        DB::table('services')
            ->where('category', 'konstruksi')
            ->where('title', 'Renovasi & Tambah Lantai')
            ->update(['title' => 'Renovasi', 'updated_at' => now()]);

        DB::table('services')
            ->where('category', 'konstruksi')
            ->where('title', 'Fit-out Komersial')
            ->delete();
    }

    public function down(): void
    {
        DB::table('services')
            ->where('category', 'konstruksi')
            ->where('title', 'Pembangunan Baru')
            ->update(['title' => 'Bangun Rumah Baru', 'updated_at' => now()]);

        DB::table('services')
            ->where('category', 'konstruksi')
            ->where('title', 'Renovasi')
            ->update(['title' => 'Renovasi & Tambah Lantai', 'updated_at' => now()]);

        if (! DB::table('services')->where('title', 'Fit-out Komersial')->exists()) {
            $now = now();

            DB::table('services')->insert([
                'category' => 'konstruksi',
                'title' => 'Fit-out Komersial',
                'tags' => 'Ruko, Kantor, Ritel',
                'description' => 'Menyiapkan ruang komersial agar siap dipakai usaha, dari partisi dan kelistrikan sampai finishing.',
                'cta_label' => 'Diskusikan fit-out',
                'message' => 'Halo Avenma, saya butuh fit-out ruang komersial. Jenis: [ruko/kantor/ritel], lokasi: [kota/kecamatan], luas: [m2], target selesai: [tanggal]. Mohon info lingkup dan biayanya.',
                'sort_order' => (int) DB::table('services')->max('sort_order') + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
