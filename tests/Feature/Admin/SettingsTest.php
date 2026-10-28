<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee('087736786969');
    }

    public function test_admin_saves_a_local_number(): void
    {
        SiteSetting::create(['whatsapp' => '081111111111']);

        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '087736786969'])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('087736786969', SiteSetting::whatsapp());
        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_international_input_is_stored_in_local_format(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', ['whatsapp' => '+62 877-3678-6969'])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('087736786969', SiteSetting::whatsapp());
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

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996']);
    }
}
