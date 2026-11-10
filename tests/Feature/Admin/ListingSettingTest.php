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

    public function test_an_admin_opens_the_settings_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee('Pengaturan Listing')
            ->assertSee('Tipe properti')
            ->assertSee('Status')
            ->assertSee('Air')
            ->assertSee('Daya listrik')
            ->assertSee('Tambah developer')
            ->assertSee('Rumah Siap Huni');
    }

    public function test_each_section_has_a_search_box(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee('aria-label="Cari developer"', false)
            ->assertSee('aria-label="Cari tipe properti"', false)
            ->assertSee('aria-label="Cari jenis penawaran"', false)
            ->assertSee('aria-label="Cari status"', false);
    }

    public function test_a_builtin_option_has_a_delete_control_and_can_be_removed(): void
    {
        $option = ListingOption::where('group', 'status')->where('slug', 'disewa')->firstOrFail();

        $this->actingAs($this->admin())
            ->get('/admin/pengaturan-listing')
            ->assertSee("delete-setting-option-{$option->id}", false)
            ->assertSee("edit-setting-option-{$option->id}", false);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertRedirect();

        $this->assertArrayNotHasKey('disewa', ListingOption::options('status'));
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

    public function test_a_new_water_option_reaches_the_listing_form_and_catalog(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/opsi', ['group' => 'water_source', 'label' => 'Air Hujan'])
            ->assertRedirect();

        $this->assertDatabaseHas('listing_options', ['group' => 'water_source', 'slug' => 'air-hujan']);

        $this->actingAs($this->admin())
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('Air Hujan');

        $this->get('/properti')
            ->assertOk()
            ->assertSee('Air Hujan');
    }

    public function test_a_capacity_option_uses_its_digits_and_drives_the_filter(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/opsi', ['group' => 'electricity_power', 'label' => '7.700 VA'])
            ->assertRedirect();

        $this->assertDatabaseHas('listing_options', [
            'group' => 'electricity_power',
            'slug' => '7700',
            'label' => '7.700 VA',
        ]);

        $this->listing(['title' => 'Rumah Daya Besar', 'electricity_power' => 7700]);

        $this->get('/properti?daya=7700')
            ->assertOk()
            ->assertSee('Rumah Daya Besar')
            ->assertSee('Daya listrik ≥ 7.700 VA');
    }

    public function test_a_water_option_in_use_cannot_be_deleted(): void
    {
        $option = ListingOption::where('group', 'water_source')->where('slug', 'pdam')->firstOrFail();

        $this->listing(['water_source' => 'pdam']);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertSessionHasErrors('option');

        $this->assertModelExists($option);
    }

    public function test_a_legalitas_option_in_use_cannot_be_deleted(): void
    {
        $option = ListingOption::where('group', 'certificate')->where('slug', 'shm')->firstOrFail();

        $this->listing(['certificate' => 'shm']);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertSessionHasErrors('option');

        $this->assertModelExists($option);
    }

    public function test_a_capacity_option_can_be_deleted_even_when_a_listing_matches(): void
    {
        $option = ListingOption::where('group', 'electricity_power')->where('slug', '2200')->firstOrFail();

        $this->listing(['electricity_power' => 2200]);

        $this->actingAs($this->admin())
            ->delete("/admin/pengaturan-listing/opsi/{$option->id}")
            ->assertRedirect();

        $this->assertModelMissing($option);
    }

    public function test_an_admin_renames_an_option_without_breaking_listings(): void
    {
        $option = ListingOption::where('group', 'property_type')->where('slug', 'rumah_siap_huni')->firstOrFail();

        $this->actingAs($this->admin())
            ->put("/admin/pengaturan-listing/opsi/{$option->id}", ['label' => 'Rumah Siap Tinggal'])
            ->assertRedirect();

        $option->refresh();

        $this->assertSame('Rumah Siap Tinggal', $option->label);
        $this->assertSame('rumah_siap_huni', $option->slug);
    }

    public function test_renaming_an_option_to_an_existing_label_is_refused(): void
    {
        $option = ListingOption::where('group', 'status')->where('slug', 'disewa')->firstOrFail();

        $this->actingAs($this->admin())
            ->put("/admin/pengaturan-listing/opsi/{$option->id}", ['label' => 'Dijual'])
            ->assertSessionHasErrors('option_label');

        $this->assertSame('Disewakan', $option->fresh()->label);
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

    public function test_the_add_form_asks_for_contact_details(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee('name="whatsapp"', false)
            ->assertSee('name="email"', false);
    }

    public function test_an_agen_adds_a_developer_here(): void
    {
        $agen = User::factory()->create();

        $this->actingAs($agen)
            ->post('/admin/pengaturan-listing/developer', [
                'name' => 'Graha Nusantara',
                'whatsapp' => '081234567890',
                'email' => 'halo@graha.test',
            ])
            ->assertRedirect();

        $developer = Developer::where('name', 'Graha Nusantara')->firstOrFail();
        $this->assertSame('graha-nusantara', $developer->slug);
        $this->assertSame('081234567890', $developer->whatsapp);
        $this->assertSame('halo@graha.test', $developer->email);
    }

    public function test_whatsapp_must_be_an_indonesian_mobile_number(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/developer', $this->developerPayload(['whatsapp' => '12345']))
            ->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('developers', 0);
    }

    public function test_name_whatsapp_and_email_are_required(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/developer', [])
            ->assertSessionHasErrors(['name', 'whatsapp', 'email']);

        $this->assertDatabaseCount('developers', 0);
    }

    public function test_duplicate_names_get_a_distinct_slug(): void
    {
        $this->actingAs($this->admin())->post('/admin/pengaturan-listing/developer', $this->developerPayload(['name' => 'Sama']));
        $this->actingAs($this->admin())->post('/admin/pengaturan-listing/developer', $this->developerPayload(['name' => 'Sama']));

        $this->assertSame(['sama', 'sama-2'], Developer::orderBy('id')->pluck('slug')->all());
    }

    public function test_an_admin_gets_an_edit_link_next_to_each_developer(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs($this->admin())
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee(route('admin.developers.edit', $developer));
    }

    public function test_an_admin_deletes_an_unused_developer_here(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs($this->admin())
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function developerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ciputra',
            'whatsapp' => '081234567890',
            'email' => 'halo@ciputra.test',
        ], $overrides);
    }

    private function admin(): User
    {
        return User::factory()->create();
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
