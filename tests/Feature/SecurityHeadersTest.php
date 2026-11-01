<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_a_public_page_carries_the_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString('frame-src https://maps.google.com', $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
    }

    public function test_an_error_page_carries_them_too(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
