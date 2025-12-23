<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Services\SessionManagerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserSession
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Valider que la session de l'utilisateur existe dans user_sessions
     * et vérifier la sécurité (IP, User-Agent)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = $request->session()->getId();

            // Vérifier que la session existe dans user_sessions
            $sessionExists = $this->sessionManager->sessionExists($user->id, $sessionId);

            if (!$sessionExists) {
                \Log::warning('Session invalide détectée - Déconnexion forcée', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'ip' => $request->ip(),
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Votre session a expiré ou est invalide. Veuillez vous reconnecter.');
            }

            // Valider la sécurité de la session (IP et User-Agent)
            // Note: Validation simplifiée pour éviter les déconnexions intempestives
            // La validation stricte peut causer des problèmes avec les proxies, VPN, etc.
            $isSecure = $this->sessionManager->validateSessionSecurity(
                $user,
                $sessionId,
                $request->ip(),
                $request->userAgent()
            );

            // Vérifier si la session est marquée comme suspecte
            $session = \App\Models\UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->first();

            if ($session && $session->is_suspicious && !$session->is_trusted_device) {
                \Log::warning('Session suspecte détectée', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'ip' => $request->ip(),
                    'suspicious_reasons' => $session->suspicious_reasons,
                ]);
                
                // Ne pas déconnecter automatiquement, mais loguer l'événement
                // L'utilisateur peut marquer l'appareil comme de confiance depuis la page des sessions
            }

            // Si la validation échoue, on log mais on ne déconnecte pas forcément
            // pour éviter les problèmes avec les changements d'IP légitimes
            if (!$isSecure) {
                \Log::warning('Session avec sécurité suspecte détectée', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'ip' => $request->ip(),
                ]);
            }
        }

        return $next($request);
    }
}

