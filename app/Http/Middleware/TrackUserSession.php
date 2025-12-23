<?php

namespace App\Http\Middleware;

use App\Services\SessionManagerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est authentifié, mettre à jour l'activité de sa session
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = $request->session()->getId();

            // Clé de cache pour éviter les mises à jour trop fréquentes
            // Mise à jour seulement toutes les 5 minutes pour optimiser les performances
            $cacheKey = "session_activity_{$user->id}_{$sessionId}";

            // Mettre à jour seulement si pas dans le cache (toutes les 5 minutes)
            if (!Cache::has($cacheKey)) {
                // Vérifier si la session existe déjà
                $sessionExists = $this->sessionManager->sessionExists($user->id, $sessionId);
                
                if (!$sessionExists) {
                    // Si la session n'existe pas, l'enregistrer (nouvelle connexion)
                    $this->sessionManager->registerSession(
                        $user,
                        $sessionId,
                        $request->ip(),
                        $request->userAgent(),
                        false, // Ne pas marquer comme de confiance automatiquement
                        null // L'empreinte sera générée automatiquement
                    );
                } else {
                    // Sinon, juste mettre à jour l'activité
                    $this->sessionManager->updateSessionActivity($user, $sessionId);
                }
                
                // Mettre en cache pendant 5 minutes
                Cache::put($cacheKey, true, now()->addMinutes(5));
            }
        }

        return $next($request);
    }
}
