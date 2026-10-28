<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use App\Models\SiteSetting;
use App\Support\KprCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_listing_is_shown_by_slug(): void
    {
        $this->listing([
            'title' => 'Rumah Cluster Bintaro',
            'slug' => 'rumah-cluster-bintaro',
            'description' => 'Rumah dua lantai di dalam cluster.',
        ]);

        $this->get('/properti/rumah-cluster-bintaro')
            ->assertOk()
            ->assertSee('Rumah Cluster Bintaro')
            ->assertSee('Rumah dua lantai di dalam cluster.')
            ->assertSee('Rp 1.000.000.000')
            ->assertSee('Kamar tidur');
    }

    public function test_unpublished_and_unknown_listings_are_not_found(): void
    {
        $this->listing(['slug' => 'rumah-disembunyikan', 'is_published' => false]);

        $this->get('/properti/rumah-disembunyikan')->assertNotFound();
        $this->get('/properti/tidak-ada')->assertNotFound();
    }

    public function test_whatsapp_link_uses_the_listing_contact_phone(): void
    {
        SiteSetting::create(['whatsapp' => '087736786969']);
        $this->listing(['slug' => 'rumah-bintaro', 'contact_phone' => '0812-3456-7890']);

        $this->get('/properti/rumah-bintaro')
            ->assertOk()
            ->assertSee('Hubungi Agen')
            ->assertDontSee('Hubungi Avenma')
            ->assertSee('https://wa.me/6281234567890?text=', false)
            ->assertSee(rawurlencode('Rumah Uji'), false);
    }

    public function test_whatsapp_link_falls_back_to_the_office_number(): void
    {
        SiteSetting::create(['whatsapp' => '087736786969']);
        $this->listing(['slug' => 'rumah-bintaro']);

        $this->get('/properti/rumah-bintaro')
            ->assertOk()
            ->assertSee('Hubungi Avenma')
            ->assertSee('https://wa.me/6287736786969?text=', false);
    }

    public function test_whatsapp_button_is_hidden_without_any_number(): void
    {
        $this->listing(['slug' => 'rumah-bintaro']);

        $this->get('/properti/rumah-bintaro')
            ->assertOk()
            ->assertDontSee('wa.me', false);
    }

    public function test_map_uses_the_stored_embed_and_rejects_other_sources(): void
    {
        $listing = $this->listing([
            'slug' => 'rumah-peta',
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=abc"></iframe>',
        ]);

        $this->get('/properti/rumah-peta')
            ->assertSee('https://www.google.com/maps/embed?pb=abc', false);

        $listing->update(['map_embed' => '<iframe src="javascript:alert(1)"></iframe>']);

        $this->get('/properti/rumah-peta')
            ->assertDontSee('javascript:alert', false)
            ->assertSee('https://maps.google.com/maps?q=', false);
    }

    public function test_gallery_images_replace_the_placeholder(): void
    {
        $listing = $this->listing(['slug' => 'rumah-foto']);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => 'Tampak depan rumah',
            'sort_order' => 0,
        ]);

        $this->get('/properti/rumah-foto')
            ->assertOk()
            ->assertSee('/images/properties/foto-1.webp', false)
            ->assertSee('Tampak depan rumah');
    }

    public function test_kpr_widget_is_prefilled_from_the_listing_and_settings(): void
    {
        $this->listing(['slug' => 'rumah-kpr', 'price' => 1_250_000_000]);

        $this->get('/properti/rumah-kpr')
            ->assertOk()
            ->assertSee('Simulasi KPR')
            ->assertSee('value="1.250.000.000"', false)
            ->assertSee('Tenor (tahun)')
            ->assertSee('Minimum 20%. Maksimum 70%.');
    }

    public function test_kpr_panel_renders_the_estimate_server_side(): void
    {
        $this->listing(['slug' => 'rumah-kpr', 'price' => 1_000_000_000]);

        $monthly = KprCalculator::monthly(800_000_000, 7.5, 180);

        $this->get('/properti/rumah-kpr')
            ->assertOk()
            ->assertSee('Angsuran per bulan')
            ->assertSee('Rp '.number_format($monthly, 0, ',', '.'))
            ->assertSee('Rp 200.000.000');
    }

    public function test_gallery_carets_appear_once_there_are_several_photos(): void
    {
        $listing = $this->listing(['slug' => 'rumah-banyak-foto']);

        $this->get('/properti/rumah-banyak-foto')
            ->assertOk()
            ->assertDontSee('Foto berikutnya');

        $listing->images()->createMany([
            ['path' => 'images/properties/foto-1.webp', 'alt' => 'Depan', 'sort_order' => 0],
            ['path' => 'images/properties/foto-2.webp', 'alt' => 'Dapur', 'sort_order' => 1],
        ]);

        $this->get('/properti/rumah-banyak-foto')
            ->assertOk()
            ->assertSee('Foto sebelumnya')
            ->assertSee('Foto berikutnya');
    }

    public function test_card_links_to_the_detail_page(): void
    {
        $this->listing(['title' => 'Rumah Tertaut', 'slug' => 'rumah-tertaut']);

        $this->get('/properti')
            ->assertOk()
            ->assertSee(route('public.listings.show', 'rumah-tertaut'));
    }

    private function listing(array $attributes = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'rumah'], ['name' => 'Rumah']);
        $location = Location::firstOrCreate(['slug' => 'bintaro'], ['name' => 'Bintaro']);

        return Listing::create(array_merge([
            'category_id' => $category->id,
            'location_id' => $location->id,
            'title' => 'Rumah Uji',
            'slug' => Str::random(10),
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'address' => 'Jl. Uji No. 1',
        ], $attributes));
    }
}
