<?php

namespace App\Http\Middleware;

use App\Services\SessionManagerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Mettre à jour l'activité de la session
            $this->sessionManager->updateSessionActivity($user, $sessionId);
        }

        return $next($request);
    }
}
