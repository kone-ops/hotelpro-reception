<?php

namespace App\Core;

use App\Models\Hotel;

class SettingsResolver
{
    /**
     * Vérifie si un module est activé pour un hôtel.
     * Les paramètres sont stockés dans hotel->settings['modules'][$module].
     */
    /** Valeurs par défaut pour les modules (true = activé par défaut pour tous les hôtels). */
    private static array $moduleDefaults = [
        'housekeeping' => true,
        'laundry' => true,
    ];

    public static function isModuleEnabled(Hotel $hotel, string $module): bool
    {
        $settings = $hotel->settings ?? [];
        $modules = $settings['modules'] ?? [];
        $default = self::$moduleDefaults[$module] ?? false;

        return (bool) ($modules[$module] ?? $default);
    }

    /**
     * Active ou désactive un module pour un hôtel.
     */
    public static function setModuleEnabled(Hotel $hotel, string $module, bool $enabled): void
    {
        $settings = $hotel->settings ?? [];
        $settings['modules'] = $settings['modules'] ?? [];
        $settings['modules'][$module] = $enabled;
        $hotel->update(['settings' => $settings]);
    }

    /**
     * Liste des modules reconnus (pour l'interface SuperAdmin).
     * Les libellés sont traduits selon la locale courante (lang/modules.php).
     */
    public static function getAvailableModules(): array
    {
        return [
            'housekeeping' => [
                'label' => __('modules.housekeeping.label'),
                'description' => __('modules.housekeeping.description'),
            ],
            'laundry' => [
                'label' => __('modules.laundry.label'),
                'description' => __('modules.laundry.description'),
            ],
        ];
    }
}
