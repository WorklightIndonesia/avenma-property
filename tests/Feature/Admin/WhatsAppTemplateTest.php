<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_screen(): void
    {
        $this->get('/admin/template-whatsapp')->assertRedirect('/login');
    }

    public function test_admin_sees_every_template_and_service_message(): void
    {
        $service = $this->service(['message' => 'Pesan layanan uji']);

        $this->actingAs($this->admin())
            ->get('/admin/template-whatsapp')
            ->assertOk()
            ->assertSee('Tanya properti')
            ->assertSee('Jadwal kunjungan')
            ->assertSee('Layanan umum')
            ->assertSee('Pesan layanan uji')
            ->assertSee('name="templates[listing_inquiry]"', false)
            ->assertSee('name="services['.$service->id.']"', false);
    }

    public function test_service_templates_are_grouped_by_category(): void
    {
        $this->service(['title' => 'Jual Properti Uji', 'category' => 'properti']);
        $this->service(['title' => 'Desain Uji', 'category' => 'desain-arsitektur']);
        $this->service(['title' => 'Bangun Uji', 'category' => 'konstruksi']);

        $this->actingAs($this->admin())
            ->get('/admin/template-whatsapp')
            ->assertOk()
            ->assertSeeInOrder([
                'Template sistem',
                'Template Layanan Properti',
                'Jual Properti Uji',
                'Template Layanan Desain Arsitektur',
                'Desain Uji',
                'Template Layanan Konstruksi',
                'Bangun Uji',
            ]);
    }

    public function test_admin_saves_a_system_template_and_a_service_message(): void
    {
        $service = $this->service();

        $this->actingAs($this->admin())
            ->put('/admin/template-whatsapp', [
                'templates' => $this->templates(['listing_inquiry' => 'Halo, saya mau tanya {judul}.']),
                'services' => [$service->id => 'Pesan layanan baru'],
            ])
            ->assertRedirect(route('admin.whatsapp-templates.edit'))
            ->assertSessionHas('status');

        $this->assertSame('Halo, saya mau tanya {judul}.', WhatsAppTemplate::message('listing_inquiry'));
        $this->assertSame('Pesan layanan baru', $service->refresh()->message);
    }

    public function test_an_emptied_service_message_falls_back_to_the_general_template(): void
    {
        $service = $this->service();

        $this->actingAs($this->admin())
            ->put('/admin/template-whatsapp', [
                'templates' => $this->templates(),
                'services' => [$service->id => ''],
            ]);

        $this->assertNull($service->refresh()->message);
    }

    public function test_every_system_template_must_be_filled(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/template-whatsapp', [
                'templates' => $this->templates(['listing_visit' => '']),
            ])
            ->assertSessionHasErrors('templates.listing_visit');
    }

    public function test_the_saved_templates_reach_the_public_pages(): void
    {
        SiteSetting::create(['whatsapp' => '081299900387']);
        $listing = $this->listing();

        $this->actingAs($this->admin())
            ->put('/admin/template-whatsapp', [
                'templates' => $this->templates([
                    'listing_inquiry' => 'Saya mau tanya {judul}.',
                    'layanan_default' => 'Saya mau tanya layanan.',
                    'sister_virtual_office' => 'Saya mau tanya virtual office.',
                ]),
            ]);

        $this->get('/properti/'.$listing->slug)
            ->assertOk()
            ->assertSee('https://wa.me/6281299900387?text='.rawurlencode('Saya mau tanya Rumah Uji.'), false)
            ->assertSee('Halo, saya {nama}. Saya ingin menjadwalkan kunjungan ke {judul} pada {tanggal}.', false);

        $this->get('/layanan')
            ->assertOk()
            ->assertSee(rawurlencode('Saya mau tanya layanan.'), false)
            ->assertSee(rawurlencode('Saya mau tanya virtual office.'), false);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function service(array $attributes = []): Service
    {
        return Service::create(array_merge([
            'title' => 'Konsultasi Properti',
            'description' => 'Memberikan pertimbangan harga pasar dan lokasi.',
            'message' => 'Halo Avenma, saya ingin konsultasi properti.',
        ], $attributes));
    }

    private function listing(): Listing
    {
        return Listing::create([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);
    }

    /**
     * Every key filled with its current text, so a test only overrides what it
     * cares about.
     *
     * @return array<string, string>
     */
    private function templates(array $overrides = []): array
    {
        $defaults = [];

        foreach (array_keys(WhatsAppTemplate::TEMPLATES) as $key) {
            $defaults[$key] = WhatsAppTemplate::message($key);
        }

        return array_merge($defaults, $overrides);
    }
}
