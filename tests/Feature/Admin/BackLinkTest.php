<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\BlogPost;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\PageContent;
use App\Models\Service;
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
        $admin = User::factory()->create();
        $agen = User::factory()->create(['phone' => '089630999996']);
        $agent = Agent::create([
            'name' => 'Budi',
            'phone' => '089630999996',
            'email' => 'budi@example.com',
        ]);

        $service = Service::create([
            'title' => 'Konsultasi Properti',
            'description' => 'Memberikan pertimbangan harga pasar.',
        ]);

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
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        $developer = Developer::create(['name' => 'Developer Uji', 'slug' => 'developer-uji']);
        $page = PageContent::create(['key' => 'kontak', 'title' => 'Kontak']);

        $screens = [
            ['/admin/blog', 'admin.dashboard'],
            ['/admin/blog/create', 'admin.blog.index'],
            [route('admin.blog.edit', $post), 'admin.blog.index'],
            ['/admin/listings', 'admin.dashboard'],
            ['/admin/listings/create', 'admin.listings.index'],
            [route('admin.listings.edit', $listing), 'admin.listings.index'],
            [route('admin.developers.edit', $developer), 'admin.listing-settings.index'],
            ['/admin/agents', 'admin.dashboard'],
            ['/admin/agents/create', 'admin.agents.index'],
            [route('admin.agents.edit', $agent), 'admin.agents.index'],
            ['/admin/layanan', 'admin.dashboard'],
            ['/admin/layanan/create', 'admin.services.index'],
            [route('admin.services.edit', $service), 'admin.services.index'],
            ['/admin/pages', 'admin.dashboard'],
            [route('admin.pages.edit', $page), 'admin.pages.index'],
            ['/admin/settings', 'admin.dashboard'],
            ['/admin/template-whatsapp', 'admin.dashboard'],
        ];

        foreach ($screens as [$url, $parent]) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee('href="'.route($parent).'"', false);
        }
    }
}
