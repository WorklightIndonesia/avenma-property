<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Ten placeholder articles so the blog is readable before the client sends
 * real copy. Replace the rows in the admin, not by editing this file.
 */
class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'agenbudi@gmail.com')->firstOrFail();

        foreach ($this->articles() as $index => $article) {
            BlogPost::updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'created_by' => $author->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'is_published' => true,
                    'published_at' => now()->subDays($index * 4),
                ],
            );
        }
    }

    /**
     * @return list<array{title: string, excerpt: string, content: string}>
     */
    private function articles(): array
    {
        return [
            [
                'title' => 'Membeli rumah pertama: lima biaya yang sering terlewat',
                'excerpt' => 'DP bukan satu-satunya angka di depan. Biaya akta, pajak pembeli, dan provisi bank sering baru muncul setelah harga disepakati.',
                'content' => <<<'HTML'
<p>Harga yang tertulis di brosur bukan jumlah uang yang perlu Anda siapkan. Selisihnya jarang kecil, dan hampir selalu muncul setelah harga disepakati.</p>
<h2>Biaya yang tidak masuk brosur</h2>
<ul>
<li>Pajak pembeli (BPHTB) dan biaya akta jual beli di notaris.</li>
<li>Provisi, administrasi, dan asuransi bila memakai KPR.</li>
<li>Appraisal bank, biasanya dibayar di muka.</li>
<li>Renovasi kecil, sambungan listrik dan air, serta biaya pindah.</li>
</ul>
<p>Minta rincian tertulis dari developer atau agen sebelum akad. Angka yang tidak masuk brosur tetap harus masuk hitungan Anda.</p>
<blockquote>Rumah yang tepat bukan yang termurah, tetapi yang biayanya bisa Anda jelaskan sampai rupiah terakhir.</blockquote>
HTML,
            ],
            [
                'title' => 'Cara membaca sertifikat rumah sebelum tanda tangan',
                'excerpt' => 'Nama pemilik, luas, dan catatan di lembar sertifikat menentukan apakah transaksi bisa jalan. Periksa tiga hal ini lebih dulu.',
                'content' => <<<'HTML'
<p>Sertifikat adalah dokumen yang menentukan sahnya kepemilikan. Sebelum uang muka berpindah tangan, pastikan Anda membaca salinan aslinya, bukan foto yang dikirim lewat pesan.</p>
<h2>Yang perlu dicocokkan</h2>
<ul>
<li>Nama pemilik di sertifikat dengan KTP penjual.</li>
<li>Luas tanah dan bangunan dengan kondisi di lapangan.</li>
<li>Catatan hak tanggungan atau sengketa di halaman belakang.</li>
</ul>
<p>Bila sertifikat masih atas nama orang lain, minta surat kuasa yang sah dan telusuri rantai kepemilikannya. Notaris bisa membantu mengecek ke kantor pertanahan setempat.</p>
HTML,
            ],
            [
                'title' => 'KPR atau tunai: mana yang masuk akal tahun ini',
                'excerpt' => 'Bunga KPR bisa berubah setelah masa fixed berakhir. Bandingkan angsuran pertama dengan kemampuan bayar jangka panjang, bukan hanya hari ini.',
                'content' => <<<'HTML'
<p>Membeli tunai menghindari bunga, tetapi mengunci seluruh uang Anda di satu aset. KPR menjaga kas tetap cair dengan biaya bunga yang harus dihitung jujur.</p>
<h2>Tiga angka yang menentukan</h2>
<ul>
<li>Angsuran setelah masa bunga tetap berakhir, bukan angsuran promosi.</li>
<li>Sisa uang setelah DP dan biaya awal, minimal enam bulan biaya hidup.</li>
<li>Selisih total pembayaran tunai dan kredit pada tenor yang sama.</li>
</ul>
<p>Bila angsuran setelah kenaikan bunga masih di bawah sepertiga penghasilan bulanan, KPR masuk akal. Bila tidak, memperbesar DP atau menurunkan harga target adalah jalan yang lebih aman.</p>
<blockquote>Hitung dengan bunga tertinggi yang mungkin, lalu putuskan dengan angka itu.</blockquote>
HTML,
            ],
            [
                'title' => 'Memilih lokasi: jarak ke stasiun atau ke sekolah',
                'excerpt' => 'Lokasi tidak bisa direnovasi. Putuskan kebutuhan mana yang paling sering dipakai keluarga, lalu ukur jaraknya pada jam sibuk.',
                'content' => <<<'HTML'
<p>Dua rumah dengan harga sama bisa berbeda nilainya karena lokasi. Yang menentukan bukan peta, tetapi rutinitas harian penghuninya.</p>
<h2>Cara mengukur</h2>
<ul>
<li>Tempuh rute ke tempat kerja dan sekolah pada jam berangkat, bukan hari Minggu.</li>
<li>Cek banjir dan kemacetan dari tetangga, bukan dari penjual.</li>
<li>Lihat rencana pembangunan di sekitar: jalan tol baru bisa menaikkan harga, tetapi juga kebisingan.</li>
</ul>
<p>Kalau keluarga lebih sering memakai sekolah daripada stasiun, jarak ke sekolah yang layak diutamakan. Prioritas yang jelas membuat daftar pilihan cepat menyusut.</p>
HTML,
            ],
            [
                'title' => 'Rumah primer dan sekunder: bedanya bagi pembeli',
                'excerpt' => 'Primer berarti dari developer, sekunder berarti dari pemilik sebelumnya. Bedanya bukan hanya harga, tetapi juga risiko dan proses.',
                'content' => <<<'HTML'
<p>Istilah primer dan sekunder sering dipakai di iklan properti, tetapi jarang dijelaskan artinya bagi pembeli.</p>
<h2>Primer</h2>
<p>Dibeli langsung dari developer. Pilihan tipe dan spesifikasi masih bisa dinegosiasikan sebelum akad, dan sertifikat biasanya terbit setelah serah terima. Risikonya ada pada ketepatan jadwal pembangunan.</p>
<h2>Sekunder</h2>
<p>Dibeli dari pemilik sebelumnya. Kondisi rumah bisa dilihat apa adanya dan sertifikat umumnya sudah ada. Risikonya ada pada dokumen, tunggakan, dan kondisi bangunan yang tidak terlihat.</p>
<p>Untuk pembeli pertama, sekunder sering lebih murah per meter. Untuk yang menginginkan serah terima tanpa renovasi, primer bisa lebih praktis.</p>
HTML,
            ],
            [
                'title' => 'Biaya memiliki rumah yang jarang dihitung',
                'excerpt' => 'Pajak bumi, perawatan, dan asuransi berjalan setiap tahun. Angka ini jarang masuk simulasi pembeli pertama.',
                'content' => <<<'HTML'
<p>Setelah kunci berpindah tangan, rumah tetap memakan biaya. Menyiapkan anggaran tahunan mencegah kejutan di bulan-bulan pertama.</p>
<h2>Pos yang perlu dianggarkan</h2>
<ul>
<li>Pajak bumi dan bangunan, biasanya setahun sekali.</li>
<li>Perawatan rutin: atap, pompa air, pengecatan, dan taman.</li>
<li>Asuransi rumah bila dipakai sebagai jaminan kredit.</li>
<li>Iuran lingkungan atau keamanan bila tinggal di cluster.</li>
</ul>
<p>Patokan sederhana: sisihkan satu persen dari nilai rumah per tahun untuk perawatan. Untuk rumah berusia di atas sepuluh tahun, siapkan lebih.</p>
HTML,
            ],
            [
                'title' => 'Survei rumah bekas: apa yang harus dilihat sendiri',
                'excerpt' => 'Bau, retakan, dan suara atap saat hujan tidak muncul di foto. Datang dua kali, pagi dan sore, sebelum memutuskan.',
                'content' => <<<'HTML'
<p>Foto bagus tidak menunjukkan kelembapan. Survei langsung, di jam berbeda, adalah cara termurah menghindari perbaikan besar.</p>
<h2>Periksa dari luar ke dalam</h2>
<ul>
<li>Retakan diagonal di dinding, terutama di dekat kusen pintu dan jendela.</li>
<li>Bekas air di langit-langit dan dinding kamar mandi.</li>
<li>Tekanan air dari kran paling jauh, dan aliran pembuangan.</li>
<li>Suara atap saat hujan, tanda genteng bergeser.</li>
</ul>
<p>Bila memungkinkan, ajak tukang yang biasa Anda percaya. Perkiraan biaya perbaikan dari satu kali survei lebih berguna daripada brosur mana pun.</p>
HTML,
            ],
            [
                'title' => 'Menawar harga properti tanpa memutus negosiasi',
                'excerpt' => 'Tawaran rendah tanpa alasan ditolak. Tawaran dengan data dan batas waktu yang jelas lebih sering diterima.',
                'content' => <<<'HTML'
<p>Menawar bukan soal angka terendah, tetapi soal alasan yang bisa diperiksa penjual.</p>
<h2>Urutan yang bekerja</h2>
<ul>
<li>Kumpulkan dua atau tiga properti pembanding yang sudah terjual di area sama.</li>
<li>Tentukan angka maksimum Anda sebelum bertemu, lalu jangan melampauinya.</li>
<li>Ajukan tawaran dengan alasan, misalnya perbaikan atap atau sertifikat yang belum pecah.</li>
</ul>
<p>Beri ruang bagi penjual untuk menanggapi tanpa merasa dipermainkan. Negosiasi yang sehat berakhir dengan dua pihak yang sama-sama tahu angka dan syaratnya.</p>
<blockquote>Tawaran yang baik menjelaskan dasarnya, bukan hanya angkanya.</blockquote>
HTML,
            ],
            [
                'title' => 'Pajak pembeli rumah: BPHTB dan PPN ringkas',
                'excerpt' => 'Dua pungutan ini paling sering ditanyakan pembeli. Pahami siapa yang menanggung dan kapan dibayarkan.',
                'content' => <<<'HTML'
<p>Biaya pajak berbeda antara pembelian dari developer dan dari perorangan. Memahaminya lebih awal membuat anggaran tidak meleset.</p>
<h2>BPHTB</h2>
<p>Pajak perolehan hak atas tanah dan bangunan, dibayar pembeli. Dasar pengenaannya nilai transaksi dikurangi batas nilai yang tidak kena pajak, dikalikan tarif yang berlaku di daerah setempat. Nilainya berbeda tiap kabupaten dan kota.</p>
<h2>PPN</h2>
<p>Dikenakan pada penyerahan rumah baru oleh developer yang merupakan pengusaha kena pajak. Pembelian dari perorangan umumnya tidak dikenai PPN, tetapi tetap kena BPHTB.</p>
<p>Tanyakan angka final keduanya ke notaris atau pejabat pembuat akta sebelum tanda tangan, karena hitungannya mengikuti nilai yang ditetapkan pemerintah daerah.</p>
HTML,
            ],
            [
                'title' => 'Menyewa atau membeli: hitungan sederhana',
                'excerpt' => 'Bandingkan biaya tahunan menyewa dengan bunga dan perawatan rumah, bukan harga rumah dengan uang sewa bulanan.',
                'content' => <<<'HTML'
<p>Perbandingan yang adil memakai periode yang sama, biasanya lima sampai sepuluh tahun.</p>
<h2>Yang dibandingkan</h2>
<ul>
<li>Total uang sewa selama periode itu, termasuk kenaikan tahunan.</li>
<li>Bunga KPR, biaya awal, pajak, dan perawatan selama periode yang sama.</li>
<li>Kenaikan nilai rumah, yang tidak pernah pasti.</li>
</ul>
<p>Bila menyewa jauh lebih murah dan uang sisanya diinvestasikan secara disiplin, menyewa bisa lebih menguntungkan. Bila Anda butuh kepastian tinggal dan kenaikan harga di area itu kuat, membeli lebih masuk akal.</p>
HTML,
            ],
        ];
    }
}
