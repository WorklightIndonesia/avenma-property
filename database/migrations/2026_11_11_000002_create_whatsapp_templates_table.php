<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('message');
            $table->timestamps();
        });

        // The texts as they were hardcoded, so the first deploy changes nothing
        // a visitor sees. The admin edits them from /admin/template-whatsapp.
        $now = now();

        DB::table('whatsapp_templates')->insert(collect([
            'listing_inquiry' => 'Halo, saya tertarik dengan properti "{judul}" ({url}). Mohon informasi lebih lanjut.',
            'listing_visit' => 'Halo, saya {nama}. Saya ingin menjadwalkan kunjungan ke {judul} pada {tanggal}. Mohon konfirmasinya.',
            'layanan_default' => 'Halo Avenma, saya ingin bertanya soal layanan properti. Kebutuhan saya: [jual/beli/sewa/kelola], lokasi: [kota]. Mohon info langkah berikutnya.',
            'sister_virtual_office' => 'Halo Avenma, saya ingin bertanya soal Virtual Office. Saya butuh alamat bisnis untuk [PT/CV] di [kota]. Mohon info paket dan harganya.',
            'sister_it_solutions' => 'Halo Avenma, saya ingin bertanya soal IT & Business Solutions. Saya butuh [website/aplikasi/otomasi] untuk bisnis di bidang [bidang]. Mohon info langkah berikutnya.',
        ])->map(fn (string $message, string $key) => [
            'key' => $key,
            'message' => $message,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
