<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_an_address_is_limited_across_many_accounts(): void
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->post('/login', [
                'email' => "penyusup{$attempt}@example.com",
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'penyusup21@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertStringContainsString(
            'Terlalu banyak percobaan',
            (string) $response->getSession()->get('errors')?->first('email'),
        );
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_authenticated_users_visiting_login_land_on_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect('/admin/dashboard');
    }
}
