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

    public function test_agen_cannot_reach_the_account_screens(): void
    {
        $agen = $this->agen();
        $other = $this->agen();

        $this->actingAs($agen)->get('/admin/users')->assertForbidden();
        $this->actingAs($agen)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($agen)->get(route('admin.users.edit', $other))->assertForbidden();
    }

    public function test_admin_sees_the_account_screens(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee($admin->name);
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk()->assertSee($admin->email);
    }

    public function test_admin_creates_an_agen(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload())
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('Budi', $user->name);
        $this->assertSame('089630999996', $user->phone);
        $this->assertSame(User::ROLE_AGEN, $user->role);
        $this->assertTrue(Hash::check('Rahasia1!', $user->password));
    }

    public function test_phone_is_required_for_agen_but_not_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload(['phone' => '']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('users', 1); // the acting admin only

        $this->actingAs($this->admin())
            ->post('/admin/users', $this->payload([
                'email' => 'admin2@example.com',
                'phone' => '',
                'role' => User::ROLE_ADMIN,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com', 'phone' => null]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->agen(['email' => 'budi@example.com']);

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
        $user = $this->agen(['password' => 'Rahasia1!']);
        $hash = $user->password;

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => 'Nama Baru',
                'email' => $user->email,
                'phone' => '081234567890',
                'role' => User::ROLE_AGEN,
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
        $user = $this->agen(['password' => 'Rahasia1!']);

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => User::ROLE_AGEN,
                'password' => 'Rahasia2!',
                'password_confirmation' => 'Rahasia2!',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('Rahasia2!', $user->refresh()->password));
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => '081234567890',
                'role' => User::ROLE_AGEN,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(User::ROLE_ADMIN, $admin->refresh()->role);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_deletes_another_account(): void
    {
        $user = $this->agen();

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function agen(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_AGEN,
            'phone' => '089630999996',
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '089630999996',
            'role' => User::ROLE_AGEN,
            'password' => 'Rahasia1!',
            'password_confirmation' => 'Rahasia1!',
        ], $attributes);
    }
}
