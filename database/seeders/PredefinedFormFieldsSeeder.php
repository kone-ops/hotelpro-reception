<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class PredefinedFormFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les hôtels
        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            // Vérifier si l'hôtel a déjà des champs
            if ($hotel->formFields()->count() > 0) {
                $this->command->info("L'hôtel {$hotel->name} a déjà des champs. Ignoré.");
                continue;
            }

            $this->command->info("Création des champs pour {$hotel->name}...");
            
            $fields = $this->getPredefinedFields($hotel->id);
            
            foreach ($fields as $field) {
                FormField::create($field);
            }
            
            $this->command->info("✓ {count($fields)} champs créés pour {$hotel->name}");
        }
    }

    /**
     * Retourne les champs prédéfinis selon le cahier de charge
     */
    private function getPredefinedFields($hotelId): array
    {
        return [
            // ========== SECTION 1: TYPE DE RÉSERVATION ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Type de réservation',
                'key' => 'type_reservation',
                'type' => 'select',
                'required' => true,
                'position' => 1,
                'options' => json_encode(['Individuel', 'Groupe'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Code groupe',
                'key' => 'code_groupe',
                'type' => 'text',
                'required' => false,
                'position' => 2,
                'options' => json_encode(['conditional' => 'type_reservation:Groupe'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nom du groupe',
                'key' => 'nom_groupe',
                'type' => 'text',
                'required' => false,
                'position' => 3,
                'options' => json_encode(['conditional' => 'type_reservation:Groupe'])
            ],

            // ========== SECTION 2: INFORMATIONS PERSONNELLES ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Type de pièce d\'identité',
                'key' => 'type_piece_identite',
                'type' => 'select',
                'required' => true,
                'position' => 10,
                'options' => json_encode(['CNI', 'Passeport', 'Permis de conduire', 'Autre'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Pièce d\'identité (Recto)',
                'key' => 'piece_identite_recto',
                'type' => 'file',
                'required' => true,
                'position' => 11,
                'options' => json_encode(['accept' => 'image/*,.pdf', 'max_size' => '5MB'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Pièce d\'identité (Verso)',
                'key' => 'piece_identite_verso',
                'type' => 'file',
                'required' => false,
                'position' => 12,
                'options' => json_encode(['accept' => 'image/*,.pdf', 'max_size' => '5MB'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nom de famille',
                'key' => 'nom',
                'type' => 'text',
                'required' => true,
                'position' => 13,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Prénom(s)',
                'key' => 'prenom',
                'type' => 'text',
                'required' => true,
                'position' => 14,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Sexe',
                'key' => 'sexe',
                'type' => 'select',
                'required' => true,
                'position' => 15,
                'options' => json_encode(['Masculin', 'Féminin', 'Autre'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Date de naissance',
                'key' => 'date_naissance',
                'type' => 'date',
                'required' => true,
                'position' => 16,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nationalité',
                'key' => 'nationalite',
                'type' => 'text',
                'required' => true,
                'position' => 17,
                'options' => null
            ],

            // ========== SECTION 3: COORDONNÉES ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Adresse complète',
                'key' => 'adresse',
                'type' => 'textarea',
                'required' => true,
                'position' => 20,
                'options' => json_encode(['rows' => 3, 'placeholder' => 'Rue, Ville, Pays'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Téléphone',
                'key' => 'telephone',
                'type' => 'tel',
                'required' => true,
                'position' => 21,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Email',
                'key' => 'email',
                'type' => 'email',
                'required' => true,
                'position' => 22,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Personne à contacter en cas d\'urgence',
                'key' => 'contact_urgence_nom',
                'type' => 'text',
                'required' => false,
                'position' => 23,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Téléphone de la personne de contact',
                'key' => 'contact_urgence_telephone',
                'type' => 'tel',
                'required' => false,
                'position' => 24,
                'options' => null
            ],

            // ========== SECTION 4: INFORMATIONS SUR LE SÉJOUR ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Date d\'arrivée prévue',
                'key' => 'date_arrivee',
                'type' => 'date',
                'required' => true,
                'position' => 30,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Viens de',
                'key' => 'provenance',
                'type' => 'text',
                'required' => false,
                'position' => 31,
                'options' => json_encode(['placeholder' => 'Ville ou pays de provenance'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Date de départ prévue',
                'key' => 'date_depart',
                'type' => 'date',
                'required' => true,
                'position' => 32,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nombre de nuits',
                'key' => 'nombre_nuits',
                'type' => 'number',
                'required' => false,
                'position' => 33,
                'options' => json_encode(['readonly' => true, 'calculated' => true])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nombre d\'adultes',
                'key' => 'nombre_adultes',
                'type' => 'number',
                'required' => true,
                'position' => 34,
                'options' => json_encode(['min' => 1, 'default' => 1])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Nombre d\'enfants',
                'key' => 'nombre_enfants',
                'type' => 'number',
                'required' => false,
                'position' => 35,
                'options' => json_encode(['min' => 0, 'default' => 0])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Type de chambre souhaitée',
                'key' => 'type_chambre',
                'type' => 'select',
                'required' => true,
                'position' => 36,
                'options' => json_encode(['Single', 'Double', 'Suite', 'Triple', 'Familiale'])
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'Préférences particulières',
                'key' => 'preferences',
                'type' => 'textarea',
                'required' => false,
                'position' => 37,
                'options' => json_encode([
                    'rows' => 3,
                    'placeholder' => 'Lit supplémentaire, étage préféré, vue, allergies, etc.'
                ])
            ],

            // ========== SECTION 5: PRÉ-VALIDATION ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Je confirme l\'exactitude des informations',
                'key' => 'confirmation_exactitude',
                'type' => 'checkbox',
                'required' => true,
                'position' => 40,
                'options' => null
            ],
            [
                'hotel_id' => $hotelId,
                'label' => 'J\'accepte les conditions de réservation et la politique de confidentialité',
                'key' => 'acceptation_conditions',
                'type' => 'checkbox',
                'required' => true,
                'position' => 41,
                'options' => json_encode(['link' => '/conditions-generales'])
            ],

            // ========== SECTION 6: SIGNATURE ==========
            [
                'hotel_id' => $hotelId,
                'label' => 'Signature électronique',
                'key' => 'signature',
                'type' => 'signature',
                'required' => true,
                'position' => 50,
                'options' => json_encode(['canvas_width' => 500, 'canvas_height' => 200])
            ],
        ];
    }
}


