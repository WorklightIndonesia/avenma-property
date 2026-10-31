<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A short Maps link is resolved with an HTTP call; never reach the network.
        Http::fake();
    }

    public function test_guests_are_redirected_from_the_settings_screen(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_agen_cannot_reach_the_settings_screen(): void
    {
        $this->actingAs($this->agen())->get('/admin/settings')->assertForbidden();
        $this->actingAs($this->agen())->put('/admin/settings', ['whatsapp' => '087736786969'])->assertForbidden();
    }

    public function test_admin_sees_the_current_number(): void
    {
        SiteSetting::create(['whatsapp' => '087736786969']);

        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('087736786969')
            ->assertSee('name="maps_url"', false);
    }

    public function test_admin_saves_a_local_number(): void
    {
        SiteSetting::create(['whatsapp' => '081111111111']);

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969'])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('087736786969', SiteSetting::whatsappNumber());
        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_international_input_is_stored_in_local_format(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '+62 877-3678-6969'])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('087736786969', SiteSetting::whatsappNumber());
    }

    public function test_invalid_numbers_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => 'bukan nomor'])
            ->assertSessionHasErrors('whatsapp');

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '0215551234'])
            ->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_the_row_is_created_when_missing(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969'])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseHas('site_settings', ['whatsapp' => '087736786969']);
    }

    public function test_the_contact_details_are_saved_and_validated(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', [
                'whatsapp' => '087736786969',
                'email' => 'halo@avenma.id',
                'address' => 'Jl. Contoh No. 1, Jakarta Selatan',
                'maps_url' => 'https://maps.app.goo.gl/yvhXuc3mnWkUjua56',
                'instagram' => 'avenma.properti',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $setting = SiteSetting::current();

        $this->assertSame('halo@avenma.id', $setting->email);
        $this->assertSame('Jl. Contoh No. 1, Jakarta Selatan', $setting->address);
        $this->assertSame('https://maps.app.goo.gl/yvhXuc3mnWkUjua56', $setting->maps_url);
        $this->assertSame('avenma.properti', $setting->instagram);
        $this->assertSame('https://instagram.com/avenma.properti', $setting->instagramUrl());
    }

    public function test_a_map_link_must_be_an_https_google_maps_address(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'maps_url' => 'bukan tautan'])
            ->assertSessionHasErrors('maps_url');

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'maps_url' => 'http://maps.google.com/?q=x'])
            ->assertSessionHasErrors('maps_url');

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'maps_url' => 'https://example.com/maps/place/@-6.1,106.9'])
            ->assertSessionHasErrors('maps_url');
    }

    public function test_the_place_coordinates_are_read_from_the_map_link(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', [
                'whatsapp' => '087736786969',
                'maps_url' => 'https://www.google.com/maps/place/Avenma+Properti/@-6.1638,106.9073,17z',
            ])
            ->assertSessionHasNoErrors();

        $setting = SiteSetting::current();

        $this->assertSame(-6.1638, $setting->maps_lat);
        $this->assertSame(106.9073, $setting->maps_lng);
    }

    public function test_invalid_contacts_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'email' => 'bukan-email'])
            ->assertSessionHasErrors('email');

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'instagram' => 'avenma properti'])
            ->assertSessionHasErrors('instagram');
    }

    public function test_a_full_instagram_address_is_reduced_to_the_handle(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'instagram' => 'https://instagram.com/@avenma/'])
            ->assertSessionHasNoErrors();

        $this->assertSame('avenma', SiteSetting::current()->instagram);
    }

    public function test_empty_contact_fields_stay_empty(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969', 'email' => '', 'address' => '', 'maps_url' => '', 'instagram' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull(SiteSetting::current()->email);
        $this->assertNull(SiteSetting::current()->address);
        $this->assertNull(SiteSetting::current()->maps_url);
        $this->assertNull(SiteSetting::current()->instagramUrl());
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996']);
    }
}
