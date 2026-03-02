<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\FormField;

class FormConfigService
{
    protected $hotel;
    protected $config;
    protected $customFields;
    
    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
        $this->config = $hotel->form_field_config ?? [];
        // Récupérer les champs personnalisés triés par section puis par position (numérique avec support décimal)
        // On garde deux collections : une indexée par key pour les recherches rapides, 
        // et une ordonnée pour l'affichage
        $this->customFields = $hotel->formFields()
            ->where('active', true)
            ->orderBy('section')
            ->orderBy('position') // Tri numérique avec support des décimales
            ->get();
    }
    
    /**
     * Vérifier si un champ est visible
     */
    public function isVisible(string $fieldKey): bool
    {
        // Vérifier d'abord si c'est un champ personnalisé
        $customField = $this->customFields->firstWhere('key', $fieldKey);
        if ($customField) {
            return $customField->active;
        }
        
        if (!isset($this->config[$fieldKey])) {
            // Valeurs par défaut si non configuré
            $defaults = [
                'type_reservation' => true,
                'nom_groupe' => true,
                'code_groupe' => true,
                'type_piece_identite' => true,
                'numero_piece_identite' => true,
                'nom' => true,
                'prenom' => true,
                'sexe' => true,
                'date_naissance' => true,
                'lieu_naissance' => true,
                'nationalite' => true,
                'adresse' => true,
                'telephone' => true,
                'email' => true,
                'profession' => true,
                'date_arrivee' => true,
                'heure_arrivee' => true,
                'date_depart' => true,
                'nombre_adultes' => true,
                'nombre_enfants' => true,
                'venant_de' => true,
                'type_chambre' => true,
                'room_id' => true,
                'piece_identite_recto' => true,
                'piece_identite_verso' => true,
                'signature' => true,
                'preferences' => true,
            ];
            
            return $defaults[$fieldKey] ?? true;
        }
        
        $visible = $this->config[$fieldKey]['visible'] ?? true;
        // S'assurer que toute valeur "falsy" (false, 0, "0") est bien considérée comme non visible
        return $visible === true || $visible === 1 || $visible === '1';
    }
    
    /**
     * Vérifier si un champ est obligatoire
     */
    public function isRequired(string $fieldKey): bool
    {
        // Un champ ne peut pas être obligatoire s'il n'est pas visible
        if (!$this->isVisible($fieldKey)) {
            return false;
        }
        
        // Vérifier d'abord si c'est un champ personnalisé
        $customField = $this->customFields->firstWhere('key', $fieldKey);
        if ($customField) {
            return $customField->required;
        }
        
        if (!isset($this->config[$fieldKey])) {
            // Valeurs par défaut si non configuré
            $defaults = [
                'type_reservation' => true,
                'nom_groupe' => false,
                'code_groupe' => false,
                'type_piece_identite' => true,
                'numero_piece_identite' => true,
                'nom' => true,
                'prenom' => true,
                'sexe' => true,
                'date_naissance' => true,
                'lieu_naissance' => true,
                'nationalite' => true,
                'adresse' => false,
                'telephone' => true,
                'email' => true,
                'profession' => true,
                'date_arrivee' => true,
                'heure_arrivee' => false,
                'date_depart' => true,
                'nombre_adultes' => true,
                'nombre_enfants' => false,
                'venant_de' => true,
                'type_chambre' => true,
                'room_id' => false,
                'piece_identite_recto' => true,
                'piece_identite_verso' => false,
                'signature' => true,
                'preferences' => false,
            ];
            
            return $defaults[$fieldKey] ?? false;
        }
        
        return $this->config[$fieldKey]['required'] ?? false;
    }
    
    /**
     * Obtenir l'attribut required pour un input
     */
    public function getRequiredAttribute(string $fieldKey): string
    {
        return $this->isRequired($fieldKey) ? 'required' : '';
    }
    
    /**
     * Obtenir la classe CSS pour les labels obligatoires
     */
    public function getRequiredClass(string $fieldKey): string
    {
        return $this->isRequired($fieldKey) ? 'required' : '';
    }
    
    /**
     * Obtenir l'étoile pour les champs obligatoires
     */
    public function getRequiredStar(string $fieldKey): string
    {
        return $this->isRequired($fieldKey) ? '<span class="required">*</span>' : '';
    }
    
    /**
     * Obtenir tous les champs personnalisés actifs
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }
    
    /**
     * Obtenir un champ personnalisé par sa clé
     */
    public function getCustomField(string $fieldKey): ?FormField
    {
        return $this->customFields->firstWhere('key', $fieldKey);
    }

    /**
     * Obtenir les champs personnalisés pour une section donnée
     */
    public function getCustomFieldsBySection(int $section)
    {
        return $this->customFields
            ->where('section', $section)
            ->where('active', true)
            ->sortBy('position')
            ->values();
    }

    /**
     * Formater la valeur d'un champ personnalisé pour l'affichage
     */
    public function formatCustomFieldValue(FormField $field, $value): string
    {
        if (empty($value)) {
            return 'Non renseigné';
        }

        switch ($field->type) {
            case 'checkbox':
                return $value ? 'Oui' : 'Non';
            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y');
                } catch (\Exception $e) {
                    return $value;
                }
            case 'datetime':
                try {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    return $value;
                }
            case 'select':
            case 'radio':
                // Si la valeur correspond à une option, retourner l'option
                if ($field->options && in_array($value, $field->options)) {
                    return $value;
                }
                return $value;
            case 'textarea':
                return nl2br(e($value));
            default:
                return e($value);
        }
    }

    /**
     * Obtenir tous les champs personnalisés avec leurs valeurs depuis les données de réservation
     */
    public function getCustomFieldsWithValues(array $reservationData): array
    {
        $fieldsWithValues = [];
        
        foreach ($this->customFields as $field) {
            if (isset($reservationData[$field->key])) {
                $fieldsWithValues[] = [
                    'field' => $field,
                    'value' => $reservationData[$field->key],
                    'formatted_value' => $this->formatCustomFieldValue($field, $reservationData[$field->key]),
                ];
            }
        }
        
        return $fieldsWithValues;
    }
}




