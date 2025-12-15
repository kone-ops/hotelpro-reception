<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de sanitization des inputs
 * Protection contre XSS et injections
 */
class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Nettoyer les tags HTML dangereux
                $value = strip_tags($value, '<p><br><strong><em><ul><ol><li>');
                // Supprimer les scripts
                $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
                // Trim whitespace
                $value = trim($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}





