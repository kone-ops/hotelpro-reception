<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SessionManagerService
{
    /**
     * Nombre maximum de sessions simultanées autorisées
     */
    const MAX_SESSIONS = 3;

    /**
     * Enregistrer une nouvelle session pour un utilisateur
     */
    public function registerSession(User $user, string $sessionId, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        // Nettoyer les sessions expirées de l'utilisateur
        $this->cleanExpiredSessions($user->id);

        // Compter les sessions actives
        $activeSessions = $this->getActiveSessionsCount($user->id);

        // Si on atteint la limite, supprimer la session la plus ancienne
        if ($activeSessions >= self::MAX_SESSIONS) {
            $this->removeOldestSession($user->id);
        }

        // Créer ou mettre à jour la session
        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'last_activity' => now(),
            ]
        );
    }

    /**
     * Mettre à jour l'activité d'une session
     */
    public function updateSessionActivity(User $user, string $sessionId): void
    {
        UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->update(['last_activity' => now()]);
    }

    /**
     * Supprimer une session spécifique
     */
    public function removeSession(User $user, string $sessionId): void
    {
        UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->delete();
    }

    /**
     * Supprimer toutes les sessions d'un utilisateur
     */
    public function removeAllSessions(User $user): void
    {
        UserSession::where('user_id', $user->id)->delete();
    }

    /**
     * Obtenir le nombre de sessions actives pour un utilisateur
     */
    public function getActiveSessionsCount(int $userId): int
    {
        return UserSession::where('user_id', $userId)
            ->active()
            ->count();
    }

    /**
     * Obtenir toutes les sessions actives d'un utilisateur
     */
    public function getActiveSessions(int $userId)
    {
        return UserSession::where('user_id', $userId)
            ->active()
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Supprimer la session la plus ancienne d'un utilisateur
     */
    protected function removeOldestSession(int $userId): void
    {
        $oldestSession = UserSession::where('user_id', $userId)
            ->active()
            ->orderBy('last_activity', 'asc')
            ->first();

        if ($oldestSession) {
            // Invalider la session dans la table sessions de Laravel
            // La table sessions utilise 'id' comme clé primaire qui correspond au session_id
            try {
                DB::table('sessions')
                    ->where('id', $oldestSession->session_id)
                    ->delete();
            } catch (\Exception $e) {
                // Si la table sessions n'existe pas ou a une structure différente, continuer
                \Log::warning('Impossible de supprimer la session de la table sessions', [
                    'session_id' => $oldestSession->session_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Supprimer l'enregistrement
            $oldestSession->delete();
        }
    }

    /**
     * Nettoyer les sessions expirées d'un utilisateur
     */
    protected function cleanExpiredSessions(int $userId): void
    {
        $expiredSessions = UserSession::where('user_id', $userId)
            ->where(function($query) {
                $query->whereNull('last_activity')
                    ->orWhere('last_activity', '<', now()->subMinutes(config('session.lifetime', 4320)));
            })
            ->get();

        foreach ($expiredSessions as $session) {
            // Invalider la session dans la table sessions de Laravel
            try {
                DB::table('sessions')
                    ->where('id', $session->session_id)
                    ->delete();
            } catch (\Exception $e) {
                // Si la table sessions n'existe pas ou a une structure différente, continuer
                \Log::warning('Impossible de supprimer la session expirée de la table sessions', [
                    'session_id' => $session->session_id,
                    'error' => $e->getMessage()
                ]);
            }

            $session->delete();
        }
    }

    /**
     * Vérifier si l'utilisateur peut créer une nouvelle session
     */
    public function canCreateNewSession(int $userId): bool
    {
        $this->cleanExpiredSessions($userId);
        return $this->getActiveSessionsCount($userId) < self::MAX_SESSIONS;
    }
}

