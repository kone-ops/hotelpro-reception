<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Hotel;
use App\Models\Reservation;

/**
 * Service de gestion du cache optimisé pour Redis
 * Améliore significativement les performances de l'application
 */
class CacheService
{
    // Durées de vie du cache (en secondes)
    const TTL_SHORT = 300;      // 5 minutes - données très volatiles
    const TTL_MEDIUM = 1800;    // 30 minutes - données peu volatiles
    const TTL_LONG = 3600;      // 1 heure - données stables
    const TTL_DAY = 86400;      // 24 heures - données rarement changées

    /**
     * Obtenir les statistiques d'un hôtel (avec cache)
     * Utilisé dans les dashboards
     */
    public function getHotelStats(int $hotelId, int $ttl = self::TTL_SHORT): array
    {
        return Cache::remember(
            "hotel:{$hotelId}:stats",
            $ttl,
            function () use ($hotelId) {
                $hotel = Hotel::find($hotelId);
                if (!$hotel) return [];
                
                return [
                    'total' => $hotel->reservations()->count(),
                    'pending' => $hotel->reservations()->where('status', 'pending')->count(),
                    'validated' => $hotel->reservations()->where('status', 'validated')->count(),
                    'rejected' => $hotel->reservations()->where('status', 'rejected')->count(),
                    'checked_in' => $hotel->reservations()->where('status', 'checked_in')->count(),
                    'checked_out' => $hotel->reservations()->where('status', 'checked_out')->count(),
                    'today' => $hotel->reservations()->whereDate('created_at', today())->count(),
                    'this_week' => $hotel->reservations()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'this_month' => $hotel->reservations()->whereMonth('created_at', now()->month)->count(),
                ];
            }
        );
    }

    /**
     * Obtenir les types de chambres disponibles (avec cache)
     */
    public function getAvailableRoomTypes(int $hotelId, int $ttl = self::TTL_MEDIUM): array
    {
        return Cache::remember(
            "hotel:{$hotelId}:room_types",
            $ttl,
            function () use ($hotelId) {
                $hotel = Hotel::find($hotelId);
                if (!$hotel) return [];
                
                return $hotel->roomTypes()
                    ->where('is_available', true)
                    ->select('id', 'name', 'description', 'price', 'capacity')
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Obtenir les chambres disponibles (avec cache)
     */
    public function getAvailableRooms(int $hotelId, int $ttl = self::TTL_SHORT): array
    {
        return Cache::remember(
            "hotel:{$hotelId}:available_rooms",
            $ttl,
            function () use ($hotelId) {
                $hotel = Hotel::find($hotelId);
                if (!$hotel) return [];
                
                return $hotel->rooms()
                    ->where('status', 'available')
                    ->with('roomType:id,name')
                    ->select('id', 'number', 'room_type_id', 'status', 'floor')
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Obtenir les réservations récentes (avec cache)
     */
    public function getRecentReservations(int $hotelId, int $limit = 10, int $ttl = self::TTL_SHORT): array
    {
        return Cache::remember(
            "hotel:{$hotelId}:recent_reservations:{$limit}",
            $ttl,
            function () use ($hotelId, $limit) {
                return Reservation::where('hotel_id', $hotelId)
                    ->with(['room:id,number', 'roomType:id,name'])
                    ->latest()
                    ->take($limit)
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Obtenir la configuration d'un hôtel (avec cache long)
     */
    public function getHotelConfig(int $hotelId, int $ttl = self::TTL_DAY): ?array
    {
        return Cache::remember(
            "hotel:{$hotelId}:config",
            $ttl,
            function () use ($hotelId) {
                $hotel = Hotel::find($hotelId);
                return $hotel ? $hotel->toArray() : null;
            }
        );
    }

    /**
     * Invalider le cache d'un hôtel spécifique
     */
    public function clearHotelCache(int $hotelId): void
    {
        $patterns = [
            "hotel:{$hotelId}:stats",
            "hotel:{$hotelId}:room_types",
            "hotel:{$hotelId}:available_rooms",
            "hotel:{$hotelId}:recent_reservations:*",
            "hotel:{$hotelId}:config",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // Utiliser Redis scan pour les patterns avec wildcard
                $this->clearByPattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Invalider le cache par pattern (Redis uniquement)
     */
    protected function clearByPattern(string $pattern): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur clearByPattern', ['pattern' => $pattern, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Invalider tout le cache de l'application
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur clearAll cache', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtenir les statistiques du cache (Redis)
     */
    public function getCacheStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info();
                
                return [
                    'connected' => true,
                    'used_memory' => $info['used_memory_human'] ?? 'N/A',
                    'keys' => $redis->dbsize(),
                    'hits' => $info['keyspace_hits'] ?? 0,
                    'misses' => $info['keyspace_misses'] ?? 0,
                    'uptime' => $info['uptime_in_days'] ?? 0,
                ];
            }
            
            return ['connected' => false, 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Précharger le cache pour un hôtel (optimisation au démarrage)
     */
    public function warmupHotelCache(int $hotelId): void
    {
        // Précharger les données fréquemment utilisées
        $this->getHotelStats($hotelId);
        $this->getAvailableRoomTypes($hotelId);
        $this->getAvailableRooms($hotelId);
        $this->getHotelConfig($hotelId);
    }
}
