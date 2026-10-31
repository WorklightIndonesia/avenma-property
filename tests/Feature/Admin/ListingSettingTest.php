<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\ListingOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_agen_open_the_settings_page(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_AGEN] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/admin/pengaturan-listing')
                ->assertOk()
                ->assertSee('Pengaturan Listing')
                ->assertSee('Tipe properti')
                ->assertSee('Status')
                ->assertSee('Tambah developer')
                ->assertSee('Bawaan');
        }
    }

    public function test_an_option_added_here_reaches_the_listing_form(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/opsi', ['group' => 'property_type', 'label' => 'Tanah'])
            ->assertRedirect();

        $this->assertDatabaseHas('listing_options', ['group' => 'property_type', 'slug' => 'tanah']);

        $this->actingAs($this->admin())
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('Tanah');

        $this->assertSame('Tanah', ListingOption::options('property_type')['tanah']);
    }

    public function test_a_duplicate_option_is_refused(): void
    {
        ListingOption::create(['group' => 'status', 'label' => 'Lelang', 'slug' => 'lelang']);

        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/opsi', ['group' => 'status', 'label' => 'Lelang'])
            ->assertSessionHasErrors('label');

        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/opsi', ['group' => 'status', 'label' => 'Dijual'])
            ->assertSessionHasErrors('label');
    }

    public function test_an_option_in_use_cannot_be_deleted(): void
    {
        $option = ListingOption::create(['group' => 'status', 'label' => 'Lelang', 'slug' => 'lelang']);

        $this->listing(['status' => 'lelang']);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertSessionHasErrors('option');

        $this->assertDatabaseHas('listing_options', ['id' => $option->id]);
    }

    public function test_an_unused_option_is_deleted(): void
    {
        $option = ListingOption::create(['group' => 'listing_type', 'label' => 'Tukar guling', 'slug' => 'tukar-guling']);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('listing_options', ['id' => $option->id]);
    }

    public function test_an_agen_adds_and_removes_a_developer_here(): void
    {
        $agen = User::factory()->create(['role' => User::ROLE_AGEN]);

        $this->actingAs($agen)
            ->post('/admin/pengaturan-listing/developer', ['name' => 'Graha Nusantara'])
            ->assertRedirect();

        $developer = Developer::where('name', 'Graha Nusantara')->firstOrFail();
        $this->assertSame('graha-nusantara', $developer->slug);

        $this->actingAs($agen)
            ->delete("/admin/pengaturan-listing/developer/{$developer->id}")
            ->assertRedirect();

        $this->assertModelMissing($developer);
    }

    public function test_a_developer_in_use_cannot_be_deleted(): void
    {
        $developer = Developer::create(['name' => 'Bintaro Development', 'slug' => 'bintaro-development']);

        $this->listing(['developer_id' => $developer->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/developer/{$developer->id}")
            ->assertSessionHasErrors('option');

        $this->assertModelExists($developer);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Lelang',
            'slug' => 'rumah-lelang',
            'description' => 'Deskripsi.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 500_000_000,
        ], $attributes));
    }
}
