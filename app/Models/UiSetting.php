<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UiSetting extends Model
{
    protected $fillable = [
        'category',
        'key',
        'value',
        'unit',
        'type',
        'label',
        'description',
        'min_value',
        'max_value',
        'default_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
    ];

    /**
     * Obtenir une valeur de paramètre UI
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = "ui_setting.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)
                ->where('is_active', true)
                ->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->getFormattedValue();
        });
    }

    /**
     * Définir une valeur de paramètre UI
     */
    public static function setValue(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();
        
        if ($setting) {
            $setting->update(['value' => $value]);
            Cache::forget("ui_setting.{$key}");
            Cache::forget('ui_settings.all');
        }
    }

    /**
     * Obtenir tous les paramètres actifs par catégorie
     */
    public static function getAllByCategory(): array
    {
        return Cache::remember('ui_settings.all', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('category')
                ->orderBy('label')
                ->get()
                ->groupBy('category')
                ->toArray();
        });
    }

    /**
     * Obtenir tous les paramètres sous forme de variables CSS
     */
    public static function getCssVariables(): string
    {
        return Cache::remember('ui_settings.css', 3600, function () {
            $settings = static::where('is_active', true)->get();
            $css = '';
            
            foreach ($settings as $setting) {
                $cssVar = str_replace('_', '-', $setting->key);
                $css .= "--{$cssVar}: {$setting->getFormattedValue()};\n        ";
            }
            
            return $css;
        });
    }

    /**
     * Réinitialiser aux valeurs par défaut
     */
    public static function resetToDefaults(): void
    {
        $settings = static::all();
        
        foreach ($settings as $setting) {
            $setting->update(['value' => $setting->default_value]);
        }
        
        static::clearCache();
    }

    /**
     * Obtenir la valeur formatée avec l'unité
     */
    public function getFormattedValue(): string
    {
        if ($this->unit) {
            return $this->value . $this->unit;
        }
        
        return $this->value;
    }

    /**
     * Invalider tout le cache
     */
    public static function clearCache(): void
    {
        Cache::forget('ui_settings.all');
        Cache::forget('ui_settings.css');
        static::all()->each(function ($setting) {
            Cache::forget("ui_setting.{$setting->key}");
        });
    }
}
