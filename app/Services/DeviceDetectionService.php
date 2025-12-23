<?php

namespace App\Services;

class DeviceDetectionService
{
    /**
     * Analyser le User-Agent pour extraire les informations de l'appareil
     */
    public function parseUserAgent(string $userAgent): array
    {
        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $platform = $this->detectPlatform($userAgent);
        $deviceName = $this->generateDeviceName($userAgent, $deviceType, $platform);

        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Détecter le type d'appareil
     */
    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Détecter le navigateur
     */
    protected function detectBrowser(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'chrome') !== false && strpos($userAgent, 'edg') === false) {
            return 'Chrome';
        }

        if (strpos($userAgent, 'firefox') !== false) {
            return 'Firefox';
        }

        if (strpos($userAgent, 'safari') !== false && strpos($userAgent, 'chrome') === false) {
            return 'Safari';
        }

        if (strpos($userAgent, 'edg') !== false) {
            return 'Edge';
        }

        if (strpos($userAgent, 'opera') !== false || strpos($userAgent, 'opr') !== false) {
            return 'Opera';
        }

        return 'Unknown';
    }

    /**
     * Détecter la plateforme
     */
    protected function detectPlatform(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'windows') !== false) {
            if (preg_match('/windows nt 10/i', $userAgent)) {
                return 'Windows 10/11';
            }
            if (preg_match('/windows nt 6.3/i', $userAgent)) {
                return 'Windows 8.1';
            }
            if (preg_match('/windows nt 6.2/i', $userAgent)) {
                return 'Windows 8';
            }
            if (preg_match('/windows nt 6.1/i', $userAgent)) {
                return 'Windows 7';
            }
            return 'Windows';
        }

        if (strpos($userAgent, 'mac os x') !== false || strpos($userAgent, 'macintosh') !== false) {
            return 'macOS';
        }

        if (strpos($userAgent, 'linux') !== false) {
            return 'Linux';
        }

        if (strpos($userAgent, 'android') !== false) {
            return 'Android';
        }

        if (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'iOS';
        }

        return 'Unknown';
    }

    /**
     * Générer un nom d'appareil lisible
     */
    protected function generateDeviceName(string $userAgent, string $deviceType, string $platform): string
    {
        $parts = [];

        // Ajouter la plateforme
        if ($platform !== 'Unknown') {
            $parts[] = $platform;
        }

        // Ajouter le type d'appareil si ce n'est pas un desktop
        if ($deviceType !== 'desktop') {
            $parts[] = ucfirst($deviceType);
        }

        // Détecter des modèles spécifiques
        $userAgentLower = strtolower($userAgent);
        
        if (preg_match('/iphone/i', $userAgent)) {
            $parts[] = 'iPhone';
        } elseif (preg_match('/ipad/i', $userAgent)) {
            $parts[] = 'iPad';
        } elseif (preg_match('/android/i', $userAgent)) {
            // Essayer d'extraire le modèle Android
            if (preg_match('/android [\d.]+; ([^)]+)/i', $userAgent, $matches)) {
                $model = trim($matches[1]);
                if (strlen($model) < 30) { // Éviter les strings trop longues
                    $parts[] = $model;
                }
            }
        }

        return implode(' - ', $parts) ?: 'Unknown Device';
    }

    /**
     * Générer une empreinte digitale de l'appareil
     * Basée sur User-Agent, résolution d'écran (si disponible), timezone, etc.
     */
    public function generateDeviceFingerprint(string $userAgent, ?string $screenResolution = null, ?string $timezone = null): string
    {
        $components = [
            $userAgent,
            $screenResolution ?? 'unknown',
            $timezone ?? 'unknown',
        ];

        return hash('sha256', implode('|', $components));
    }
}

