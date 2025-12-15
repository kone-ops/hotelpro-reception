<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'hotel_id',
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec l'hôtel
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Obtenir une valeur de configuration pour un hôtel spécifique
     */
    public static function get(string $key, $default = null, $hotelId = null)
    {
        $cacheKey = $hotelId ? "setting.{$hotelId}.{$key}" : "setting.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $hotelId) {
            $query = static::where('key', $key)->where('is_active', true);
            
            if ($hotelId) {
                $query->where('hotel_id', $hotelId);
            } else {
                $query->whereNull('hotel_id');
            }
            
            $setting = $query->first();
            
            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Définir une valeur de configuration pour un hôtel
     */
    public static function set(string $key, $value, string $type = 'string', $hotelId = null): void
    {
        $setting = static::firstOrCreate([
            'key' => $key,
            'hotel_id' => $hotelId
        ]);
        
        $setting->update([
            'value' => is_array($value) ? json_encode($value) : $value,
            'type' => $type,
        ]);

        $cacheKey = $hotelId ? "setting.{$hotelId}.{$key}" : "setting.{$key}";
        Cache::forget($cacheKey);
    }

    /**
     * Vérifier si une fonctionnalité est activée pour un hôtel
     */
    public static function isEnabled(string $key, $hotelId = null): bool
    {
        return (bool) static::get($key, false, $hotelId);
    }

    /**
     * Convertir la valeur selon le type
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return explode(',', $value);
            default:
                return $value;
        }
    }

    /**
     * Obtenir tous les paramètres d'un groupe pour un hôtel
     */
    public static function getGroup(string $group, $hotelId = null): array
    {
        $cacheKey = $hotelId ? "settings.group.{$hotelId}.{$group}" : "settings.group.{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group, $hotelId) {
            $query = static::where('group', $group)->where('is_active', true);
            
            if ($hotelId) {
                $query->where('hotel_id', $hotelId);
            } else {
                $query->whereNull('hotel_id');
            }
            
            return $query->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => static::castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * Invalider le cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
