<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every dashboard screen except the dashboard itself opens with a link to its
 * parent, so a page saved without one fails here instead of in review.
 */
class BackLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_screen_links_back_to_its_parent(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $agen = User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996']);

        $post = BlogPost::create([
            'created_by' => $agen->id,
            'title' => 'Artikel Uji',
            'slug' => 'artikel-uji',
            'content' => '<p>Isi.</p>',
        ]);

        $listing = Listing::create([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        $developer = Developer::create(['name' => 'Developer Uji', 'slug' => 'developer-uji']);
        $page = PageContent::create(['key' => 'tentang-kami', 'title' => 'Tentang Kami']);

        $screens = [
            ['/admin/blog', 'admin.dashboard'],
            ['/admin/blog/create', 'admin.blog.index'],
            [route('admin.blog.edit', $post), 'admin.blog.index'],
            ['/admin/listings', 'admin.dashboard'],
            ['/admin/listings/create', 'admin.listings.index'],
            [route('admin.listings.edit', $listing), 'admin.listings.index'],
            ['/admin/developers', 'admin.dashboard'],
            ['/admin/developers/create', 'admin.developers.index'],
            [route('admin.developers.edit', $developer), 'admin.developers.index'],
            ['/admin/users', 'admin.dashboard'],
            ['/admin/users/create', 'admin.users.index'],
            [route('admin.users.edit', $agen), 'admin.users.index'],
            ['/admin/pages', 'admin.dashboard'],
            [route('admin.pages.edit', $page), 'admin.pages.index'],
            ['/admin/settings', 'admin.dashboard'],
        ];

        foreach ($screens as [$url, $parent]) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee('href="'.route($parent).'"', false);
        }
    }
}
