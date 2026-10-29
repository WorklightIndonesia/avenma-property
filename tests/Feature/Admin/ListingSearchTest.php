<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_search_matches_part_of_the_title(): void
    {
        $this->listing(['title' => 'Apartemen Sudirman Park']);
        $this->listing(['title' => 'Rumah Bogor Asri']);

        $this->actingAs($this->admin())
            ->get('/admin/listings?properti=sudirman')
            ->assertOk()
            ->assertSee('Apartemen Sudirman Park')
            ->assertDontSee('Rumah Bogor Asri');
    }

    public function test_location_search_matches_the_kecamatan_or_the_city(): void
    {
        $this->listing([
            'title' => 'Rumah Pondok',
            'location_id' => $this->location('Pondok Aren', 'Kota Tangerang Selatan')->id,
        ]);
        $this->listing([
            'title' => 'Rumah Bogor',
            'location_id' => $this->location('Tanah Sareal', 'Kota Bogor')->id,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/listings?lokasi=Pondok')
            ->assertSee('Rumah Pondok')
            ->assertDontSee('Rumah Bogor');

        $this->actingAs($this->admin())
            ->get('/admin/listings?lokasi=Bogor')
            ->assertSee('Rumah Bogor')
            ->assertDontSee('Rumah Pondok');
    }

    public function test_status_filter_keeps_only_the_chosen_status(): void
    {
        $this->listing(['title' => 'Rumah Status Jual', 'status' => 'dijual']);
        $this->listing(['title' => 'Rumah Status Sewa', 'status' => 'disewa']);

        $this->actingAs($this->admin())
            ->get('/admin/listings?status=disewa')
            ->assertSee('Rumah Status Sewa')
            ->assertDontSee('Rumah Status Jual');
    }

    public function test_price_sort_orders_the_table(): void
    {
        $this->listing(['title' => 'Rumah Menengah', 'price' => 1_000_000_000]);
        $this->listing(['title' => 'Rumah Murah', 'price' => 300_000_000]);
        $this->listing(['title' => 'Rumah Mahal', 'price' => 5_000_000_000]);

        $this->actingAs($this->admin())
            ->get('/admin/listings?urut=termurah')
            ->assertSeeInOrder(['Rumah Murah', 'Rumah Menengah', 'Rumah Mahal']);

        $this->actingAs($this->admin())
            ->get('/admin/listings?urut=termahal')
            ->assertSeeInOrder(['Rumah Mahal', 'Rumah Menengah', 'Rumah Murah']);
    }

    public function test_filters_and_sort_combine(): void
    {
        $bogor = $this->location('Tanah Sareal', 'Kota Bogor')->id;

        $this->listing(['title' => 'Rumah Bogor Mahal', 'location_id' => $bogor, 'price' => 4_000_000_000]);
        $this->listing(['title' => 'Rumah Bogor Murah', 'location_id' => $bogor, 'price' => 400_000_000]);
        $this->listing(['title' => 'Rumah Bandung', 'location_id' => $this->location('Sukajadi', 'Kota Bandung')->id, 'price' => 100_000_000]);

        $this->actingAs($this->admin())
            ->get('/admin/listings?lokasi=Bogor&urut=termurah')
            ->assertSeeInOrder(['Rumah Bogor Murah', 'Rumah Bogor Mahal'])
            ->assertDontSee('Rumah Bandung');
    }

    public function test_agen_filters_stay_inside_their_own_listings(): void
    {
        $agen = $this->agen();
        $this->listing(['title' => 'Rumah Milik Budi', 'created_by' => $agen->id]);
        $this->listing(['title' => 'Rumah Agen Lain', 'created_by' => $this->agen()->id]);

        $this->actingAs($agen)
            ->get('/admin/listings?properti=Rumah')
            ->assertSee('Rumah Milik Budi')
            ->assertDontSee('Rumah Agen Lain');
    }

    public function test_filters_survive_pagination_links(): void
    {
        foreach (range(1, 21) as $number) {
            $this->listing(['title' => "Rumah Uji {$number}"]);
        }

        $response = $this->actingAs($this->admin())->get('/admin/listings?properti=Rumah');

        $response->assertOk();
        $this->assertStringContainsString('properti=Rumah', $response->viewData('listings')->url(2));
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
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
