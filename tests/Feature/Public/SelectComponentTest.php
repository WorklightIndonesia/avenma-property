<?php

namespace Tests\Feature\Public;

use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every dropdown is the same component now, so a regression here would hit the
 * public filter and both admin forms at once.
 */
class SelectComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_filter_uses_the_shared_dropdown(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->get('/properti')
            ->assertOk()
            ->assertSee('aria-haspopup="listbox"', false)
            ->assertSee('data-name="tipe"', false)
            ->assertSee('data-name="developer"', false)
            // The plain <select> is what submits when JavaScript is off.
            ->assertSee('<select', false)
            ->assertSee('name="tipe"', false);
    }

    public function test_the_current_filter_value_is_kept(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->get('/properti?tipe=rumah&status=dijual')
            ->assertOk()
            ->assertSee('data-name="tipe" value="rumah"', false)
            ->assertSee('data-name="status" value="dijual"', false);
    }

    public function test_the_admin_listing_form_uses_the_shared_dropdown(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('data-name="property_type"', false)
            ->assertSee('data-name="listing_type"', false)
            ->assertSee('data-name="status"', false)
            ->assertSee('data-name="developer_id"', false);
    }
}
