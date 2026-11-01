<?php

namespace Tests\Feature\Admin;

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

        $screens = [
            '/admin/dashboard',
            '/admin/listings',
            '/admin/listings/create',
            '/admin/listings/rumah-uji/edit',
            '/admin/developers',
            '/admin/developers/create',
            '/admin/blog',
            '/admin/blog/create',
            '/admin/blog/artikel-uji/edit',
            '/admin/users',
            '/admin/users/create',
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
