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

        $page = PageContent::where('key', 'tentang-kami')->firstOrFail();

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
}
