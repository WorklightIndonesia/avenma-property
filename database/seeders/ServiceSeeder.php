<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * The eight service lines the office sells. The admin edits these from
 * /admin/layanan; this seeder only fills an empty table.
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
                'title' => 'Jual & Beli Properti',
                'tags' => 'Rumah, Ruko, Apartemen',
                'description' => 'Membantu pemilik dan calon pembeli menemukan properti yang sesuai dengan kebutuhan, lokasi, dan anggaran.',
                'cta_label' => 'Cari atau jual',
                'message' => 'Halo Avenma, saya ingin jual/beli properti. Saya: [pemilik/pembeli], jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], anggaran: [Rp]. Mohon info langkah berikutnya.',
            ],
            [
                'title' => 'Sewa & Menyewakan Properti',
                'tags' => 'Hunian, Ruko, Kantor, Komersial',
                'description' => 'Melayani kebutuhan sewa maupun pemasaran properti untuk hunian, ruko, kantor, apartemen, dan properti komersial lainnya.',
                'cta_label' => 'Sewa properti',
                'message' => 'Halo Avenma, saya ingin sewa/menyewakan properti. Saya: [pencari sewa/pemilik], jenis properti: [hunian/ruko/kantor/apartemen], lokasi: [kota/kecamatan], durasi: [x bulan], anggaran: [Rp]. Mohon info ketersediaannya.',
            ],
            [
                'title' => 'Pemasaran Properti',
                'tags' => 'Portal Properti, Website, Media Sosial, Jaringan Agen',
                'description' => 'Properti dipasarkan melalui berbagai kanal, termasuk portal properti, website, media sosial, database internal, dan jaringan agen untuk meningkatkan jangkauan calon pembeli atau penyewa.',
                'cta_label' => 'Pasarkan properti',
                'message' => 'Halo Avenma, saya ingin memasarkan properti saya. Jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], harga penawaran: [Rp], target: [dijual/disewakan]. Mohon info paket pemasarannya.',
            ],
            [
                'title' => 'Property Marketing & Listing Management',
                'tags' => 'Foto, Deskripsi, Harga, Listing',
                'description' => 'Membantu pemilik dalam menyiapkan materi pemasaran, foto, deskripsi properti, penentuan harga, hingga pengelolaan listing.',
                'cta_label' => 'Kelola listing',
                'message' => 'Halo Avenma, saya ingin dibantu pemasaran dan pengelolaan listing. Jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], materi yang sudah ada: [foto/deskripsi/belum ada]. Mohon info langkah berikutnya.',
            ],
            [
                'title' => 'Konsultasi Properti',
                'tags' => 'Harga Pasar, Lokasi, Potensi, Strategi',
                'description' => 'Memberikan informasi dan pertimbangan terkait harga pasar, lokasi, potensi properti, serta strategi jual atau sewa.',
                'cta_label' => 'Konsultasi',
                'message' => 'Halo Avenma, saya ingin konsultasi properti. Yang ingin dibahas: [harga pasar/lokasi/potensi/strategi jual-sewa], properti di: [kota/kecamatan]. Boleh saya ceritakan situasinya?',
            ],
            [
                'title' => 'Property Management',
                'tags' => 'Maintenance, Administrasi, Tenant, Pembayaran',
                'description' => 'Membantu pengelolaan properti, termasuk koordinasi maintenance, administrasi, tenant, pembayaran, dan kebutuhan operasional properti.',
                'cta_label' => 'Titip kelola',
                'message' => 'Halo Avenma, saya ingin menitipkan pengelolaan properti. Jenis properti: [rumah/ruko/kantor/apartemen], lokasi: [kota/kecamatan], jumlah unit: [x], saat ini: [kosong/ada penyewa]. Mohon info lingkup dan biayanya.',
            ],
            [
                'title' => 'Commercial Property',
                'tags' => 'Ruko, Kantor, Gudang, Tanah',
                'description' => 'Melayani kebutuhan properti komersial seperti ruko, kantor, gudang, tanah, dan aset komersial lainnya untuk kebutuhan bisnis maupun investasi.',
                'cta_label' => 'Cari komersial',
                'message' => 'Halo Avenma, saya mencari properti komersial. Jenis: [ruko/kantor/gudang/tanah], lokasi: [kota/kecamatan], kebutuhan: [sewa/beli], anggaran: [Rp]. Mohon info pilihannya.',
            ],
            [
                'title' => 'Property Investment',
                'tags' => 'Lokasi, Nilai Aset, Potensi, Peluang',
                'description' => 'Membantu calon investor mencari dan mempertimbangkan properti berdasarkan lokasi, nilai aset, potensi penggunaan, serta peluang investasi.',
                'cta_label' => 'Mulai investasi',
                'message' => 'Halo Avenma, saya ingin berinvestasi properti. Lokasi yang diminati: [kota/kecamatan], anggaran: [Rp], tujuan: [pendapatan sewa/kenaikan nilai]. Mohon rekomendasi dan pertimbangannya.',
            ],
        ];
    }
}
