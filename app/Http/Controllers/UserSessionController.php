<?php

namespace App\Http\Controllers;

use App\Services\SessionManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserSessionController extends Controller
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Afficher toutes les sessions actives de l'utilisateur
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();
        
        // Récupérer toutes les sessions actives
        $sessions = $this->sessionManager->getActiveSessions($user->id);
        
        // Marquer la session actuelle
        $sessions = $sessions->map(function ($session) use ($currentSessionId) {
            $session->is_current = $session->session_id === $currentSessionId;
            return $session;
        });

        // Compter les sessions actives
        $activeCount = $sessions->count();
        $maxSessions = config('session.max_sessions', 3);

        return view('profile.sessions', [
            'sessions' => $sessions,
            'activeCount' => $activeCount,
            'maxSessions' => $maxSessions,
            'currentSessionId' => $currentSessionId,
        ]);
    }

    /**
     * Déconnecter une session spécifique
     */
    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        // Empêcher la déconnexion de la session actuelle via cette route
        if ($sessionId === $currentSessionId) {
            return redirect()->route('sessions.index')
                ->with('error', 'Vous ne pouvez pas déconnecter votre session actuelle depuis cette page. Utilisez le bouton de déconnexion.');
        }

        // Vérifier que la session appartient à l'utilisateur
        $session = \App\Models\UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return redirect()->route('sessions.index')
                ->with('error', 'Session introuvable ou vous n\'avez pas la permission de la supprimer.');
        }

        // Supprimer la session
        try {
            try {
                DB::table('sessions')
                    ->where('id', $sessionId)
                    ->delete();
            } catch (\Exception $e) {
                Log::warning('Impossible de supprimer la session de la table sessions', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
                ]);
            }

            $this->sessionManager->removeSession($user, $sessionId);

            return redirect()->route('sessions.index')
                ->with('success', 'Session déconnectée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion de la session', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Une erreur est survenue lors de la déconnexion de la session.');
        }
    }

    /**
     * Déconnecter toutes les autres sessions (garder seulement la session actuelle)
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        // Récupérer toutes les sessions sauf la session actuelle
        $otherSessions = \App\Models\UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        $deletedCount = 0;

        foreach ($otherSessions as $session) {
            try {
                try {
                    DB::table('sessions')
                        ->where('id', $session->session_id)
                        ->delete();
                } catch (\Exception $e) {
                    Log::warning('Impossible de supprimer la session de la table sessions', [
                        'session_id' => $session->session_id,
                        'error' => $e->getMessage()
                    ]);
                }

                $this->sessionManager->removeSession($user, $session->session_id);
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('Erreur lors de la déconnexion d\'une session', [
                    'session_id' => $session->session_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('sessions.index')
            ->with('success', "{$deletedCount} session(s) déconnectée(s) avec succès.");
    }

    /**
     * Marquer un appareil comme de confiance
     */
    public function trust(Request $request, string $sessionId): RedirectResponse
    {
        $user = $request->user();
        
        // Vérifier que la session appartient à l'utilisateur
        $session = \App\Models\UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return redirect()->route('sessions.index')
                ->with('error', 'Session introuvable ou vous n\'avez pas la permission de la modifier.');
        }

        // Marquer comme appareil de confiance
        $this->sessionManager->markAsTrustedDevice($user, $sessionId);

        return redirect()->route('sessions.index')
            ->with('success', 'Appareil marqué comme de confiance. Vous recevrez moins d\'alertes de sécurité pour cet appareil.');
    }
}

