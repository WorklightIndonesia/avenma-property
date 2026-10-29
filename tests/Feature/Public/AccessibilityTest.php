<?php

namespace Tests\Feature\Public;

use Tests\Feature\AccessibilityTestCase;

class AccessibilityTest extends AccessibilityTestCase
{
    public function test_every_public_screen_keeps_its_structure(): void
    {
        $this->seedContent();

        $screens = [
            '/',
            '/tentang-kami',
            '/kontak',
            '/syarat-ketentuan',
            '/properti',
            '/properti/rumah-uji',
            '/blog',
            '/blog/artikel-uji',
        ];

        foreach ($screens as $url) {
            $this->assertAccessible($url);
        }
    }
}
