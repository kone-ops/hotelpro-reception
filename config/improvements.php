<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Améliorations
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => true,
        'position' => 'toast-top-right', // toast-top-right, toast-bottom-right, etc.
        'timeout' => 5000, // millisecondes
        'progress_bar' => true,
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => [
            'stats' => 300,          // 5 minutes
            'room_types' => 1800,    // 30 minutes
            'reservations' => 300,   // 5 minutes
        ],
    ],

    'rate_limiting' => [
        'public_form' => [
            'max_attempts' => 5,     // Nombre de soumissions
            'decay_minutes' => 60,   // Période en minutes
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    'audit' => [
        'enabled' => true,
        'log_names' => [
            'pre-reservation',
            'hotel',
            'user',
            'security',
        ],
        'retention_days' => 90, // Garder les logs 90 jours
    ],

    'search' => [
        'per_page' => 20,
        'max_results' => 1000,
        'export_formats' => ['excel', 'pdf', 'csv'],
    ],

    'ui' => [
        'onboarding_enabled' => true,
        'help_tooltips' => true,
        'dashboard_charts' => true,
        'advanced_search' => true,
    ],

    'security' => [
        'sanitize_inputs' => true,
        'allowed_html_tags' => '<p><br><strong><em><ul><ol><li>',
        'max_upload_size' => 5120, // KB
    ],

    'api' => [
        'enabled' => true,
        'version' => 'v1',
        'documentation_url' => '/api/documentation',
    ],
];





