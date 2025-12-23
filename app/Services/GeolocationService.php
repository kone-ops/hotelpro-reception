<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeolocationService
{
    /**
     * Obtenir les informations de géolocalisation depuis une IP
     * Utilise ip-api.com (gratuit, sans clé API)
     */
    public function getLocationFromIp(string $ip): ?array
    {
        // Ignorer les IPs locales
        if ($this->isLocalIp($ip)) {
            return [
                'country' => 'Local',
                'city' => 'Local',
                'region' => 'Local',
                'latitude' => null,
                'longitude' => null,
            ];
        }

        // Utiliser le cache pour éviter trop de requêtes
        $cacheKey = "geolocation_{$ip}";
        
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip) {
            try {
                // Utiliser ip-api.com (gratuit, 45 requêtes/minute)
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,regionName,city,lat,lon,query'
                ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    $data = $response->json();
                    
                    return [
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la géolocalisation', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    /**
     * Vérifier si l'IP est locale
     */
    protected function isLocalIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            || $ip === '127.0.0.1'
            || $ip === '::1';
    }

    /**
     * Obtenir le nom du pays depuis le code pays
     */
    public function getCountryName(string $countryCode): string
    {
        $countries = [
            'FR' => 'France',
            'US' => 'États-Unis',
            'GB' => 'Royaume-Uni',
            'DE' => 'Allemagne',
            'ES' => 'Espagne',
            'IT' => 'Italie',
            'BE' => 'Belgique',
            'CH' => 'Suisse',
            'CA' => 'Canada',
            'MA' => 'Maroc',
            'DZ' => 'Algérie',
            'TN' => 'Tunisie',
            'SN' => 'Sénégal',
            'CI' => 'Côte d\'Ivoire',
            // Ajouter d'autres pays selon les besoins
        ];

        return $countries[$countryCode] ?? $countryCode;
    }
}

