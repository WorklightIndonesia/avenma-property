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

    public function test_all_four_pages_render_from_the_database(): void
    {
        $this->seed(PageContentSeeder::class);

        foreach (['/', '/tentang-kami', '/kontak', '/syarat-ketentuan'] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/')
            ->assertSee('Temukan properti yang tepat untuk Anda.');

        $this->get('/tentang-kami')
            ->assertSee('Siapa Avenma')
            ->assertSee('Yang kami pegang');

        $this->get('/syarat-ketentuan')
            ->assertSee('Informasi properti')
            ->assertSee('Hak cipta');
    }

    public function test_a_page_without_a_row_is_not_found(): void
    {
        $this->get('/tentang-kami')->assertNotFound();
    }

    public function test_edited_copy_reaches_the_public_page(): void
    {
        $this->seed(PageContentSeeder::class);

        $page = PageContent::where('key', 'tentang-kami')->firstOrFail();
        $page->sections()->where('key', 'cerita')->update([
            'heading' => 'Kisah baru',
            'body' => '<p>Paragraf baru.</p>',
        ]);

        $this->get('/tentang-kami')
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
            ->assertSee('maps.google.com/maps?q='.rawurlencode('Jl. Contoh No. 1, Jakarta'), false);
    }

    public function test_empty_contact_details_are_left_out_entirely(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->get('/kontak')
            ->assertOk()
            ->assertDontSee('mailto:', false)
            ->assertDontSee('instagram.com', false)
            ->assertDontSee('segera tersedia');
    }

    public function test_the_footer_links_the_filled_contacts(): void
    {
        $this->seed(PageContentSeeder::class);

        SiteSetting::create(['whatsapp' => '087736786969', 'email' => 'halo@avenma.id']);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('https://wa.me/6287736786969', false)
            ->assertSee('mailto:halo@avenma.id', false)
            ->assertSee('Syarat &amp; Ketentuan', false);
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
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }
}
