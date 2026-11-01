<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers the framework does not send. The policy pins where assets and frames
 * may come from; it is not a substitute for escaping, because the inline Alpine
 * and Quill scripts need script-src 'unsafe-inline' and 'unsafe-eval'.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', $this->policy());

        return $response;
    }

    private function policy(): string
    {
        // Vite serves assets and the HMR socket from its own origin during
        // `npm run dev`; production reads the built files from /build.
        $dev = app()->environment('production')
            ? ''
            : ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'".$dev,
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data:",
            "connect-src 'self'".$dev,
            'frame-src https://maps.google.com https://www.google.com',
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
