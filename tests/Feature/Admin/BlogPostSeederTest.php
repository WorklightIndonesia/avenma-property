<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\BlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BlogPostSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_articles_are_authored_by_the_admin_and_dated_the_day_it_runs(): void
    {
        config(['seeding.admin_password' => 'Rahasia1!']);
        $this->travelTo(Carbon::parse('2026-11-01 13:00'));

        $this->seed(AdminSeeder::class);
        $this->seed(BlogPostSeeder::class);

        $author = User::where('email', AdminSeeder::EMAIL)->firstOrFail();

        $this->assertSame(10, BlogPost::count());
        $this->assertSame(0, BlogPost::where('created_by', '!=', $author->id)->count());
        $this->assertSame(0, BlogPost::whereDate('published_at', '!=', '2026-11-01')->count());

        // Re-running updates in place instead of leaving a second copy.
        $this->seed(BlogPostSeeder::class);

        $this->assertSame(10, BlogPost::count());
    }
}
