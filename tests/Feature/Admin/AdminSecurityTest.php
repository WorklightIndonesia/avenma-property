<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sensitive_screen_asks_for_the_password_again(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);
        $this->withSession(['auth.password_confirmed_at' => null]);

        $this->get('/admin/settings')->assertRedirect(route('password.confirm'));
    }

    public function test_confirming_the_password_opens_the_screen(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);
        $this->withSession(['auth.password_confirmed_at' => null]);

        $this->post('/confirm-password', ['password' => 'password'])->assertRedirect();

        $this->get('/admin/settings')->assertOk();
    }

    public function test_the_sixty_first_admin_request_is_throttled(): void
    {
        Cache::flush();

        $admin = User::factory()->create();

        $this->actingAs($admin);

        for ($request = 1; $request <= 60; $request++) {
            $this->get('/admin/dashboard')->assertOk();
        }

        $this->get('/admin/dashboard')->assertStatus(429);
    }
}
