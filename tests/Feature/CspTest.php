<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CspTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_policy_uses_a_nonce_instead_of_unsafe_inline_scripts(): void
    {
        $response = $this->get('/');

        $policy = (string) $response->headers->get('Content-Security-Policy');
        $scriptSrc = $this->scriptSrc($policy);

        $this->assertMatchesRegularExpression("/'nonce-[a-f0-9]+'/", $scriptSrc);
        $this->assertStringNotContainsString("'unsafe-inline'", $scriptSrc);
        $this->assertStringContainsString("'unsafe-eval'", $scriptSrc);
    }

    public function test_public_and_admin_pages_only_use_external_or_nonce_scripts(): void
    {
        $this->assertEveryScriptIsAllowed($this->get('/')->getContent());

        $this->actingAs(User::factory()->create());

        $admin = $this->get('/admin/dashboard')->getContent();

        $this->assertStringContainsString('nonce=', $admin);
        $this->assertEveryScriptIsAllowed($admin);
    }

    private function assertEveryScriptIsAllowed(string $html): void
    {
        preg_match_all('/<script\b[^>]*>/i', $html, $matches);

        foreach ($matches[0] as $tag) {
            $this->assertTrue(
                str_contains($tag, 'src=') || str_contains($tag, 'nonce='),
                "Inline script without a nonce: {$tag}",
            );
        }
    }

    private function scriptSrc(string $policy): string
    {
        foreach (explode(';', $policy) as $directive) {
            if (str_starts_with(trim($directive), 'script-src')) {
                return $directive;
            }
        }

        return '';
    }
}
