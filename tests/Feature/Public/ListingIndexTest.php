<?php

namespace Tests\Feature\Public;

use App\Models\Developer;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_listings_are_listed(): void
    {
        $this->listing(['title' => 'Rumah Terbit']);
        $this->listing(['title' => 'Rumah Disembunyikan', 'is_published' => false]);

        $this->get('/properti')
            ->assertOk()
            ->assertSee('Rumah Terbit')
            ->assertDontSee('Rumah Disembunyikan');
    }

    public function test_region_filter_narrows_the_list_at_each_level(): void
    {
        $pondokAren = $this->location('Pondok Aren', 'Kota Tangerang Selatan', 'Banten');
        $cilandak = $this->location('Cilandak', 'Kota Administrasi Jakarta Selatan', 'DKI Jakarta');

        $this->listing(['title' => 'Rumah Pondok Aren', 'location_id' => $pondokAren->id]);
        $this->listing(['title' => 'Rumah Cilandak', 'location_id' => $cilandak->id]);

        $this->get(route('public.listings.index', ['provinsi' => 'Banten']))
            ->assertOk()
            ->assertSee('Rumah Pondok Aren')
            ->assertDontSee('Rumah Cilandak');

        $this->get(route('public.listings.index', [
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Kota Administrasi Jakarta Selatan',
        ]))
            ->assertOk()
            ->assertSee('Rumah Cilandak')
            ->assertDontSee('Rumah Pondok Aren');

        $this->get(route('public.listings.index', [
            'provinsi' => 'Banten',
            'kota' => 'Kota Tangerang Selatan',
            'kecamatan' => 'Pondok Aren',
        ]))
            ->assertOk()
            ->assertSee('Rumah Pondok Aren')
            ->assertDontSee('Rumah Cilandak');
    }

    public function test_a_city_outside_its_province_is_ignored(): void
    {
        $pondokAren = $this->location('Pondok Aren', 'Kota Tangerang Selatan', 'Banten');
        $this->listing(['title' => 'Rumah Pondok Aren', 'location_id' => $pondokAren->id]);

        // Hand-edited URL: the city belongs to another province, so only the
        // province survives and the listing stays visible.
        $this->get(route('public.listings.index', [
            'provinsi' => 'Banten',
            'kota' => 'Kota Administrasi Jakarta Selatan',
        ]))
            ->assertOk()
            ->assertSee('Rumah Pondok Aren')
            ->assertDontSee('Hapus filter Kota Administrasi Jakarta Selatan');
    }

    public function test_region_options_skip_regions_without_published_listings(): void
    {
        $pondokAren = $this->location('Pondok Aren', 'Kota Tangerang Selatan', 'Banten');
        $cilandak = $this->location('Cilandak', 'Kota Administrasi Jakarta Selatan', 'DKI Jakarta');

        $this->listing(['location_id' => $pondokAren->id]);
        $this->listing(['location_id' => $pondokAren->id]);
        $this->listing(['title' => 'Draf', 'location_id' => $cilandak->id, 'is_published' => false]);

        // The pickers are rendered from the JSON the page hands to Alpine.
        $this->get('/properti')
            ->assertOk()
            ->assertSee('\u0022name\u0022:\u0022Banten\u0022,\u0022count\u0022:2', false)
            ->assertSee('\u0022name\u0022:\u0022Pondok Aren\u0022,\u0022count\u0022:2', false)
            ->assertDontSee('Cilandak');
    }

    public function test_type_status_and_price_filters_narrow_the_list(): void
    {
        $this->listing(['title' => 'Rumah Murah', 'property_type' => 'rumah', 'status' => 'dijual', 'price' => 400_000_000]);
        $this->listing(['title' => 'Apartemen Mahal', 'property_type' => 'apartemen', 'status' => 'disewa', 'price' => 1_500_000_000]);

        $this->get('/properti?tipe=rumah')
            ->assertSee('Rumah Murah')
            ->assertDontSee('Apartemen Mahal');

        $this->get('/properti?status=disewa')
            ->assertSee('Apartemen Mahal')
            ->assertDontSee('Rumah Murah');

        $this->get('/properti?harga=under-500')
            ->assertSee('Rumah Murah')
            ->assertDontSee('Apartemen Mahal');

        $this->get('/properti?harga=1000-2000')
            ->assertSee('Apartemen Mahal')
            ->assertDontSee('Rumah Murah');
    }

    public function test_price_buckets_bound_each_range(): void
    {
        $this->listing(['title' => 'Tepat Lima Ratus', 'price' => 500_000_000]);
        $this->listing(['title' => 'Tepat Satu Miliar', 'price' => 1_000_000_000]);

        $this->get('/properti?harga=under-500')
            ->assertSee('Belum ada properti yang cocok');

        $this->get('/properti?harga=500-1000')
            ->assertSee('Tepat Lima Ratus')
            ->assertDontSee('Tepat Satu Miliar');
    }

    public function test_spec_and_offer_filters_narrow_the_list(): void
    {
        $developer = Developer::create(['name' => 'Bintaro Development', 'slug' => 'bintaro-development']);

        $this->listing([
            'title' => 'Rumah Besar',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'carport' => 2,
            'land_area' => 200,
            'building_area' => 180,
            'floors' => 2,
            'listing_type' => 'primary',
            'developer_id' => $developer->id,
        ]);
        $this->listing([
            'title' => 'Rumah Kecil',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'land_area' => 80,
            'building_area' => 60,
            'floors' => 1,
            'listing_type' => 'secondary',
        ]);

        foreach (['kamar=3', 'mandi=2', 'carport=2', 'lt=100', 'lb=100', 'lantai=2', 'penawaran=primary', 'developer='.$developer->id] as $query) {
            $this->get('/properti?'.$query)
                ->assertOk()
                ->assertSee('Rumah Besar')
                ->assertDontSee('Rumah Kecil');
        }
    }

    public function test_new_filter_chips_explain_the_value(): void
    {
        $this->listing(['title' => 'Rumah Uji', 'bedrooms' => 3, 'land_area' => 120]);

        $this->get('/properti?kamar=2&lt=100&lantai=2&carport=1')
            ->assertOk()
            ->assertSee('2+ kamar tidur')
            ->assertSee('1+ carport')
            ->assertSee('LT ≥ 100 m²')
            ->assertSee('2+ lantai');
    }

    public function test_each_card_shows_its_sale_or_rent_chip(): void
    {
        $this->listing(['title' => 'Rumah Jual', 'status' => 'dijual']);
        $this->listing(['title' => 'Rumah Sewa', 'status' => 'disewa']);

        $this->get('/properti')
            ->assertOk()
            ->assertSee('>Dijual</span>', false)
            ->assertSee('>Disewakan</span>', false);
    }

    public function test_the_baru_chip_lasts_thirty_days(): void
    {
        $old = $this->listing(['title' => 'Rumah Lama']);
        $old->forceFill(['created_at' => now()->subDays(31)])->save();

        $this->listing(['title' => 'Rumah Baru']);

        $response = $this->get('/properti')->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), '>Baru</span>'));
    }

    public function test_filters_combine_and_sort_is_applied(): void
    {
        $this->listing(['title' => 'Rumah Paling Mahal', 'price' => 2_000_000_000]);
        $this->listing(['title' => 'Rumah Paling Murah', 'price' => 500_000_000]);
        $this->listing(['title' => 'Apartemen Lain', 'property_type' => 'apartemen', 'price' => 100_000_000]);

        $response = $this->get('/properti?tipe=rumah&urut=termurah')->assertOk();

        $response->assertSeeInOrder(['Rumah Paling Murah', 'Rumah Paling Mahal']);
        $response->assertDontSee('Apartemen Lain');
    }

    public function test_per_page_can_be_chosen_and_falls_back_when_invalid(): void
    {
        foreach (range(1, 8) as $index) {
            $this->listing(['title' => 'Rumah '.$index]);
        }

        $this->get('/properti?per=12')
            ->assertOk()
            ->assertSee('Menampilkan 1–8 dari 8');

        $this->get('/properti?per=999')
            ->assertOk()
            ->assertSee('Menampilkan 1–6 dari 8');
    }

    public function test_applying_filters_keeps_the_chosen_page_size(): void
    {
        $this->get('/properti?per=12')
            ->assertOk()
            ->assertSee('<input type="hidden" name="per" value="12">', false);
    }

    public function test_chip_link_removes_only_its_own_filter(): void
    {
        $this->listing(['location_id' => $this->location('Pondok Aren', 'Kota Tangerang Selatan', 'Banten')->id]);

        // Removing the province drops the levels under it as well.
        $expected = route('public.listings.index', [
            'tipe' => 'rumah',
            'harga' => '1000-2000',
        ]);

        $this->get(route('public.listings.index', [
            'provinsi' => 'Banten',
            'kota' => 'Kota Tangerang Selatan',
            'tipe' => 'rumah',
            'harga' => '1000-2000',
        ]))
            ->assertOk()
            ->assertSee($expected)
            ->assertSee('Hapus filter Banten')
            ->assertSee('Hapus filter Kota Tangerang Selatan');
    }

    public function test_pagination_keeps_the_active_filters(): void
    {
        $location = $this->location();

        foreach (range(1, 7) as $index) {
            $this->listing(['title' => 'Rumah Banten '.$index, 'location_id' => $location->id]);
        }

        $response = $this->get(route('public.listings.index', ['provinsi' => 'Banten']))->assertOk();

        $response->assertSee('Menampilkan 1–6 dari 7');
        $response->assertSee(route('public.listings.index', ['provinsi' => 'Banten', 'page' => 2]));
    }

    public function test_empty_state_offers_a_reset(): void
    {
        $this->get('/properti')
            ->assertOk()
            ->assertSee('Belum ada properti yang cocok')
            ->assertSee('Hapus semua filter');
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
