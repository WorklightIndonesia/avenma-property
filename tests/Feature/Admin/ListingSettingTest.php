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
                ->assertSee('Rumah Siap Huni');
        }
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
            ->assertSee("delete-setting-option-{$option->id}", false);

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
        $agen = User::factory()->create(['role' => User::ROLE_AGEN]);

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

    public function test_an_agen_cannot_delete_a_developer_here(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_AGEN]))
            ->delete("/admin/pengaturan-listing/developer/{$developer->id}")
            ->assertForbidden();

        $this->assertModelExists($developer);
    }

    public function test_an_admin_gets_an_edit_link_next_to_each_developer(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs($this->admin())
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee(route('admin.developers.edit', $developer));
    }

    public function test_an_agen_gets_no_developer_edit_link(): void
    {
        $developer = Developer::create(['name' => 'Lepas', 'slug' => 'lepas']);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_AGEN]))
            ->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertDontSee(route('admin.developers.edit', $developer));
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
