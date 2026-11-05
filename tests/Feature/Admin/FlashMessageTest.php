<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_success_message_becomes_a_green_toast(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pengaturan-listing/developer', [
                'name' => 'Beverly Group',
                'whatsapp' => '081234567890',
                'email' => 'halo@beverly.test',
            ])
            ->assertSessionHasNoErrors();

        $this->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee('role="status"', false)
            ->assertSee('border-l-success', false)
            ->assertSee('Developer "Beverly Group" ditambahkan.')
            ->assertSee('6000', false);
    }

    public function test_a_blocked_delete_names_the_developer_in_a_red_toast(): void
    {
        $developer = Developer::create(['name' => 'Beverly Group', 'slug' => 'beverly-group']);

        Listing::create([
            'location_id' => $this->location()->id,
            'developer_id' => $developer->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.listing-settings.developers.destroy', $developer))
            ->assertRedirect();

        $this->get('/admin/pengaturan-listing')
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('border-l-danger', false)
            ->assertSee('Developer Beverly Group dipakai 1 listing. Pindahkan listing itu dulu.')
            ->assertSee('10000', false);
    }

    public function test_an_error_toast_stays_longer_than_a_success_toast(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/agents/create')
            ->post('/admin/agents', ['name' => '', 'email' => '', 'phone' => ''])
            ->assertRedirect('/admin/agents/create');

        $page = $this->get('/admin/agents/create')->assertOk()->getContent();

        $this->assertStringContainsString('10000', $page);
        $this->assertStringNotContainsString('6000', $page);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }
}
