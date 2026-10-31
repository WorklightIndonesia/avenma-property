<?php

namespace Tests\Feature\Public;

use App\Models\Listing;
use App\Models\PageContent;
use App\Models\SiteSetting;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_three_pages_render_from_the_database(): void
    {
        $this->seed(PageContentSeeder::class);

        foreach (['/', '/kontak', '/syarat-ketentuan'] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/')
            ->assertSee('Temukan properti yang tepat untuk Anda.');

        $this->get('/syarat-ketentuan')
            ->assertSee('Informasi properti')
            ->assertSee('Hak cipta');
    }

    public function test_a_page_without_a_row_is_not_found(): void
    {
        $this->get('/kontak')->assertNotFound();
    }

    public function test_hubungi_kami_buttons_open_the_office_whatsapp(): void
    {
        $this->seed(PageContentSeeder::class);
        SiteSetting::create(['whatsapp' => '087736786969']);

        $this->get('/')
            ->assertSee(url('/hubungi-kami'))
            ->assertSee('href="https://wa.me/6287736786969"', false);

        $this->get('/hubungi-kami')->assertRedirect('https://wa.me/6287736786969');
    }

    public function test_hubungi_kami_falls_back_to_the_contact_page_without_a_number(): void
    {
        $this->get('/hubungi-kami')->assertRedirect(route('public.pages.contact'));
    }

    public function test_edited_copy_reaches_the_public_page(): void
    {
        $this->seed(PageContentSeeder::class);

        $page = PageContent::where('key', 'kontak')->firstOrFail();
        $page->sections()->where('key', 'kontak')->update([
            'heading' => 'Kisah baru',
            'body' => '<p>Paragraf baru.</p>',
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('Kisah baru')
            ->assertSee('Paragraf baru.');
    }

    public function test_meta_title_and_description_override_the_defaults(): void
    {
        $this->seed(PageContentSeeder::class);

        PageContent::where('key', 'kontak')->update([
            'meta_title' => 'Hubungi Tim Avenma',
            'meta_description' => 'Jawaban cepat untuk pertanyaan properti.',
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('<title>Hubungi Tim Avenma - Avenma Properti</title>', false)
            ->assertSee('content="Jawaban cepat untuk pertanyaan properti."', false);
    }

    public function test_the_contact_page_has_no_hero_and_opens_with_its_heading(): void
    {
        $this->seed(PageContentSeeder::class);

        $response = $this->get('/kontak')->assertOk();

        $response->assertDontSee('Foto halaman')
            ->assertDontSee('Hubungi kami.');

        $this->assertStringContainsString('Hubungi Kami', Str::between($response->getContent(), '<h1', '</h1>'));
    }

    public function test_the_contact_page_lists_the_filled_contact_details(): void
    {
        $this->seed(PageContentSeeder::class);

        SiteSetting::create([
            'whatsapp' => '087736786969',
            'email' => 'halo@avenma.id',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'instagram' => 'avenma.properti',
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('https://wa.me/6287736786969', false)
            ->assertSee('mailto:halo@avenma.id', false)
            ->assertSee('Jl. Contoh No. 1, Jakarta')
            ->assertSee('https://instagram.com/avenma.properti', false)
            ->assertSee('maps.google.com/maps?q='.rawurlencode('Jl. Contoh No. 1, Jakarta'), false)
            ->assertSee('Kirim pesan')
            ->assertSee('name="message"', false);
    }

    public function test_empty_contact_details_are_left_out_entirely(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->get('/kontak')
            ->assertOk()
            ->assertDontSee('maps.google.com', false)
            ->assertDontSee('>Alamat<', false)
            ->assertDontSee('mailto:', false)
            ->assertDontSee('instagram.com', false)
            ->assertDontSee('Kirim pesan')
            ->assertDontSee('segera tersedia');
    }

    public function test_the_footer_icons_link_the_contact_channels(): void
    {
        $this->seed(PageContentSeeder::class);

        SiteSetting::create([
            'whatsapp' => '087736786969',
            'email' => 'halo@avenma.id',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'instagram' => 'avenma.properti',
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('https://wa.me/6287736786969', false)
            ->assertSee('https://instagram.com/avenma.properti', false)
            ->assertSee('mailto:halo@avenma.id', false)
            ->assertSee('maps.google.com/maps?q='.rawurlencode('Jl. Contoh No. 1, Jakarta'), false)
            ->assertDontSee('avenmaproperti', false)
            ->assertDontSee('avenma.properti@gmail.com', false)
            ->assertDontSee('maps.app.goo.gl', false);
    }

    public function test_the_saved_google_maps_link_wins_over_the_address(): void
    {
        $this->seed(PageContentSeeder::class);

        SiteSetting::create([
            'whatsapp' => '087736786969',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'maps_url' => 'https://maps.app.goo.gl/yvhXuc3mnWkUjua56',
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('href="https://maps.app.goo.gl/yvhXuc3mnWkUjua56"', false)
            ->assertSee('Buka di Google Maps');
    }

    public function test_the_embedded_map_uses_the_place_coordinates(): void
    {
        $this->seed(PageContentSeeder::class);

        SiteSetting::create([
            'whatsapp' => '087736786969',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'maps_url' => 'https://maps.app.goo.gl/yvhXuc3mnWkUjua56',
            'maps_lat' => -6.1638,
            'maps_lng' => 106.9073,
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSee('maps.google.com/maps?q=-6.1638,106.9073', false);
    }

    public function test_the_home_page_shows_the_featured_section(): void
    {
        $this->seed(PageContentSeeder::class);
        $this->listing(['title' => 'Rumah Tampil']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Properti pilihan')
            ->assertSee('Rumah Tampil');
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }
}
