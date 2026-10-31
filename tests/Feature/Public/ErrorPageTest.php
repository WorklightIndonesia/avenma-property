<?php

namespace Tests\Feature\Public;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_url_renders_the_branded_not_found_page(): void
    {
        $response = $this->get('/halaman-yang-tidak-ada');

        $response->assertNotFound();
        $response->assertSee('Halaman tidak ditemukan');
        $response->assertSee('Lewati ke konten');
        $response->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_an_unpublished_listing_is_not_found(): void
    {
        $this->get('/properti/rumah-tidak-tayang')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_an_agen_gets_the_branded_forbidden_page(): void
    {
        $agen = User::factory()->create(['role' => User::ROLE_AGEN]);

        $this->actingAs($agen)
            ->get('/admin/users')
            ->assertForbidden()
            ->assertSee('Anda tidak punya akses ke halaman ini');
    }

    public function test_every_error_page_uses_one_h1_and_the_shared_shell(): void
    {
        foreach (['403', '404', '419', '429', '500', '503'] as $code) {
            $html = view("errors.{$code}")->render();

            $this->assertSame(1, substr_count($html, '<h1'), "errors/{$code}: exactly one h1");
            $this->assertStringContainsString('lang="', $html, "errors/{$code}: html lang");
            $this->assertStringContainsString('href="#main"', $html, "errors/{$code}: skip link");
            $this->assertStringContainsString('id="main"', $html, "errors/{$code}: skip target");
            $this->assertStringContainsString('noindex', $html, "errors/{$code}: noindex");
        }
    }
}
