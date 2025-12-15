<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AllowReceptionOrHotelAdmin
{
    /**
     * Vérifier que l'utilisateur est réceptionniste OU admin hotel
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(403, 'Non authentifié');
        }
        
        // Permettre l'accès si l'utilisateur est réceptionniste OU admin hotel
        if ($user->hasRole('receptionist') || $user->hasRole('hotel-admin')) {
            return $next($request);
        }
        
        // Si super-admin, permettre aussi l'accès (pour la supervision)
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        abort(403, 'Accès non autorisé. Vous devez être réceptionniste ou admin hotel.');
    }
}

