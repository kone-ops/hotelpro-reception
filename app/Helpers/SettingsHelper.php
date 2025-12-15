<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Obtenir une valeur de configuration
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('setting_enabled')) {
    /**
     * Vérifier si une fonctionnalité est activée
     */
    function setting_enabled(string $key): bool
    {
        return Setting::isEnabled($key);
    }
}

if (!function_exists('settings_group')) {
    /**
     * Obtenir tous les paramètres d'un groupe
     */
    function settings_group(string $group): array
    {
        return Setting::getGroup($group);
    }
}





