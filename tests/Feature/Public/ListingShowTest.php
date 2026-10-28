<?php

namespace Tests\Feature\Public;

use App\Models\Listing;
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

    public function test_map_prefers_the_picked_point(): void
    {
        $this->listing([
            'slug' => 'rumah-peta',
            'latitude' => -6.2615,
            'longitude' => 106.8,
        ]);

        $this->get('/properti/rumah-peta')
            ->assertOk()
            ->assertSee('https://maps.google.com/maps?q=-6.2615,106.8&amp;z=17&amp;output=embed', false);
    }

    public function test_map_falls_back_to_the_address_text(): void
    {
        $this->listing(['slug' => 'rumah-peta', 'address' => 'Jl. Uji No. 1']);

        $this->get('/properti/rumah-peta')
            ->assertOk()
            ->assertSee('https://maps.google.com/maps?q='.rawurlencode('Jl. Uji No. 1'), false);
    }

    public function test_map_is_hidden_without_a_point_or_address(): void
    {
        $this->listing(['slug' => 'rumah-peta', 'address' => null]);

        $this->get('/properti/rumah-peta')
            ->assertOk()
            ->assertDontSee('Peta lokasi', false);
    }

    public function test_gallery_images_replace_the_placeholder(): void
    {
        $listing = $this->listing(['slug' => 'rumah-foto']);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => 'Tampak depan rumah',
            'label' => 'Tampak depan',
            'sort_order' => 0,
        ]);

        $listing->images()->create([
            'path' => 'images/properties/foto-2.webp',
            'alt' => 'Dapur rumah',
            'label' => 'Dapur',
            'sort_order' => 1,
        ]);

        $this->get('/properti/rumah-foto')
            ->assertOk()
            ->assertSee('/images/properties/foto-1.webp', false)
            ->assertSee('Tampilkan foto 1: Tampak depan')
            ->assertSee('Tampilkan foto 2: Dapur');
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

    public function test_a_rented_listing_shows_no_kpr_simulation(): void
    {
        $this->listing(['slug' => 'rumah-disewa', 'status' => 'disewa']);
        $this->listing(['slug' => 'rumah-dijual', 'status' => 'dijual']);

        $this->get('/properti/rumah-disewa')
            ->assertOk()
            ->assertDontSee('Simulasi KPR');

        $this->get('/properti/rumah-dijual')
            ->assertOk()
            ->assertSee('Simulasi KPR');
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

    public function test_land_area_and_floors_show_for_a_house(): void
    {
        $this->listing([
            'slug' => 'rumah-dua-lantai',
            'land_area' => 120,
            'building_area' => 90,
            'floors' => 2,
        ]);

        $this->get('/properti/rumah-dua-lantai')
            ->assertOk()
            ->assertSee('Luas tanah')
            ->assertSee('120 m²')
            ->assertSee('Jumlah lantai')
            ->assertSee('2 lantai');
    }

    public function test_an_apartment_does_not_show_land_area_or_floors(): void
    {
        $this->listing([
            'slug' => 'apartemen-sudirman',
            'property_type' => 'apartemen',
            'land_area' => 68,
            'building_area' => 68,
            'floors' => 5,
        ]);

        $this->get('/properti/apartemen-sudirman')
            ->assertOk()
            ->assertSee('Luas unit')
            ->assertDontSee('Luas tanah')
            ->assertDontSee('Luas bangunan')
            ->assertDontSee('Jumlah lantai');
    }

    public function test_a_land_listing_shows_land_area_instead_of_building_area(): void
    {
        $this->listing([
            'slug' => 'tanah-ciputat',
            'property_type' => 'tanah',
            'land_area' => 500,
            'building_area' => 100,
        ]);

        $this->get('/properti/tanah-ciputat')
            ->assertOk()
            ->assertSee('Luas tanah')
            ->assertSee('500 m²')
            ->assertDontSee('Luas bangunan');
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
            'bedrooms' => 3,
            'bathrooms' => 2,
            'address' => 'Jl. Uji No. 1',
        ], $attributes));
    }
}
