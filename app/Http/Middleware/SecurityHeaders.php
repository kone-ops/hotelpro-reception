<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter les headers de sécurité
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy (CSP) - Adapté pour le projet
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net cdn.datatables.net",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdn.datatables.net fonts.googleapis.com",
            "img-src 'self' data: blob: cdn.datatables.net cdn.jsdelivr.net",
            "font-src 'self' data: fonts.gstatic.com cdn.jsdelivr.net",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ];
        
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // Permissions Policy (anciennement Feature-Policy)
        $permissions = [
            'camera=(self)',
            'microphone=()' ,
            'geolocation=()',
            'payment=()',
        ];
        
        $response->headers->set('Permissions-Policy', implode(', ', $permissions));

        return $response;
    }
}

