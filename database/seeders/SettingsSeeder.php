<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // ========== FONCTIONNALITÉS ==========
            [
                'key' => 'features.notifications_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer les notifications Toast',
                'description' => 'Afficher les notifications de succès/erreur avec Toastr',
                'is_active' => true,
            ],
            [
                'key' => 'features.onboarding_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer le tour guidé',
                'description' => 'Afficher le tutoriel pour les nouveaux utilisateurs',
                'is_active' => true,
            ],
            [
                'key' => 'features.help_tooltips',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer les tooltips d\'aide',
                'description' => 'Afficher les bulles d\'aide contextuelles',
                'is_active' => true,
            ],
            [
                'key' => 'features.dashboard_charts',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer les graphiques',
                'description' => 'Afficher les graphiques Chart.js dans le dashboard',
                'is_active' => true,
            ],
            [
                'key' => 'features.advanced_search',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer la recherche avancée',
                'description' => 'Afficher les filtres de recherche avancée',
                'is_active' => true,
            ],
            [
                'key' => 'features.keyboard_shortcuts',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer les raccourcis clavier',
                'description' => 'Activer la navigation par raccourcis clavier',
                'is_active' => true,
            ],
            [
                'key' => 'features.dark_mode',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer le mode sombre',
                'description' => 'Permettre le basculement entre thème clair et sombre',
                'is_active' => true,
            ],
            [
                'key' => 'features.export_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer l\'export de données',
                'description' => 'Permettre l\'export Excel/PDF/CSV',
                'is_active' => true,
            ],
            [
                'key' => 'features.api_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Activer l\'API REST',
                'description' => 'Activer les endpoints API pour mobile',
                'is_active' => true,
            ],

            // ========== SÉCURITÉ ==========
            [
                'key' => 'security.rate_limiting_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Activer le rate limiting',
                'description' => 'Limiter le nombre de requêtes (anti-spam)',
                'is_active' => true,
            ],
            [
                'key' => 'security.public_form_max_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Limite soumissions formulaire public',
                'description' => 'Nombre maximum de soumissions par heure',
                'is_active' => true,
            ],
            [
                'key' => 'security.sanitize_inputs',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Nettoyer les inputs',
                'description' => 'Sanitizer automatiquement les données entrantes',
                'is_active' => true,
            ],
            [
                'key' => 'security.audit_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Activer l\'audit trail',
                'description' => 'Enregistrer toutes les actions dans activity_logs',
                'is_active' => true,
            ],
            [
                'key' => 'security.audit_retention_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Rétention logs (jours)',
                'description' => 'Nombre de jours de conservation des logs',
                'is_active' => true,
            ],

            // ========== CACHE ==========
            [
                'key' => 'cache.enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'cache',
                'label' => 'Activer le cache',
                'description' => 'Utiliser le cache pour les performances',
                'is_active' => true,
            ],
            [
                'key' => 'cache.stats_ttl',
                'value' => '300',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'TTL statistiques (secondes)',
                'description' => 'Durée de cache des statistiques',
                'is_active' => true,
            ],
            [
                'key' => 'cache.room_types_ttl',
                'value' => '1800',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'TTL types de chambres (secondes)',
                'description' => 'Durée de cache des types de chambres',
                'is_active' => true,
            ],

            // ========== NOTIFICATIONS ==========
            [
                'key' => 'notifications.position',
                'value' => 'toast-top-right',
                'type' => 'string',
                'group' => 'notifications',
                'label' => 'Position des notifications',
                'description' => 'Position d\'affichage (toast-top-right, toast-bottom-right, etc.)',
                'is_active' => true,
            ],
            [
                'key' => 'notifications.timeout',
                'value' => '5000',
                'type' => 'integer',
                'group' => 'notifications',
                'label' => 'Durée d\'affichage (ms)',
                'description' => 'Temps avant fermeture automatique',
                'is_active' => true,
            ],
            [
                'key' => 'notifications.progress_bar',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Afficher barre de progression',
                'description' => 'Montrer la progression du timeout',
                'is_active' => true,
            ],
            [
                'key' => 'notifications.email_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Activer notifications email',
                'description' => 'Envoyer des emails de confirmation/rejet',
                'is_active' => true,
            ],

            // ========== INTERFACE ==========
            [
                'key' => 'ui.items_per_page',
                'value' => '20',
                'type' => 'integer',
                'group' => 'ui',
                'label' => 'Éléments par page',
                'description' => 'Nombre d\'éléments dans les listes paginées',
                'is_active' => true,
            ],
            [
                'key' => 'ui.date_format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'group' => 'ui',
                'label' => 'Format de date',
                'description' => 'Format d\'affichage des dates (PHP)',
                'is_active' => true,
            ],
            [
                'key' => 'ui.time_format',
                'value' => 'H:i',
                'type' => 'string',
                'group' => 'ui',
                'label' => 'Format d\'heure',
                'description' => 'Format d\'affichage de l\'heure (PHP)',
                'is_active' => true,
            ],
            [
                'key' => 'ui.default_language',
                'value' => 'fr',
                'type' => 'string',
                'group' => 'ui',
                'label' => 'Langue par défaut',
                'description' => 'Code langue (fr, en, es, etc.)',
                'is_active' => true,
            ],

            // ========== FORMULAIRE PUBLIC ==========
            [
                'key' => 'public_form.require_signature',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'public_form',
                'label' => 'Signature obligatoire',
                'description' => 'Exiger la signature électronique',
                'is_active' => true,
            ],
            [
                'key' => 'public_form.require_documents',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'public_form',
                'label' => 'Documents obligatoires',
                'description' => 'Exiger l\'upload de pièce d\'identité',
                'is_active' => true,
            ],
            [
                'key' => 'public_form.enable_camera',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'public_form',
                'label' => 'Activer la caméra',
                'description' => 'Permettre la prise de photo de documents',
                'is_active' => true,
            ],
            [
                'key' => 'public_form.auto_save',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'public_form',
                'label' => 'Sauvegarde automatique',
                'description' => 'Sauvegarder dans localStorage',
                'is_active' => true,
            ],
            [
                'key' => 'public_form.min_age',
                'value' => '17',
                'type' => 'integer',
                'group' => 'public_form',
                'label' => 'Âge minimum',
                'description' => 'Âge minimum pour réserver (années)',
                'is_active' => true,
            ],

            // ========== EXPORT ==========
            [
                'key' => 'export.formats',
                'value' => 'excel,pdf,csv',
                'type' => 'array',
                'group' => 'export',
                'label' => 'Formats d\'export',
                'description' => 'Formats autorisés (séparés par virgule)',
                'is_active' => true,
            ],
            [
                'key' => 'export.max_records',
                'value' => '5000',
                'type' => 'integer',
                'group' => 'export',
                'label' => 'Limite d\'export',
                'description' => 'Nombre maximum d\'enregistrements exportables',
                'is_active' => true,
            ],

            // ========== PERFORMANCE ==========
            [
                'key' => 'performance.eager_loading',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'performance',
                'label' => 'Eager loading automatique',
                'description' => 'Charger les relations automatiquement',
                'is_active' => true,
            ],
            [
                'key' => 'performance.prevent_lazy_loading',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'performance',
                'label' => 'Prévenir lazy loading',
                'description' => 'Erreur si lazy loading détecté (dev)',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ ' . count($settings) . ' paramètres créés avec succès !');
    }
}
