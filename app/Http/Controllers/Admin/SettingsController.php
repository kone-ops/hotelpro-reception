<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des paramètres configurables
 */
class SettingsController extends Controller
{
    /**
     * Afficher tous les paramètres
     */
    public function index()
    {
        // Exclure le groupe 'impression' car il a sa propre page dédiée
        $settings = Setting::orderBy('group')
            ->orderBy('label')
            ->where('group', '!=', 'impression')
            ->get()
            ->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                // Gérer les checkboxes (boolean)
                if ($setting->type === 'boolean') {
                    $value = $value === 'on' || $value === '1' || $value === true ? 'true' : 'false';
                }
                
                $setting->update(['value' => $value]);
            }
        }

        // Gérer les checkboxes non cochées (absentes du POST)
        $booleanSettings = Setting::where('type', 'boolean')->get();
        foreach ($booleanSettings as $setting) {
            if (!isset($request->settings[$setting->key])) {
                $setting->update(['value' => 'false']);
            }
        }

        Setting::clearCache();

        return redirect()->route('super.settings.index')
            ->with('success', 'Paramètres mis à jour avec succès !');
    }

    /**
     * Réinitialiser aux valeurs par défaut
     */
    public function reset()
    {
        \Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
        
        Setting::clearCache();

        return redirect()->route('super.settings.index')
            ->with('success', 'Paramètres réinitialisés aux valeurs par défaut !');
    }

    /**
     * Activer/Désactiver un paramètre
     */
    public function toggle(Setting $setting)
    {
        $setting->update(['is_active' => !$setting->is_active]);
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'is_active' => $setting->is_active,
        ]);
    }

    /**
     * Vider le cache
     */
    public function clearCache()
    {
        Setting::clearCache();
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');

        return redirect()->route('super.settings.index')
            ->with('success', 'Cache vidé avec succès !');
    }
}
