<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\UiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UiSettingController extends Controller
{
    /**
     * Afficher la page de gestion des paramètres UI
     */
    public function index()
    {
        $settings = UiSetting::where('is_active', true)
            ->orderBy('category')
            ->orderBy('label')
            ->get()
            ->groupBy('category');

        return view('super.ui-settings.index', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres UI
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->settings as $key => $value) {
            $setting = UiSetting::where('key', $key)->first();
            
            if ($setting) {
                // Valider les limites min/max
                if ($setting->min_value !== null && $value < $setting->min_value) {
                    $value = $setting->min_value;
                }
                if ($setting->max_value !== null && $value > $setting->max_value) {
                    $value = $setting->max_value;
                }
                
                $setting->update(['value' => $value]);
            }
        }

        // Vider le cache
        UiSetting::clearCache();

        // Si c'est une requête AJAX, retourner JSON
        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Les paramètres ont été mis à jour avec succès.'
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Content-Type-Options' => 'nosniff'
            ]);
        }

        return redirect()
            ->route('super.ui-settings.index')
            ->with('success', 'Les paramètres de l\'interface ont été mis à jour avec succès.');
    }

    /**
     * Réinitialiser aux valeurs par défaut
     */
    public function reset()
    {
        UiSetting::resetToDefaults();

        return redirect()
            ->route('super.ui-settings.index')
            ->with('success', 'Les paramètres de l\'interface ont été réinitialisés aux valeurs par défaut.');
    }

    /**
     * Prévisualiser les changements
     */
    public function preview(Request $request)
    {
        $settings = [];
        
        foreach ($request->settings ?? [] as $key => $value) {
            $setting = UiSetting::where('key', $key)->first();
            if ($setting) {
                $unit = $setting->unit ?? '';
                $settings[$key] = $value . $unit;
            }
        }

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
}
