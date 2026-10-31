<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A destructive action opens the shared dialog and names the object it will
 * delete. Nothing falls back to the browser's own confirm prompt.
 */
class DeletePromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_listing_table_opens_a_dialog_naming_the_listing(): void
    {
        $listing = $this->listing(['title' => 'Rumah Semeru']);

        $this->actingAs($this->admin())->get('/admin/listings')
            ->assertOk()
            ->assertSee("open-modal', 'delete-listing-{$listing->id}", false)
            ->assertSee('Hapus listing?')
            ->assertSee('Rumah Semeru')
            ->assertDontSee('return confirm', false);
    }

    public function test_the_developer_table_opens_a_dialog_naming_the_developer(): void
    {
        $developer = Developer::create(['name' => 'Beverly Group', 'slug' => 'beverly-group']);

        $this->actingAs($this->admin())->get('/admin/developers')
            ->assertOk()
            ->assertSee("open-modal', 'delete-developer-{$developer->id}", false)
            ->assertSee('Hapus developer?')
            ->assertSee('Beverly Group')
            ->assertDontSee('return confirm', false);
    }

    public function test_the_account_table_opens_a_dialog_naming_the_account(): void
    {
        $agen = User::factory()->create([
            'role' => User::ROLE_AGEN,
            'phone' => '089630999996',
            'name' => 'Budi',
            'email' => 'agenbudi@gmail.com',
        ]);

        $this->actingAs($this->admin())->get('/admin/users')
            ->assertOk()
            ->assertSee("open-modal', 'delete-user-{$agen->id}", false)
            ->assertSee('Hapus akun?')
            ->assertSee('agenbudi@gmail.com')
            ->assertDontSee('return confirm', false);
    }

    public function test_the_photo_row_opens_a_dialog_naming_the_photo(): void
    {
        $listing = $this->listing();
        $image = $listing->images()->create([
            'path' => 'images/listings/1/foto.jpg',
            'label' => 'Tampak depan',
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin())->get(route('admin.listings.edit', $listing))
            ->assertOk()
            ->assertSee("open-modal', 'delete-photo-{$image->id}", false)
            ->assertSee('Hapus foto?')
            ->assertSee('Tampak depan')
            ->assertDontSee('return confirm', false);
    }

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ], $attributes));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }
}
