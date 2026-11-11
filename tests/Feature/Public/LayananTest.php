<?php

namespace Tests\Feature\Public;

use App\Models\SiteSetting;
use App\Support\SisterService;
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

    public function test_only_the_first_row_of_each_section_starts_open(): void
    {
        $html = $this->get('/layanan')->getContent();

        $this->assertSame(13, substr_count($html, 'aria-controls="layanan-panel-'));
        $this->assertSame(3, substr_count($html, 'aria-expanded="true"'));
        $this->assertSame(3, substr_count($html, 'grid-template-rows: 1fr'));
        $this->assertSame(10, substr_count($html, 'grid-template-rows: 0fr'));
    }

    public function test_each_service_has_its_own_anchor(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('id="jual-beli-properti"', false)
            ->assertSee('id="property-investment"', false)
            ->assertSee('id="desain-rumah-tinggal"', false)
            ->assertSee('id="pembangunan-baru"', false)
            ->assertSee('id="renovasi"', false)
            ->assertDontSee('id="fit-out-komersial"', false);
    }

    public function test_each_section_has_a_chip_that_switches_the_list(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('href="#properti"', false)
            ->assertSee('href="#desain-arsitektur"', false)
            ->assertSee('href="#konstruksi"', false)
            ->assertSee('>Properti</a>', false)
            ->assertSee('>Desain Arsitektur</a>', false)
            ->assertSee('>Konstruksi</a>', false);
    }

    public function test_only_the_chosen_section_is_visible(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('data-category="properti"', false)
            ->assertSee('data-category="desain-arsitektur"', false)
            ->assertSee('data-category="konstruksi"', false)
            ->assertSee('x-show="active === \'properti\'"', false)
            ->assertSee('x-show="active === \'konstruksi\'"', false)
            ->assertSee('id="properti"', false)
            ->assertSee('id="desain-arsitektur"', false)
            ->assertSee('id="konstruksi"', false);
    }

    public function test_the_block_is_titled_and_each_section_keeps_its_own_heading(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('>Layanan Kami</h2>', false)
            ->assertSee('id="layanan-properti"', false)
            ->assertSee('id="layanan-desain-arsitektur"', false)
            ->assertSee('id="layanan-konstruksi"', false)
            ->assertSee('aria-labelledby="layanan-konstruksi"', false);
    }

    public function test_the_navbar_links_to_each_section(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('href="'.url('/layanan').'#properti"', false)
            ->assertSee('href="'.url('/layanan').'#desain-arsitektur"', false)
            ->assertSee('href="'.url('/layanan').'#konstruksi"', false);
    }

    public function test_each_row_links_to_its_own_whatsapp_template(): void
    {
        SiteSetting::create(['whatsapp' => '081299900387']);

        $this->get('/layanan')
            ->assertOk()
            ->assertSee('https://wa.me/6281299900387?text='.rawurlencode('Halo Avenma, saya ingin jual/beli properti. Saya: [pemilik/pembeli], jenis properti: [rumah/ruko/apartemen], lokasi: [kota/kecamatan], anggaran: [Rp]. Mohon info langkah berikutnya.'), false)
            ->assertSee('https://wa.me/6281299900387?text='.rawurlencode('Halo Avenma, saya ingin berinvestasi properti. Lokasi yang diminati: [kota/kecamatan], anggaran: [Rp], tujuan: [pendapatan sewa/kenaikan nilai]. Mohon rekomendasi dan pertimbangannya.'), false);
    }

    public function test_the_sister_services_ask_avenma_on_whatsapp_instead_of_linking_out(): void
    {
        SiteSetting::create(['whatsapp' => '081299900387']);

        $html = $this->get('/layanan')->getContent();

        foreach (SisterService::all() as $sister) {
            $this->assertStringContainsString(
                'https://wa.me/6281299900387?text='.rawurlencode($sister['message']),
                $html
            );
        }

        $this->assertStringNotContainsString('startspace.co.id', $html);
        $this->assertStringNotContainsString('worklight.id', $html);
    }

    public function test_the_old_service_pages_are_gone(): void
    {
        $this->get('/layanan/arsitektur-interior')->assertNotFound();
        $this->get('/layanan-lama')->assertNotFound();
    }
}
