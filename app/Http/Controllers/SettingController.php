<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Afficher la page des paramètres d'impression
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Déterminer l'hotel_id à utiliser
        if ($user->hasRole('super-admin')) {
            $hotelId = $request->input('hotel_id');
        } else {
            $hotelId = $user->hotel_id;
        }
        
        // Récupérer les valeurs des paramètres pour l'hôtel concerné
        $query = Setting::query();
        
        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        } else {
            $query->whereNull('hotel_id');
        }
        
        $settings = $query->whereIn('key', [
                'signature_formulaire',
                'signature_fiche_police', 
                'auto_print_police', 
                'manual_print_only',
                // Backward compatibility
                'signature_obligatoire'
            ])
            ->get()
            ->keyBy('key');
        
        $settingValues = [
            'signature_formulaire' => $settings->get('signature_formulaire')?->value 
                ?? $settings->get('signature_obligatoire')?->value 
                ?? '0',
            'signature_fiche_police' => $settings->get('signature_fiche_police')?->value ?? '1',
            'auto_print_police' => $settings->get('auto_print_police')?->value ?? '0',
            'manual_print_only' => $settings->get('manual_print_only')?->value ?? '0',
        ];
        
        // Si super-admin, ajouter liste des hôtels
        if ($user->hasRole('super-admin')) {
            $hotels = \App\Models\Hotel::orderBy('name')->get();
            return view('admin.settings.impression', compact('settingValues', 'hotels', 'hotelId'));
        }
        
        return view('admin.settings.impression', compact('settingValues'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Déterminer l'hotel_id
        $hotelId = $request->input('hotel_id', $user->hotel_id);
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $hotelId !== $user->hotel_id) {
            abort(403, 'Accès non autorisé');
        }
        
        $request->validate([
            'hotel_id' => $user->hasRole('super-admin') ? 'nullable|exists:hotels,id' : 'nullable',
            'settings' => 'nullable|array',
            'settings.*' => 'nullable'
        ]);

        // IMPORTANT: Les checkboxes non cochées ne sont PAS envoyées !
        // On doit gérer TOUTES les clés possibles
        $allKeys = [
            'signature_formulaire',
            'signature_fiche_police',
            'auto_print_police',
            'manual_print_only'
        ];
        
        foreach ($allKeys as $key) {
            // La valeur est '1' si la checkbox est dans $request->settings, sinon '0'
            $value = isset($request->settings[$key]) && $request->settings[$key] == '1' ? '1' : '0';
            
            Setting::updateOrCreate(
                ['key' => $key, 'hotel_id' => $hotelId],
                [
                    'value' => $value,
                    'type' => 'boolean',
                    'group' => 'impression',
                    'label' => $this->getSettingLabel($key),
                    'description' => $this->getSettingDescription($key),
                    'is_active' => true
                ]
            );
        }

        // Vider le cache des paramètres pour cet hôtel
        $this->clearSettingsCache($hotelId);

        $routeName = $user->hasRole('super-admin') ? 'super.settings.impression' : 'hotel.settings.impression';
        
        // Si super-admin avec hotel_id, ajouter le paramètre à l'URL
        if ($user->hasRole('super-admin') && $hotelId) {
            return redirect()->route($routeName, ['hotel_id' => $hotelId])
                ->with('success', 'Paramètres d\'impression mis à jour avec succès.');
        }
        
        return redirect()->route($routeName)
            ->with('success', 'Paramètres d\'impression mis à jour avec succès.');
    }
    
    /**
     * Obtenir le label d'un paramètre
     */
    private function getSettingLabel(string $key): string
    {
        $labels = [
            'signature_formulaire' => 'Signature Formulaire Public',
            'signature_fiche_police' => 'Signature Fiche de Police',
            'auto_print_police' => 'Impression Automatique Fiche Police',
            'manual_print_only' => 'Impression Manuelle Uniquement'
        ];
        
        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
    
    /**
     * Obtenir la description d'un paramètre
     */
    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'signature_formulaire' => 'Exiger une signature numérique dans le formulaire de pré-réservation',
            'signature_fiche_police' => 'Afficher la signature du client sur la fiche de police',
            'auto_print_police' => 'Imprimer automatiquement la fiche police après validation de réservation',
            'manual_print_only' => 'Désactiver l\'impression automatique et afficher un bouton manuel'
        ];
        
        return $descriptions[$key] ?? '';
    }

    /**
     * Convertir la valeur selon le type
     */
    private function convertValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string) (int) $value;
            case 'float':
                return (string) (float) $value;
            case 'json':
                return is_array($value) ? json_encode($value) : $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Réinitialiser les paramètres par défaut
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $hotelId = $request->input('hotel_id', $user->hotel_id);
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $hotelId !== $user->hotel_id) {
            abort(403, 'Accès non autorisé');
        }
        
        $defaultSettings = [
            'signature_formulaire' => [
                'key' => 'signature_formulaire',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Signature Formulaire Public',
                'description' => 'Exiger une signature numérique dans le formulaire de pré-réservation',
                'hotel_id' => $hotelId,
                'is_active' => true
            ],
            'signature_fiche_police' => [
                'key' => 'signature_fiche_police',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Signature Fiche de Police',
                'description' => 'Afficher la signature du client sur la fiche de police',
                'hotel_id' => $hotelId,
                'is_active' => true
            ],
            'auto_print_police' => [
                'key' => 'auto_print_police',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression Automatique Fiche Police',
                'description' => 'Imprimer automatiquement la fiche police après validation de réservation',
                'hotel_id' => $hotelId,
                'is_active' => true
            ],
            'manual_print_only' => [
                'key' => 'manual_print_only',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression Manuelle Uniquement',
                'description' => 'Désactiver l\'impression automatique et afficher un bouton manuel',
                'hotel_id' => $hotelId,
                'is_active' => true
            ]
        ];

        foreach ($defaultSettings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key, 'hotel_id' => $hotelId],
                $data
            );
        }

        $this->clearSettingsCache($hotelId);

        $routeName = $user->hasRole('super-admin') ? 'super.settings.impression' : 'hotel.settings.impression';
        
        // Si super-admin avec hotel_id, ajouter le paramètre à l'URL
        if ($user->hasRole('super-admin') && $hotelId) {
            return redirect()->route($routeName, ['hotel_id' => $hotelId])
                ->with('success', 'Paramètres réinitialisés avec succès.');
        }
        
        return redirect()->route($routeName)
            ->with('success', 'Paramètres réinitialisés avec succès.');
    }

    /**
     * Vider le cache des paramètres pour un hôtel spécifique
     * Compatible avec tous les drivers de cache (file, database, redis, etc.)
     */
    private function clearSettingsCache($hotelId): void
    {
        // Vider les clés individuelles de cache
        $keys = [
            'signature_formulaire',
            'signature_fiche_police',
            'auto_print_police',
            'manual_print_only'
        ];
        
        foreach ($keys as $key) {
            $cacheKey = $hotelId ? "setting.{$hotelId}.{$key}" : "setting.{$key}";
            Cache::forget($cacheKey);
        }
        
        // Vider le cache du groupe impression pour cet hôtel
        $groupCacheKey = $hotelId ? "settings.group.{$hotelId}.impression" : "settings.group.impression";
        Cache::forget($groupCacheKey);
    }
}
