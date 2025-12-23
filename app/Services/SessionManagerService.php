<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Services\GeolocationService;
use App\Services\DeviceDetectionService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionManagerService
{
    protected GeolocationService $geolocationService;
    protected DeviceDetectionService $deviceDetectionService;
    protected ?NotificationService $notificationService;

    public function __construct(
        GeolocationService $geolocationService,
        DeviceDetectionService $deviceDetectionService,
        ?NotificationService $notificationService = null
    ) {
        $this->geolocationService = $geolocationService;
        $this->deviceDetectionService = $deviceDetectionService;
        $this->notificationService = $notificationService;
    }
    /**
     * Obtenir le nombre maximum de sessions simultanées autorisées
     * Utilise la configuration au lieu d'une constante codée en dur
     */
    public function getMaxSessions(): int
    {
        return config('session.max_sessions', 3);
    }

    /**
     * Enregistrer une nouvelle session pour un utilisateur
     */
    public function registerSession(
        User $user,
        string $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        bool $isTrustedDevice = false,
        ?string $deviceFingerprint = null
    ): void {
        $ipAddress = $ipAddress ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();

        // Nettoyer les sessions expirées de l'utilisateur
        $this->cleanExpiredSessions($user->id);

        // Compter les sessions actives
        $activeSessions = $this->getActiveSessionsCount($user->id);

        // Si on atteint la limite, supprimer la session la plus ancienne
        if ($activeSessions >= $this->getMaxSessions()) {
            $this->removeOldestSession($user->id);
        }

        // Détecter les informations de l'appareil
        $deviceInfo = $this->deviceDetectionService->parseUserAgent($userAgent);
        
        // Obtenir la géolocalisation
        $location = $this->geolocationService->getLocationFromIp($ipAddress);
        
        // Générer l'empreinte digitale si non fournie
        if (!$deviceFingerprint) {
            $deviceFingerprint = $this->deviceDetectionService->generateDeviceFingerprint(
                $userAgent,
                request()->header('X-Screen-Resolution'),
                request()->header('X-Timezone')
            );
        }

        // Détecter les anomalies
        $anomalies = $this->detectAnomalies($user, $ipAddress, $userAgent, $deviceFingerprint);
        $isSuspicious = !empty($anomalies);

        // Vérifier si c'est une nouvelle session ou une mise à jour
        $existingSession = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        $isNewSession = !$existingSession;

        // Créer ou mettre à jour la session
        $sessionData = [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity' => now(),
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'country' => $location['country'] ?? null,
            'city' => $location['city'] ?? null,
            'region' => $location['region'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'is_trusted_device' => $isTrustedDevice,
            'device_fingerprint' => $deviceFingerprint,
            'is_suspicious' => $isSuspicious,
            'suspicious_reasons' => $isSuspicious ? $anomalies : null,
            'last_seen_at' => now(),
        ];

        if ($isNewSession) {
            $sessionData['first_seen_at'] = now();
        }

        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
            ],
            $sessionData
        );

        // Envoyer une notification pour une nouvelle connexion
        if ($isNewSession && $this->notificationService) {
            $this->sendNewConnectionNotification($user, $ipAddress, $deviceInfo, $location, $isSuspicious);
        }
    }

    /**
     * Mettre à jour l'activité d'une session
     */
    public function updateSessionActivity(User $user, string $sessionId): void
    {
        UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->update([
                'last_activity' => now(),
                'last_seen_at' => now(),
            ]);
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
            \Log::info('Session supprimée automatiquement (limite atteinte)', [
                'user_id' => $userId,
                'session_id' => $oldestSession->session_id,
                'ip' => $oldestSession->ip_address,
                'reason' => 'max_sessions_reached',
            ]);

            try {
                DB::table('sessions')
                    ->where('id', $oldestSession->session_id)
                    ->delete();
            } catch (\Exception $e) {
                \Log::warning('Impossible de supprimer la session de la table sessions', [
                    'session_id' => $oldestSession->session_id,
                    'error' => $e->getMessage()
                ]);
            }

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
            try {
                DB::table('sessions')
                    ->where('id', $session->session_id)
                    ->delete();
            } catch (\Exception $e) {
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
        return $this->getActiveSessionsCount($userId) < $this->getMaxSessions();
    }

    /**
     * Vérifier si une session existe et est valide
     */
    public function sessionExists(int $userId, string $sessionId): bool
    {
        return UserSession::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->active()
            ->exists();
    }

    /**
     * Valider la sécurité d'une session (IP et User-Agent)
     * Détecte les changements suspects qui pourraient indiquer un vol de session
     * Version simplifiée pour éviter les déconnexions intempestives
     * 
     * @param User $user
     * @param string $sessionId
     * @param string $currentIp
     * @param string $currentUserAgent
     * @return bool True si la session est sécurisée, false sinon
     */
    public function validateSessionSecurity(User $user, string $sessionId, string $currentIp, string $currentUserAgent): bool
    {
        $session = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            \Log::warning('Session non trouvée lors de la validation de sécurité', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
            ]);
            return false;
        }

        // Vérifier le changement de User-Agent (très suspect)
        if ($session->user_agent && $session->user_agent !== $currentUserAgent) {
            \Log::warning('Changement de User-Agent détecté - Session compromise possible', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'old_user_agent' => $session->user_agent,
                'new_user_agent' => $currentUserAgent,
                'old_ip' => $session->ip_address,
                'new_ip' => $currentIp,
            ]);

            // Optionnel: déconnecter en cas de changement de User-Agent
            // Pour l'instant, on log seulement pour éviter les problèmes avec les proxies/VPN
            // $this->removeSession($user, $sessionId);
            // try {
            //     DB::table('sessions')->where('id', $sessionId)->delete();
            // } catch (\Exception $e) {
            //     \Log::warning('Impossible de supprimer la session compromise', [
            //         'session_id' => $sessionId,
            //         'error' => $e->getMessage()
            //     ]);
            // }

            // On retourne true quand même pour éviter les déconnexions intempestives
            // mais on log l'événement
            return true;
        }

        // Vérifier le changement d'IP (moins critique mais à surveiller)
        if ($session->ip_address && $session->ip_address !== $currentIp) {
            \Log::info('Changement d\'IP détecté', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'old_ip' => $session->ip_address,
                'new_ip' => $currentIp,
            ]);

            // Mettre à jour l'IP (les utilisateurs peuvent changer de réseau)
            $session->update(['ip_address' => $currentIp]);
        }

        return true;
    }

    /**
     * Détecter les anomalies dans une session
     */
    protected function detectAnomalies(User $user, string $ipAddress, string $userAgent, string $deviceFingerprint): array
    {
        $anomalies = [];

        // Récupérer les sessions précédentes de l'utilisateur
        $previousSessions = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', request()->session()->getId())
            ->whereNotNull('device_fingerprint')
            ->orderBy('last_activity', 'desc')
            ->limit(10)
            ->get();

        if ($previousSessions->isEmpty()) {
            // Première connexion, pas d'anomalie
            return $anomalies;
        }

        // Vérifier si l'empreinte digitale est nouvelle
        $knownFingerprints = $previousSessions->pluck('device_fingerprint')->unique();
        if (!$knownFingerprints->contains($deviceFingerprint)) {
            $anomalies[] = 'Nouvel appareil détecté';
        }

        // Vérifier si l'IP est nouvelle
        $knownIps = $previousSessions->pluck('ip_address')->unique();
        if (!$knownIps->contains($ipAddress)) {
            $anomalies[] = 'Nouvelle adresse IP détectée';
        }

        // Vérifier si le User-Agent est très différent
        $knownUserAgents = $previousSessions->pluck('user_agent')->unique();
        $isSimilarUserAgent = $knownUserAgents->contains(function ($knownUA) use ($userAgent) {
            // Comparaison basique : même navigateur et plateforme
            $knownInfo = $this->deviceDetectionService->parseUserAgent($knownUA);
            $currentInfo = $this->deviceDetectionService->parseUserAgent($userAgent);
            
            return $knownInfo['browser'] === $currentInfo['browser'] 
                && $knownInfo['platform'] === $currentInfo['platform'];
        });

        if (!$isSimilarUserAgent) {
            $anomalies[] = 'Navigateur ou système d\'exploitation différent';
        }

        // Vérifier si la géolocalisation est très différente
        $location = $this->geolocationService->getLocationFromIp($ipAddress);
        if ($location && $location['country']) {
            $knownCountries = $previousSessions->whereNotNull('country')->pluck('country')->unique();
            if (!$knownCountries->contains($location['country']) && $knownCountries->count() > 0) {
                $anomalies[] = 'Connexion depuis un nouveau pays (' . $location['country'] . ')';
            }
        }

        return $anomalies;
    }

    /**
     * Envoyer une notification pour une nouvelle connexion
     */
    protected function sendNewConnectionNotification(
        User $user,
        string $ipAddress,
        array $deviceInfo,
        ?array $location,
        bool $isSuspicious
    ): void {
        if (!$this->notificationService) {
            return;
        }

        $locationText = 'Localisation inconnue';
        if ($location && $location['country']) {
            $parts = array_filter([$location['city'], $location['region'], $location['country']]);
            $locationText = implode(', ', $parts);
        }

        $deviceText = "{$deviceInfo['device_name']} ({$deviceInfo['browser']})";
        
        $title = $isSuspicious 
            ? '⚠️ Nouvelle connexion suspecte détectée'
            : '🔐 Nouvelle connexion détectée';
        
        $message = "Une nouvelle connexion a été détectée depuis votre compte.\n\n"
            . "📍 Localisation: {$locationText}\n"
            . "💻 Appareil: {$deviceText}\n"
            . "🌐 IP: {$ipAddress}\n"
            . "🕐 Date: " . now()->format('d/m/Y H:i');

        if ($isSuspicious) {
            $message .= "\n\n⚠️ Cette connexion présente des caractéristiques inhabituelles.";
        }

        $this->notificationService->create(
            $user,
            'new_session',
            $title,
            $message,
            $isSuspicious ? 'warning' : 'info',
            null,
            null,
            [
                'ip_address' => $ipAddress,
                'device_info' => $deviceInfo,
                'location' => $location,
                'is_suspicious' => $isSuspicious,
            ],
            route('sessions.index'),
            'Voir mes sessions'
        );
    }

    /**
     * Marquer un appareil comme de confiance
     */
    public function markAsTrustedDevice(User $user, string $sessionId): void
    {
        UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->update(['is_trusted_device' => true]);
    }

    /**
     * Obtenir les sessions suspectes d'un utilisateur
     */
    public function getSuspiciousSessions(int $userId)
    {
        return UserSession::where('user_id', $userId)
            ->where('is_suspicious', true)
            ->orderBy('last_activity', 'desc')
            ->get();
    }
}
