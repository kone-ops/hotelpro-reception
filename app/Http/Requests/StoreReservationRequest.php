<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Services\FormConfigService;
use App\Services\ClientService;
use Illuminate\Validation\ValidationException;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Formulaire public
    }

    /**
     * Valider les doublons après la validation standard
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $hotel = $this->route('hotel');
            
            if (!$hotel) {
                return;
            }

            // Désactivation du blocage sur doublons : le ClientService fusionnera
            // automatiquement avec le client existant (même email/téléphone/pièce).
            // Cela évite d'empêcher un client connu de refaire une réservation.
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Récupérer l'hôtel depuis la route
        $hotel = $this->route('hotel');
        
        // Créer le service de configuration
        $formConfig = $hotel ? new FormConfigService($hotel) : null;
        
        // Récupérer les types de chambre disponibles pour cet hôtel
        $validRoomTypes = [];
        if ($hotel) {
            $validRoomTypes = $hotel->roomTypes()
                ->where('is_available', true)
                ->pluck('name')
                ->toArray();
        }
        
        // Si aucun type de chambre n'est configuré, utiliser les valeurs par défaut
        if (empty($validRoomTypes)) {
            $validRoomTypes = ['Single', 'Double', 'Suite', 'Triple', 'Familiale'];
        }
        
        // Fonction helper pour générer les règles selon la configuration
        $makeRule = function($fieldKey, $baseRule) use ($formConfig) {
            if (!$formConfig || !$formConfig->isVisible($fieldKey)) {
                return 'nullable'; // Champ masqué = toujours nullable
            }
            
            if ($formConfig->isRequired($fieldKey)) {
                return $baseRule;
            }
            
            // Remplacer 'required' par 'nullable' si le champ n'est pas obligatoire
            return str_replace('required', 'nullable', $baseRule);
        };
        
        $rules = [
            // Type de réservation
            'type_reservation' => $makeRule('type_reservation', 'required|in:Individuel,Groupe'),
            
            // Informations personnelles
            'type_piece_identite' => $makeRule('type_piece_identite', 'required|in:CNI,Passeport,Permis,Autre'),
            'numero_piece_identite' => $makeRule('numero_piece_identite', 'required|string|max:100'),
            'nom' => $makeRule('nom', 'required|string|max:255'),
            'prenom' => $makeRule('prenom', 'required|string|max:255'),
            'sexe' => $makeRule('sexe', 'required|in:Masculin,Féminin'),
            'date_naissance' => $makeRule('date_naissance', 'required|date|before:18 years ago'),
            'lieu_naissance' => $makeRule('lieu_naissance', 'required|string|max:255'),
            'nationalite' => $makeRule('nationalite', 'required|string|max:255'),
            
            // Coordonnées
            'adresse' => $makeRule('adresse', 'nullable|string|max:500'),
            'telephone' => $makeRule('telephone', 'required|string|max:20'),
            'email' => $makeRule('email', 'required|email|max:255'),
            'profession' => $makeRule('profession', 'required|string|max:255'),
            
            // Séjour
            'venant_de' => $makeRule('venant_de', 'required|string|max:255'),
            'date_arrivee' => $makeRule('date_arrivee', 'required|date|after_or_equal:today'),
            'heure_arrivee' => $makeRule('heure_arrivee', 'nullable|date_format:H:i'),
            'date_depart' => $makeRule('date_depart', 'required|date|after:date_arrivee'),
            'nombre_nuits' => 'nullable|integer|min:1',
            'nombre_adultes' => $makeRule('nombre_adultes', 'required|integer|min:1|max:20'),
            'nombre_enfants' => $makeRule('nombre_enfants', 'nullable|integer|min:0|max:20'),
            'type_chambre' => $makeRule('type_chambre', 'nullable|in:' . implode(',', $validRoomTypes)),
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_id' => $makeRule('room_id', 'nullable|exists:rooms,id'),
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'preferences' => $makeRule('preferences', 'nullable|string|max:1000'),
            
            // Validation et acceptation (toujours obligatoires)
            'confirmation_exactitude' => 'required|accepted',
            'acceptation_conditions' => 'required|accepted',
            
            // Signature
            'signature' => $makeRule('signature', 'nullable|string'),
            
            // Documents d'identité (jpeg, jpg, png, webp, pdf - max 5 Mo)
            // Exigés uniquement si le champ est visible ET requis (comme les autres champs via la config)
            'piece_identite_recto' => ($formConfig && $formConfig->isVisible('piece_identite_recto') && $formConfig->isRequired('piece_identite_recto'))
                ? 'required_without:photo_recto|nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120'
                : 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'piece_identite_verso' => ($formConfig && $formConfig->isVisible('piece_identite_verso') && $formConfig->isRequired('piece_identite_verso'))
                ? 'required_without:photo_verso|nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120'
                : 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'photo_recto' => ($formConfig && $formConfig->isVisible('piece_identite_recto') && $formConfig->isRequired('piece_identite_recto'))
                ? 'required_without:piece_identite_recto|nullable|string'
                : 'nullable|string',
            'photo_verso' => ($formConfig && $formConfig->isVisible('piece_identite_verso') && $formConfig->isRequired('piece_identite_verso'))
                ? 'required_without:piece_identite_verso|nullable|string'
                : 'nullable|string',
            
            // Informations supplémentaires sur le document
            'document_number' => 'nullable|string|max:255',
            'document_delivery_date' => 'nullable|date',
            'document_delivery_place' => 'nullable|string|max:255',
        ];

        // Règles conditionnelles pour les réservations de groupe
        // Les champs groupe ne sont validés que si le type est "Groupe" ET que les champs sont visibles
        if ($this->input('type_reservation') === 'Groupe') {
            // Utiliser makeRule pour respecter la configuration de visibilité et obligation
            $rules['nom_groupe'] = $makeRule('nom_groupe', 'required|string|max:255');
            $rules['code_groupe'] = $makeRule('code_groupe', 'required|string|max:100');
        } else {
            // Si ce n'est pas une réservation de groupe, les champs sont toujours nullable
            $rules['nom_groupe'] = 'nullable|string|max:255';
            $rules['code_groupe'] = 'nullable|string|max:100';
        }

        // Ajouter les règles de validation pour les champs personnalisés
        if ($formConfig && $hotel) {
            $customFields = $formConfig->getCustomFields();
            foreach ($customFields as $field) {
                if (!$field->active) {
                    // Champ inactif = toujours nullable
                    $rules[$field->key] = 'nullable';
                    continue;
                }
                
                // Construire la règle de base selon le type
                $baseRule = [];
                
                if ($field->required) {
                    $baseRule[] = 'required';
                } else {
                    $baseRule[] = 'nullable';
                }
                
                switch ($field->type) {
                    case 'email':
                        $baseRule[] = 'email';
                        $baseRule[] = 'max:255';
                        break;
                    case 'tel':
                        $baseRule[] = 'string';
                        $baseRule[] = 'max:20';
                        break;
                    case 'number':
                        $baseRule[] = 'numeric';
                        break;
                    case 'date':
                        $baseRule[] = 'date';
                        break;
                    case 'textarea':
                        $baseRule[] = 'string';
                        $baseRule[] = 'max:2000';
                        break;
                    case 'select':
                    case 'radio':
                        // Vérifier si options est un tableau, sinon le décoder depuis JSON
                        $options = [];
                        if (is_array($field->options)) {
                            $options = $field->options;
                        } elseif (is_string($field->options) && !empty($field->options)) {
                            $decoded = json_decode($field->options, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $options = $decoded;
                            }
                        }
                        
                        if (!empty($options) && is_array($options)) {
                            $baseRule[] = 'in:' . implode(',', array_map(function($opt) {
                                return str_replace(',', '\\,', $opt); // Échapper les virgules dans les options
                            }, $options));
                        } else {
                            $baseRule[] = 'string';
                        }
                        break;
                    case 'checkbox':
                        $baseRule[] = 'boolean';
                        break;
                    default: // text
                        $baseRule[] = 'string';
                        $baseRule[] = 'max:255';
                        break;
                }
                
                $rules[$field->key] = implode('|', $baseRule);
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_reservation.required' => 'Veuillez sélectionner un type de réservation.',
            'nom.required' => 'Le nom de famille est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'sexe.required' => 'Veuillez sélectionner votre sexe.',
            'sexe.in' => 'Veuillez sélectionner un sexe valide.',
            'date_naissance.before' => 'Vous devez avoir au moins 18 ans pour effectuer une réservation.',
            'numero_piece_identite.required' => 'Le numéro de pièce d\'identité est obligatoire.',
            'venant_de.required' => 'Veuillez indiquer votre provenance.',
            'date_arrivee.after_or_equal' => 'La date d\'arrivée ne peut pas être dans le passé.',
            'date_depart.after' => 'La date de départ doit être après la date d\'arrivée.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'profession.required' => 'La profession est obligatoire.',
            'confirmation_exactitude.accepted' => 'Vous devez confirmer l\'exactitude des informations.',
            'acceptation_conditions.accepted' => 'Vous devez accepter les conditions de réservation.',
            'signature.string' => 'La signature doit être une chaîne de caractères valide.',
            'nom_groupe.required' => 'Le nom du groupe est obligatoire pour une réservation de groupe.',
            'code_groupe.required' => 'Le code du groupe est obligatoire pour une réservation de groupe.',
            'piece_identite_recto.required_without' => 'Vous devez fournir une pièce d\'identité (recto) en téléchargeant un fichier ou en prenant une photo.',
            'photo_recto.required_without' => 'Vous devez fournir une pièce d\'identité (recto) en téléchargeant un fichier ou en prenant une photo.',
            'piece_identite_verso.required_without' => 'Vous devez fournir une pièce d\'identité (verso) en téléchargeant un fichier ou en prenant une photo.',
            'photo_verso.required_without' => 'Vous devez fournir une pièce d\'identité (verso) en téléchargeant un fichier ou en prenant une photo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type_reservation' => 'type de réservation',
            'type_piece_identite' => 'type de pièce d\'identité',
            'nom' => 'nom de famille',
            'prenom' => 'prénom',
            'sexe' => 'sexe',
            'date_naissance' => 'date de naissance',
            'lieu_naissance' => 'lieu de naissance',
            'nationalite' => 'nationalité',
            'adresse' => 'adresse',
            'telephone' => 'téléphone',
            'email' => 'email',
            'profession' => 'profession',
            'venant_de' => 'provenance',
            'date_arrivee' => 'date d\'arrivée',
            'heure_arrivee' => 'heure d\'arrivée',
            'date_depart' => 'date de départ',
            'nombre_nuits' => 'nombre de nuits',
            'nombre_adultes' => 'nombre d\'adultes',
            'nombre_enfants' => 'nombre d\'enfants',
            'type_chambre' => 'type de chambre',
            'preferences' => 'préférences',
            'nom_groupe' => 'nom du groupe',
            'code_groupe' => 'code du groupe',
        ];
    }
}
