<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureHotelAccess
{
    /**
     * Vérifier que l'utilisateur a accès uniquement aux données de son hôtel
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Sauvegarder l'URL actuelle pour pouvoir y revenir en cas d'erreur
        if ($request->method() === 'GET') {
            session()->put('previous_url', $request->fullUrl());
        }
        
        // Si l'utilisateur n'a pas d'hôtel assigné, refuser l'accès
        if (!$user->hotel_id) {
            return $this->handleUnauthorized($request, 'Aucun hôtel assigné à cet utilisateur');
        }
        
        // Vérifier les paramètres de route contenant des modèles liés à un hôtel
        $routeParameters = $request->route()->parameters();
        
        foreach ($routeParameters as $key => $parameter) {
            // Si c'est un modèle avec hotel_id, vérifier l'appartenance
            if (is_object($parameter) && method_exists($parameter, 'getAttribute')) {
                if ($parameter->getAttribute('hotel_id') && $parameter->hotel_id !== $user->hotel_id) {
                    return $this->handleUnauthorized($request, 'Accès non autorisé - Cette ressource appartient à un autre hôtel');
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Gérer les accès non autorisés en redirigeant vers la page précédente
     */
    protected function handleUnauthorized(Request $request, string $message)
    {
        // Essayer de rediriger vers la page précédente
        $previousUrl = session()->get('previous_url') ?? $request->header('Referer');
        
        if ($previousUrl && parse_url($previousUrl, PHP_URL_HOST) === $request->getHost()) {
            return redirect($previousUrl)->with('error', $message);
        }
        
        // Sinon, rediriger vers le dashboard approprié
        $user = Auth::user();
        if ($user->hasRole('super-admin')) {
            return redirect()->route('super.dashboard')->with('error', $message);
        } elseif ($user->hasRole('hotel-admin')) {
            return redirect()->route('hotel.dashboard')->with('error', $message);
        } elseif ($user->hasRole('receptionist')) {
            return redirect()->route('reception.dashboard')->with('error', $message);
        }
        
        // Dernier recours
        abort(403, $message);
    }
}
