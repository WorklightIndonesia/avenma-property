<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_developer_list_page_is_gone(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/developers')
            ->assertNotFound();
    }

    public function test_guests_are_redirected_from_the_edit_screen(): void
    {
        $developer = Developer::create(['name' => 'Ciputra', 'slug' => 'ciputra']);

        $this->get(route('admin.developers.edit', $developer))->assertRedirect('/login');
    }

    public function test_agen_cannot_open_the_edit_screen(): void
    {
        $developer = Developer::create(['name' => 'Ciputra', 'slug' => 'ciputra']);

        $this->actingAs($this->agen())
            ->get(route('admin.developers.edit', $developer))
            ->assertForbidden();
    }

    public function test_the_edit_form_carries_the_contact_fields(): void
    {
        $developer = Developer::create([
            'name' => 'Ciputra',
            'slug' => 'ciputra',
            'whatsapp' => '081234567890',
            'email' => 'halo@ciputra.test',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.developers.edit', $developer))
            ->assertOk()
            ->assertSee('name="whatsapp"', false)
            ->assertSee('name="email"', false)
            ->assertSee('081234567890')
            ->assertSee('halo@ciputra.test');
    }

    public function test_agen_cannot_update_a_developer(): void
    {
        $developer = Developer::create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($this->agen())
            ->put(route('admin.developers.update', $developer), ['name' => 'Baru'])
            ->assertForbidden();

        $this->assertSame('Lama', $developer->refresh()->name);
    }

    public function test_admin_can_update_a_developer(): void
    {
        $developer = Developer::create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($this->admin())
            ->put(route('admin.developers.update', $developer), [
                'name' => 'Baru',
                'description' => 'Deskripsi baru',
                'whatsapp' => '081298765432',
                'email' => 'halo@baru.test',
            ])
            ->assertRedirect(route('admin.listing-settings.index'));

        $developer->refresh();

        $this->assertSame('Baru', $developer->name);
        $this->assertSame('baru', $developer->slug);
        $this->assertSame('Deskripsi baru', $developer->description);
        $this->assertSame('081298765432', $developer->whatsapp);
        $this->assertSame('halo@baru.test', $developer->email);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agen(): User
    {
        return User::factory()->create(['role' => User::ROLE_AGEN]);
    }
}
