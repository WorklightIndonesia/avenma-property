<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * The service lines behind /layanan, grouped by the three sections on that
 * page. The admin edits these from /admin/layanan; this seeder only fills rows
 * the table does not have yet.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->services() as $order => $service) {
            Service::updateOrCreate(
                ['title' => $service['title']],
                $service + ['sort_order' => $order],
            );
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private function services(): array
    {
        return [
            [
                'category' => 'properti',
                'title' => 'Jual & Beli Properti',
                'tags' => 'Rumah, Ruko, Apartemen',
                'description' => 'Membantu pemilik dan calon pembeli menemukan properti yang sesuai dengan kebutuhan, lokasi, dan anggaran.',
                'cta_label' => 'Cari atau jual',
                'message' => 'Halo Avenma, saya ingin jual/beli properti. Saya: [pemilik/pembeli], jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], anggaran: [Rp]. Mohon info langkah berikutnya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Sewa & Menyewakan Properti',
                'tags' => 'Hunian, Ruko, Kantor, Komersial',
                'description' => 'Melayani kebutuhan sewa maupun pemasaran properti untuk hunian, ruko, kantor, apartemen, dan properti komersial lainnya.',
                'cta_label' => 'Sewa properti',
                'message' => 'Halo Avenma, saya ingin sewa/menyewakan properti. Saya: [pencari sewa/pemilik], jenis properti: [hunian/ruko/kantor/apartemen], lokasi: [kota/kecamatan], durasi: [x bulan], anggaran: [Rp]. Mohon info ketersediaannya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Pemasaran Properti',
                'tags' => 'Portal Properti, Website, Media Sosial, Jaringan Agen',
                'description' => 'Properti dipasarkan melalui berbagai kanal, termasuk portal properti, website, media sosial, database internal, dan jaringan agen untuk meningkatkan jangkauan calon pembeli atau penyewa.',
                'cta_label' => 'Pasarkan properti',
                'message' => 'Halo Avenma, saya ingin memasarkan properti saya. Jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], harga penawaran: [Rp], target: [dijual/disewakan]. Mohon info paket pemasarannya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Property Marketing & Listing Management',
                'tags' => 'Foto, Deskripsi, Harga, Listing',
                'description' => 'Membantu pemilik dalam menyiapkan materi pemasaran, foto, deskripsi properti, penentuan harga, hingga pengelolaan listing.',
                'cta_label' => 'Kelola listing',
                'message' => 'Halo Avenma, saya ingin dibantu pemasaran dan pengelolaan listing. Jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], materi yang sudah ada: [foto/deskripsi/belum ada]. Mohon info langkah berikutnya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Konsultasi Properti',
                'tags' => 'Harga Pasar, Lokasi, Potensi, Strategi',
                'description' => 'Memberikan informasi dan pertimbangan terkait harga pasar, lokasi, potensi properti, serta strategi jual atau sewa.',
                'cta_label' => 'Konsultasi',
                'message' => 'Halo Avenma, saya ingin konsultasi properti. Yang ingin dibahas: [harga pasar/lokasi/potensi/strategi jual-sewa], properti di: [kota/kecamatan]. Boleh saya ceritakan situasinya?',
            ],
            [
                'category' => 'properti',
                'title' => 'Property Management',
                'tags' => 'Maintenance, Administrasi, Tenant, Pembayaran',
                'description' => 'Membantu pengelolaan properti, termasuk koordinasi maintenance, administrasi, tenant, pembayaran, dan kebutuhan operasional properti.',
                'cta_label' => 'Titip kelola',
                'message' => 'Halo Avenma, saya ingin menitipkan pengelolaan properti. Jenis properti: [rumah/ruko/kantor/apartemen], lokasi: [kota/kecamatan], jumlah unit: [x], saat ini: [kosong/ada penyewa]. Mohon info lingkup dan biayanya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Commercial Property',
                'tags' => 'Ruko, Kantor, Gudang, Tanah',
                'description' => 'Melayani kebutuhan properti komersial seperti ruko, kantor, gudang, tanah, dan aset komersial lainnya untuk kebutuhan bisnis maupun investasi.',
                'cta_label' => 'Cari komersial',
                'message' => 'Halo Avenma, saya mencari properti komersial. Jenis: [ruko/kantor/gudang/tanah], lokasi: [kota/kecamatan], kebutuhan: [sewa/beli], anggaran: [Rp]. Mohon info pilihannya.',
            ],
            [
                'category' => 'properti',
                'title' => 'Property Investment',
                'tags' => 'Lokasi, Nilai Aset, Potensi, Peluang',
                'description' => 'Membantu calon investor mencari dan mempertimbangkan properti berdasarkan lokasi, nilai aset, potensi penggunaan, serta peluang investasi.',
                'cta_label' => 'Mulai investasi',
                'message' => 'Halo Avenma, saya ingin berinvestasi properti. Lokasi yang diminati: [kota/kecamatan], anggaran: [Rp], tujuan: [pendapatan sewa/kenaikan nilai]. Mohon rekomendasi dan pertimbangannya.',
            ],
            [
                'category' => 'desain-arsitektur',
                'title' => 'Desain Rumah Tinggal',
                'tags' => 'Baru, Renovasi, Ekstensi',
                'description' => 'Merancang tata ruang dan tampilan rumah tinggal sesuai kebutuhan penghuni, bentuk lahan, dan anggaran yang tersedia.',
                'cta_label' => 'Mulai desain',
                'message' => 'Halo Avenma, saya ingin jasa desain rumah tinggal. Lokasi lahan: [kota/kecamatan], luas: [m2], jumlah lantai: [x], anggaran: [Rp]. Mohon info lingkup dan tahapannya.',
            ],
            [
                'category' => 'desain-arsitektur',
                'title' => 'Desain Interior',
                'tags' => 'Hunian, Kantor, Ritel',
                'description' => 'Menata interior ruang agar nyaman dipakai dan sesuai karakter penghuninya, mulai dari konsep sampai daftar material.',
                'cta_label' => 'Konsultasi interior',
                'message' => 'Halo Avenma, saya ingin jasa desain interior. Ruang: [rumah/kantor/ritel], luas: [m2], gaya yang disukai: [minimalis/modern/lainnya], anggaran: [Rp]. Mohon info langkah berikutnya.',
            ],
            [
                'category' => 'desain-arsitektur',
                'title' => 'Gambar Kerja & Visual 3D',
                'tags' => 'Denah, Render, Detail',
                'description' => 'Menyiapkan gambar kerja dan visual 3D supaya rencana desain terbaca jelas oleh pemilik, tukang, dan vendor.',
                'cta_label' => 'Minta contoh',
                'message' => 'Halo Avenma, saya butuh gambar kerja dan visual 3D. Jenis bangunan: [rumah/ruko/kantor], lokasi: [kota/kecamatan], gambar yang sudah ada: [belum ada/denah awal]. Mohon info waktu pengerjaannya.',
            ],
            [
                'category' => 'konstruksi',
                'title' => 'Pembangunan Baru',
                'tags' => 'Fondasi, Struktur, Finishing',
                'description' => 'Membangun rumah dari nol dengan perencanaan biaya, jadwal, dan pengawasan di setiap tahap pekerjaan.',
                'cta_label' => 'Hitung biaya',
                'message' => 'Halo Avenma, saya ingin membangun rumah baru. Lokasi: [kota/kecamatan], luas tanah/bangunan: [m2], jumlah lantai: [x], desain: [sudah ada/belum ada], anggaran: [Rp]. Mohon info estimasi biaya dan jadwalnya.',
            ],
            [
                'category' => 'konstruksi',
                'title' => 'Renovasi',
                'tags' => 'Perbaikan, Ekstensi, Struktur',
                'description' => 'Memperbaiki dan menambah bangunan yang sudah ada, termasuk pemeriksaan struktur sebelum pekerjaan dimulai.',
                'cta_label' => 'Rencanakan renovasi',
                'message' => 'Halo Avenma, saya ingin renovasi/tambah lantai. Jenis bangunan: [rumah/ruko/kantor], lokasi: [kota/kecamatan], pekerjaan: [renovasi/tambah lantai], anggaran: [Rp]. Mohon info langkah dan estimasinya.',
            ],
        ];
    }
}
