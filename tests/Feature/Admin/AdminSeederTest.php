<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_password_comes_from_config(): void
    {
        // Deliberately different from the value in .env: a seeder reading env()
        // directly would store that one instead.
        config(['seeding.admin_password' => 'Rahasia1!']);

        $this->seed(AdminSeeder::class);

        $user = User::where('email', AdminSeeder::EMAIL)->firstOrFail();

        $this->assertSame('Avenma Properti', $user->name);
        $this->assertSame('081299900387', $user->phone);
        $this->assertTrue(Hash::check('Rahasia1!', $user->password));
    }

    public function test_a_missing_password_stops_the_seeder(): void
    {
        config(['seeding.admin_password' => null]);

        $this->expectException(RuntimeException::class);

        $this->seed(AdminSeeder::class);
    }
}
