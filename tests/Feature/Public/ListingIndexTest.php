<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
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

    public function test_location_filter_narrows_the_list(): void
    {
        $this->listing(['title' => 'Rumah Bintaro', 'location_id' => $this->location('Bintaro', 'bintaro')->id]);
        $this->listing(['title' => 'Rumah Cipete', 'location_id' => $this->location('Cipete', 'cipete')->id]);

        $this->get('/properti?lokasi=bintaro')
            ->assertOk()
            ->assertSee('Rumah Bintaro')
            ->assertDontSee('Rumah Cipete');
    }

    public function test_type_status_and_price_filters_narrow_the_list(): void
    {
        $this->listing(['title' => 'Rumah Murah', 'property_type' => 'rumah', 'status' => 'dijual', 'price' => 400_000_000]);
        $this->listing(['title' => 'Apartemen Mahal', 'property_type' => 'apartemen', 'status' => 'baru', 'price' => 1_500_000_000]);

        $this->get('/properti?tipe=rumah')
            ->assertSee('Rumah Murah')
            ->assertDontSee('Apartemen Mahal');

        $this->get('/properti?status=baru')
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

    public function test_filters_combine_and_sort_is_applied(): void
    {
        $this->listing(['title' => 'Rumah Paling Mahal', 'price' => 2_000_000_000]);
        $this->listing(['title' => 'Rumah Paling Murah', 'price' => 500_000_000]);
        $this->listing(['title' => 'Apartemen Lain', 'property_type' => 'apartemen', 'price' => 100_000_000]);

        $response = $this->get('/properti?tipe=rumah&urut=termurah')->assertOk();

        $response->assertSeeInOrder(['Rumah Paling Murah', 'Rumah Paling Mahal']);
        $response->assertDontSee('Apartemen Lain');
    }

    public function test_chip_link_removes_only_its_own_filter(): void
    {
        $listing = $this->listing(['location_id' => $this->location('Bintaro', 'bintaro')->id]);

        $expected = route('public.listings.index', [
            'tipe' => $listing->property_type,
            'harga' => '1000-2000',
        ]);

        $this->get('/properti?lokasi=bintaro&tipe=rumah&harga=1000-2000')
            ->assertOk()
            ->assertSee($expected)
            ->assertSee('Hapus filter Bintaro');
    }

    public function test_pagination_keeps_the_active_filters(): void
    {
        $location = $this->location('Bintaro', 'bintaro');

        foreach (range(1, 7) as $index) {
            $this->listing(['title' => 'Rumah Bintaro '.$index, 'location_id' => $location->id]);
        }

        $response = $this->get('/properti?lokasi=bintaro')->assertOk();

        $response->assertSee('Menampilkan 1–6 dari 7');
        $response->assertSee(route('public.listings.index', ['lokasi' => 'bintaro', 'page' => 2]));
    }

    public function test_empty_state_offers_a_reset(): void
    {
        $this->get('/properti')
            ->assertOk()
            ->assertSee('Belum ada properti yang cocok')
            ->assertSee('Hapus semua filter');
    }

    private function location(string $name, string $slug): Location
    {
        return Location::firstOrCreate(['slug' => $slug], ['name' => $name]);
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
        ], $attributes));
    }
}
