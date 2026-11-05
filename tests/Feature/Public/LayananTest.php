<?php

namespace Tests\Feature\Public;

use App\Models\SiteSetting;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceSeeder::class);
    }

    public function test_the_page_lists_the_eight_services_in_order(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSeeInOrder([
                'Jual & Beli Properti',
                'Sewa & Menyewakan Properti',
                'Pemasaran Properti',
                'Property Marketing & Listing Management',
                'Konsultasi Properti',
                'Property Management',
                'Commercial Property',
                'Property Investment',
            ])
            ->assertDontSee('Opsi');
    }

    public function test_only_the_first_row_starts_open(): void
    {
        $html = $this->get('/layanan')->getContent();

        $this->assertSame(8, substr_count($html, 'aria-controls="layanan-panel-'));
        $this->assertSame(1, substr_count($html, 'aria-expanded="true"'));
        $this->assertSame(1, substr_count($html, 'grid-template-rows: 1fr'));
        $this->assertSame(7, substr_count($html, 'grid-template-rows: 0fr'));
    }

    public function test_each_service_has_its_own_anchor(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('id="jual-beli-properti"', false)
            ->assertSee('id="property-investment"', false);
    }

    public function test_each_row_links_to_its_own_whatsapp_template(): void
    {
        SiteSetting::create(['whatsapp' => '081299900387']);

        $this->get('/layanan')
            ->assertOk()
            ->assertSee('https://wa.me/6281299900387?text='.rawurlencode('Halo Avenma, saya ingin jual/beli properti. Saya: [pemilik/pembeli], jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], anggaran: [Rp]. Mohon info langkah berikutnya.'), false)
            ->assertSee('https://wa.me/6281299900387?text='.rawurlencode('Halo Avenma, saya ingin berinvestasi properti. Lokasi yang diminati: [kota/kecamatan], anggaran: [Rp], tujuan: [pendapatan sewa/kenaikan nilai]. Mohon rekomendasi dan pertimbangannya.'), false);
    }

    public function test_the_old_service_pages_are_gone(): void
    {
        $this->get('/layanan/arsitektur-interior')->assertNotFound();
        $this->get('/layanan-lama')->assertNotFound();
    }
}
