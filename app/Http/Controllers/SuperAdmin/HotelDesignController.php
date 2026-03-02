<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelDesignController extends Controller
{
    /**
     * Afficher la page de configuration du design et du formulaire pour un hôtel
     */
    public function show(Hotel $hotel)
    {
        // Charger la configuration actuelle
        $formFieldConfig = $hotel->form_field_config ?? [];
        $settings = $hotel->settings ?? [];
        
        // Liste de tous les champs possibles du formulaire
        $availableFields = [
            'type_reservation' => ['label' => 'Type de Réservation', 'default_required' => true, 'default_visible' => true],
            'nom_groupe' => ['label' => 'Nom du Groupe', 'default_required' => false, 'default_visible' => true],
            'code_groupe' => ['label' => 'Code Groupe', 'default_required' => false, 'default_visible' => true],
            'type_piece_identite' => ['label' => 'Type de Pièce d\'Identité', 'default_required' => true, 'default_visible' => true],
            'numero_piece_identite' => ['label' => 'Numéro de Pièce d\'Identité', 'default_required' => true, 'default_visible' => true],
            'nom' => ['label' => 'Nom', 'default_required' => true, 'default_visible' => true],
            'prenom' => ['label' => 'Prénom', 'default_required' => true, 'default_visible' => true],
            'sexe' => ['label' => 'Sexe', 'default_required' => true, 'default_visible' => true],
            'date_naissance' => ['label' => 'Date de Naissance', 'default_required' => true, 'default_visible' => true],
            'lieu_naissance' => ['label' => 'Lieu de Naissance', 'default_required' => true, 'default_visible' => true],
            'nationalite' => ['label' => 'Nationalité', 'default_required' => true, 'default_visible' => true],
            'adresse' => ['label' => 'Adresse', 'default_required' => false, 'default_visible' => true],
            'telephone' => ['label' => 'Téléphone', 'default_required' => true, 'default_visible' => true],
            'email' => ['label' => 'Email', 'default_required' => true, 'default_visible' => true],
            'profession' => ['label' => 'Profession', 'default_required' => true, 'default_visible' => true],
            'date_arrivee' => ['label' => 'Date d\'Arrivée', 'default_required' => true, 'default_visible' => true],
            'heure_arrivee' => ['label' => 'Heure d\'Arrivée', 'default_required' => false, 'default_visible' => true],
            'date_depart' => ['label' => 'Date de Départ', 'default_required' => true, 'default_visible' => true],
            'nombre_adultes' => ['label' => 'Nombre d\'Adultes', 'default_required' => true, 'default_visible' => true],
            'nombre_enfants' => ['label' => 'Nombre d\'Enfants', 'default_required' => false, 'default_visible' => true],
            'venant_de' => ['label' => 'Venant de', 'default_required' => true, 'default_visible' => true],
            'type_chambre' => ['label' => 'Type de Chambre', 'default_required' => true, 'default_visible' => true],
            'room_id' => ['label' => 'Numéro de Chambre', 'default_required' => false, 'default_visible' => true],
            'piece_identite_recto' => ['label' => 'Pièce d\'Identité (Recto)', 'default_required' => true, 'default_visible' => true],
            'piece_identite_verso' => ['label' => 'Pièce d\'Identité (Verso)', 'default_required' => false, 'default_visible' => true],
            'signature' => ['label' => 'Signature', 'default_required' => true, 'default_visible' => true],
            'preferences' => ['label' => 'Préférences / Demandes spéciales', 'default_required' => false, 'default_visible' => true],
        ];
        
        // Charger les champs personnalisés triés par section puis position (numérique)
        $customFields = $hotel->formFields()
            ->orderBy('section')
            ->orderBy('position')
            ->get();
        
        // Définir les points d'insertion pour chaque section (positions des champs prédéfinis)
        $insertionPoints = $this->getInsertionPoints();
        
        return view('super.hotels.design', compact('hotel', 'formFieldConfig', 'settings', 'availableFields', 'customFields', 'insertionPoints'));
    }
    
    /**
     * Mettre à jour le design et la configuration du formulaire
     */
    public function update(Request $request, Hotel $hotel)
    {
        $data = $request->validate([
            // Design
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048',
            // Couleurs : accepter vide ou hexa (les checkboxes envoient parfois un tableau)
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',

            // Configuration des champs : visible/required peuvent être "0", "1" ou ["0","1"]
            // quand hidden + checkbox ont le même name (PHP envoie un tableau si les deux sont envoyés)
            'form_fields' => 'nullable|array',
            'form_fields.*' => 'nullable|array',
            'form_fields.*.visible' => 'nullable',
            'form_fields.*.required' => 'nullable',

            // Settings généraux
            'settings' => 'nullable|array',
        ]);
        
        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo si existe
            if ($hotel->logo) {
                $oldPath = public_path($hotel->logo);
                // Compatibilité avec anciens chemins
                if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                    $oldPath = public_path('images/logos/' . basename($hotel->logo));
                }
                if (\Illuminate\Support\Facades\File::exists($oldPath)) {
                    \Illuminate\Support\Facades\File::delete($oldPath);
                }
            }
            
            $extension = $request->file('logo')->getClientOriginalExtension();
            $filename = 'logo_' . \Illuminate\Support\Str::random(40) . '.' . $extension;
            $directory = public_path('images/logos');
            
            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }
            
            $request->file('logo')->move($directory, $filename);
            $hotel->logo = 'images/logos/' . $filename;
        }
        
        // Mettre à jour les couleurs (format hexa uniquement, sinon conserver l’existant)
        $primary = trim((string) ($request->input('primary_color') ?? ''));
        $secondary = trim((string) ($request->input('secondary_color') ?? ''));
        if ($primary !== '' && preg_match('/^#[0-9A-Fa-f]{6}$/', $primary)) {
            $hotel->primary_color = $primary;
        }
        if ($secondary !== '' && preg_match('/^#[0-9A-Fa-f]{6}$/', $secondary)) {
            $hotel->secondary_color = $secondary;
        }
        
        // Mettre à jour la configuration des champs
        if ($request->has('form_fields')) {
            // Normaliser les valeurs : convertir "0" string en false, "1" en true
            // Les checkboxes HTML envoient "1" si cochées, ou rien si non cochées
            // Les champs cachés avec value="0" garantissent qu'une valeur est toujours envoyée
            $normalizedConfig = [];
            foreach ($request->form_fields as $fieldKey => $fieldConfig) {
                // Pour les checkboxes : si coché on envoie hidden=0 + checkbox=1 (PHP peut recevoir un tableau)
                // Si décoché on envoie seulement hidden=0
                $visibleValue = $fieldConfig['visible'] ?? '0';
                $requiredValue = $fieldConfig['required'] ?? '0';
                
                $normalizedConfig[$fieldKey] = [
                    'visible' => $this->normalizeCheckboxValue($visibleValue),
                    'required' => $this->normalizeCheckboxValue($requiredValue),
                ];
            }
            $hotel->form_field_config = $normalizedConfig;
        }
        
        // Mettre à jour les settings
        if ($request->has('settings')) {
            $currentSettings = $hotel->settings ?? [];
            $hotel->settings = array_merge($currentSettings, $request->settings);
        }
        
        $hotel->save();
        
        return redirect()->route('super.hotels.design', $hotel)
            ->with('success', 'Configuration mise à jour avec succès.');
    }
    
    /**
     * Normalise une valeur de checkbox (scalaire ou tableau quand hidden+checkbox ont le même name).
     * Retourne true si la checkbox est considérée comme cochée.
     */
    private function normalizeCheckboxValue($value): bool
    {
        if (is_array($value)) {
            return in_array('1', $value, true) || in_array(1, $value, true) || in_array(true, $value, true);
        }
        return $value === '1' || $value === 1 || $value === true;
    }
    
    /**
     * Créer un nouveau champ personnalisé
     */
    public function storeField(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,tel,number,date,textarea,select,checkbox,radio',
            'required' => 'boolean',
            'active' => 'boolean',
            'options' => 'nullable|string',
            'position' => 'nullable|numeric|min:0',
            'section' => 'required|integer|min:0|max:6',
        ]);
        
        // Vérifier que la clé n'existe pas déjà pour cet hôtel
        if (FormField::where('hotel_id', $hotel->id)->where('key', $validated['key'])->exists()) {
            return back()->withErrors(['key' => 'Cette clé existe déjà pour cet hôtel.'])->withInput();
        }
        
        // Générer la clé automatiquement à partir du libellé si non fournie
        if (empty($validated['key'])) {
            $validated['key'] = Str::slug($validated['label'], '_');
        }
        
        // S'assurer que la clé est en minuscules et ne contient que des caractères autorisés
        $validated['key'] = strtolower(preg_replace('/[^a-z0-9_]/', '_', $validated['key']));
        
        // Parser les options si c'est un select/radio
        $options = null;
        if (in_array($validated['type'], ['select', 'radio']) && !empty($validated['options'])) {
            $optionsArray = array_map('trim', explode(',', $validated['options']));
            $options = $optionsArray;
        }
        
        // Déterminer la position si non fournie (dans la section spécifiée)
        if (!isset($validated['position']) || $validated['position'] === '' || $validated['position'] === null) {
            $maxPosition = FormField::where('hotel_id', $hotel->id)
                ->where('section', $validated['section'])
                ->max('position') ?? 0;
            $validated['position'] = (float) $maxPosition + 1;
        } else {
            // S'assurer que la position est un nombre (peut être décimal)
            $validated['position'] = (float) $validated['position'];
        }
        
        FormField::create([
            'hotel_id' => $hotel->id,
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'required' => $validated['required'] ?? false,
            'active' => $validated['active'] ?? true,
            'options' => $options,
            'position' => $validated['position'],
            'section' => $validated['section'],
        ]);
        
        return redirect()->route('super.hotels.design', $hotel)
            ->with('success', 'Champ personnalisé créé avec succès.');
    }
    
    /**
     * Mettre à jour un champ personnalisé
     */
    public function updateField(Request $request, Hotel $hotel, FormField $formField)
    {
        // Vérifier que le champ appartient à l'hôtel
        if ($formField->hotel_id !== $hotel->id) {
            abort(403, 'Ce champ n\'appartient pas à cet hôtel.');
        }
        
        $validated = $request->validate([
            'key' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,tel,number,date,textarea,select,radio,checkbox',
            'required' => 'boolean',
            'active' => 'boolean',
            'options' => 'nullable|string',
            'position' => 'nullable|numeric|min:0',
            'section' => 'required|integer|min:0|max:6',
        ]);
        
        // Vérifier que la clé n'existe pas déjà pour un autre champ de cet hôtel
        if (FormField::where('hotel_id', $hotel->id)
            ->where('key', $validated['key'])
            ->where('id', '!=', $formField->id)
            ->exists()) {
            return back()->withErrors(['key' => 'Cette clé existe déjà pour cet hôtel.'])->withInput();
        }
        
        // Parser les options si c'est un select/radio
        $options = null;
        if (in_array($validated['type'], ['select', 'radio']) && !empty($validated['options'])) {
            $optionsArray = array_map('trim', explode(',', $validated['options']));
            $options = $optionsArray;
        }
        
        // Gérer la position lors de la mise à jour
        $position = $formField->position;
        if (isset($validated['position']) && $validated['position'] !== '' && $validated['position'] !== null) {
            $position = (float) $validated['position'];
        }
        
        $formField->update([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'required' => $validated['required'] ?? false,
            'active' => $validated['active'] ?? true,
            'options' => $options,
            'position' => $position,
            'section' => $validated['section'],
        ]);
        
        return redirect()->route('super.hotels.design', $hotel)
            ->with('success', 'Champ personnalisé mis à jour avec succès.');
    }
    
    /**
     * Supprimer un champ personnalisé
     */
    public function destroyField(Hotel $hotel, FormField $formField)
    {
        // Vérifier que le champ appartient à l'hôtel
        if ($formField->hotel_id !== $hotel->id) {
            abort(403, 'Ce champ n\'appartient pas à cet hôtel.');
        }
        
        $formField->delete();
        
        return redirect()->route('super.hotels.design', $hotel)
            ->with('success', 'Champ personnalisé supprimé avec succès.');
    }
    
    /**
     * Supprimer plusieurs champs personnalisés
     */
    public function destroyMultipleFields(Request $request, Hotel $hotel)
    {
        $request->validate([
            'field_ids' => 'required|array',
            'field_ids.*' => 'required|integer|exists:form_fields,id',
        ]);
        
        $fieldIds = $request->input('field_ids');
        
        // Vérifier que tous les champs appartiennent à l'hôtel
        $fields = FormField::where('hotel_id', $hotel->id)
            ->whereIn('id', $fieldIds)
            ->get();
        
        if ($fields->count() !== count($fieldIds)) {
            return redirect()->route('super.hotels.design', $hotel)
                ->with('error', 'Certains champs n\'appartiennent pas à cet hôtel.');
        }
        
        $count = $fields->count();
        FormField::whereIn('id', $fieldIds)->delete();
        
        $message = $count === 1 
            ? 'Champ personnalisé supprimé avec succès.' 
            : $count . ' champs personnalisés supprimés avec succès.';
        
        return redirect()->route('super.hotels.design', $hotel)
            ->with('success', $message);
    }
    
    /**
     * Obtenir les points d'insertion pour chaque section
     * Définit où les champs personnalisés peuvent être insérés
     */
    private function getInsertionPoints(): array
    {
        return [
            0 => [ // Section 0: Recherche Client
                ['position' => 1, 'label' => 'Début de la section'],
                ['position' => 2, 'label' => 'Fin de la section'],
            ],
            1 => [ // Section 1: Type de Réservation
                ['position' => 1, 'label' => 'Après le choix du type de réservation'],
                ['position' => 2, 'label' => 'Après les champs groupe (si visible)'],
            ],
            2 => [ // Section 2: Informations Personnelles
                ['position' => 1, 'label' => 'Type de Pièce d\'Identité'],
                ['position' => 2, 'label' => 'Numéro de Pièce d\'Identité'],
                ['position' => 2.5, 'label' => 'Après Numéro de Pièce d\'Identité (Date expiration, Lieu délivrance, etc.)'],
                ['position' => 3, 'label' => 'Après les sections upload/camera'],
                ['position' => 4, 'label' => 'Nom'],
                ['position' => 5, 'label' => 'Prénom'],
                ['position' => 6, 'label' => 'Sexe, Date de naissance, Lieu de naissance'],
                ['position' => 7, 'label' => 'Nationalité'],
                ['position' => 8, 'label' => 'Fin de la section'],
            ],
            3 => [ // Section 3: Coordonnées
                ['position' => 1, 'label' => 'Adresse'],
                ['position' => 2, 'label' => 'Téléphone'],
                ['position' => 3, 'label' => 'Email'],
                ['position' => 4, 'label' => 'Profession'],
                ['position' => 5, 'label' => 'Fin de la section'],
            ],
            4 => [ // Section 4: Informations du Séjour
                ['position' => 1, 'label' => 'Venant de'],
                ['position' => 2, 'label' => 'Date d\'arrivée'],
                ['position' => 3, 'label' => 'Date de départ'],
                ['position' => 4, 'label' => 'Nombre d\'adultes'],
                ['position' => 5, 'label' => 'Nombre d\'enfants'],
                ['position' => 6, 'label' => 'Type de chambre'],
                ['position' => 7, 'label' => 'Préférences'],
                ['position' => 8, 'label' => 'Fin de la section'],
            ],
            5 => [ // Section 5: Validation
                ['position' => 1, 'label' => 'Début de la section'],
                ['position' => 2, 'label' => 'Après les checkboxes de validation'],
            ],
            6 => [ // Section 6: Signature
                ['position' => 1, 'label' => 'Début de la section'],
                ['position' => 2, 'label' => 'Après le canvas de signature'],
            ],
        ];
    }
}

