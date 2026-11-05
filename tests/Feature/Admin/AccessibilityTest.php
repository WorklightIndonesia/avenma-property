<?php

namespace Tests\Feature\Admin;

use App\Models\Developer;
use App\Models\PageContent;
use App\Models\User;
use Tests\Feature\AccessibilityTestCase;

class AccessibilityTest extends AccessibilityTestCase
{
    public function test_every_admin_screen_keeps_its_structure(): void
    {
        $this->seedContent();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));

        $page = PageContent::where('key', 'kontak')->firstOrFail();

        $developer = Developer::create([
            'name' => 'Developer Uji',
            'slug' => 'developer-uji',
            'whatsapp' => '081234567890',
            'email' => 'halo@developer.test',
        ]);

        $screens = [
            '/admin/dashboard',
            '/admin/listings',
            '/admin/listings/create',
            '/admin/listings/rumah-uji/edit',
            '/admin/pengaturan-listing',
            route('admin.developers.edit', $developer),
            '/admin/blog',
            '/admin/blog/create',
            '/admin/blog/artikel-uji/edit',
            '/admin/agents',
            '/admin/agents/create',
            '/admin/layanan',
            '/admin/layanan/create',
            '/admin/pages',
            '/admin/pages/'.$page->id.'/edit',
            '/admin/settings',
        ];

        foreach ($screens as $url) {
            $this->assertAccessible($url);
        }
    }

    public function test_the_sidebar_leaves_the_tab_order_when_it_is_closed(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->get('/admin/dashboard')
            ->assertOk()
            // Off-canvas is not the same as hidden: without inert, eight links
            // stay focusable at x = -232 before the menu button.
            ->assertSee('x-bind:inert="!sidebarOpen"', false);
    }
}
