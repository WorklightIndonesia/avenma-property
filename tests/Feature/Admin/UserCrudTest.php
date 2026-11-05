<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_account_screen(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_admin_sees_the_account_screens(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee($admin->name);
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk()->assertSee($admin->email);
    }

    public function test_admin_creates_another_admin(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload())
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('Budi', $user->name);
        $this->assertSame('089630999996', $user->phone);
        $this->assertTrue(Hash::check('Rahasia1!', $user->password));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->admin(['email' => 'budi@example.com']);

        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload())
            ->assertSessionHasErrors('email');
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload(['password' => 'password', 'password_confirmation' => 'password']))
            ->assertSessionHasErrors('password');
    }

    public function test_update_without_a_password_keeps_the_old_one(): void
    {
        $user = $this->admin(['password' => 'Rahasia1!']);
        $hash = $user->password;

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => 'Nama Baru',
                'email' => $user->email,
                'phone' => '081234567890',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame($hash, $user->password);
    }

    public function test_update_can_replace_the_password(): void
    {
        $user = $this->admin(['password' => 'Rahasia1!']);

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => 'Rahasia2!',
                'password_confirmation' => 'Rahasia2!',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('Rahasia2!', $user->refresh()->password));
    }

    public function test_admin_deletes_another_admin(): void
    {
        $admin = $this->admin();
        $other = $this->admin(['email' => 'lain@example.com']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $other))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($other);
    }

    public function test_the_last_admin_cannot_be_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertModelExists($admin);
    }

    public function test_an_admin_can_delete_themselves_when_another_remains(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);
        $this->admin(['email' => 'lain@example.com']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertModelMissing($admin);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function admin(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '089630999996',
            'password' => 'Rahasia1!',
            'password_confirmation' => 'Rahasia1!',
        ], $attributes);
    }
}
