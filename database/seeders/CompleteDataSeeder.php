<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use App\Models\FormField;
use App\Models\PreReservation;
use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDataSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 5 hôtels avec des données réalistes
        $hotels = [
            [
                'name' => 'Hotel Plaza Luxury',
                'address' => '123 Avenue des Champs-Élysées',
                'city' => 'Paris',
                'country' => 'France',
                'primary_color' => '#1a4b8c',
                'secondary_color' => '#e19f32',
            ],
            [
                'name' => 'Grand Hotel Continental',
                'address' => '456 Boulevard Haussmann',
                'city' => 'Lyon',
                'country' => 'France',
                'primary_color' => '#2c3e50',
                'secondary_color' => '#e74c3c',
            ],
            [
                'name' => 'Beach Resort Paradise',
                'address' => '789 Promenade des Anglais',
                'city' => 'Nice',
                'country' => 'France',
                'primary_color' => '#16a085',
                'secondary_color' => '#f39c12',
            ],
            [
                'name' => 'Mountain View Hotel',
                'address' => '321 Rue de la Montagne',
                'city' => 'Grenoble',
                'country' => 'France',
                'primary_color' => '#8e44ad',
                'secondary_color' => '#3498db',
            ],
            [
                'name' => 'City Center Business Hotel',
                'address' => '654 Rue de la République',
                'city' => 'Marseille',
                'country' => 'France',
                'primary_color' => '#c0392b',
                'secondary_color' => '#d35400',
            ],
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            // Créer un admin pour chaque hôtel
            $admin = User::create([
                'name' => 'Admin ' . $hotel->name,
                'email' => 'admin' . $hotel->id . '@' . strtolower(str_replace(' ', '', $hotel->name)) . '.com',
                'password' => Hash::make('password'),
                'hotel_id' => $hotel->id,
            ]);
            $admin->assignRole('hotel-admin');

            // Créer 2 réceptionnistes par hôtel
            for ($i = 1; $i <= 2; $i++) {
                $receptionist = User::create([
                    'name' => 'Réceptionniste ' . $i . ' - ' . $hotel->name,
                    'email' => 'reception' . $i . '.hotel' . $hotel->id . '@example.com',
                    'password' => Hash::make('password'),
                    'hotel_id' => $hotel->id,
                ]);
                $receptionist->assignRole('receptionist');
            }

            // Créer des champs de formulaire pour chaque hôtel
            $formFields = [
                ['key' => 'nom', 'label' => 'Nom complet', 'type' => 'text', 'required' => true, 'position' => 1, 'active' => true],
                ['key' => 'email', 'label' => 'Adresse email', 'type' => 'email', 'required' => true, 'position' => 2, 'active' => true],
                ['key' => 'telephone', 'label' => 'Téléphone', 'type' => 'text', 'required' => true, 'position' => 3, 'active' => true],
                ['key' => 'date_naissance', 'label' => 'Date de naissance', 'type' => 'date', 'required' => false, 'position' => 4, 'active' => true],
                ['key' => 'nationalite', 'label' => 'Nationalité', 'type' => 'text', 'required' => true, 'position' => 5, 'active' => true],
                ['key' => 'date_arrivee', 'label' => 'Date d\'arrivée', 'type' => 'date', 'required' => true, 'position' => 6, 'active' => true],
                ['key' => 'date_depart', 'label' => 'Date de départ', 'type' => 'date', 'required' => true, 'position' => 7, 'active' => true],
                ['key' => 'nombre_personnes', 'label' => 'Nombre de personnes', 'type' => 'number', 'required' => true, 'position' => 8, 'active' => true],
                ['key' => 'type_chambre', 'label' => 'Type de chambre', 'type' => 'select', 'required' => true, 'position' => 9, 'options' => json_encode(['Simple', 'Double', 'Suite', 'Deluxe']), 'active' => true],
                ['key' => 'commentaires', 'label' => 'Commentaires', 'type' => 'textarea', 'required' => false, 'position' => 10, 'active' => true],
            ];

            foreach ($formFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['hotel_id' => $hotel->id]));
            }

            // Créer des pré-réservations pour chaque hôtel
            $statuses = ['pending', 'validated', 'rejected'];
            $prenoms = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Luc', 'Amélie', 'Thomas', 'Julie', 'Nicolas', 'Emma'];
            $noms = ['Dupont', 'Martin', 'Bernard', 'Dubois', 'Laurent', 'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Moreau'];
            
            for ($i = 1; $i <= 15; $i++) {
                $prenom = $prenoms[array_rand($prenoms)];
                $nom = $noms[array_rand($noms)];
                $status = $statuses[array_rand($statuses)];
                
                PreReservation::create([
                    'hotel_id' => $hotel->id,
                    'status' => $status,
                    'data' => [
                        'nom' => $prenom . ' ' . $nom,
                        'email' => strtolower($prenom . '.' . $nom . $i . '@example.com'),
                        'telephone' => '+33 6 ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                        'date_naissance' => date('Y-m-d', strtotime('-' . rand(20, 60) . ' years')),
                        'nationalite' => 'Française',
                        'date_arrivee' => date('Y-m-d', strtotime('+' . rand(1, 30) . ' days')),
                        'date_depart' => date('Y-m-d', strtotime('+' . rand(31, 45) . ' days')),
                        'nombre_personnes' => rand(1, 4),
                        'type_chambre' => ['Simple', 'Double', 'Suite', 'Deluxe'][array_rand(['Simple', 'Double', 'Suite', 'Deluxe'])],
                        'commentaires' => $i % 3 == 0 ? 'Arrivée tardive prévue' : '',
                    ],
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            // Créer 2-3 groupes par hôtel
            for ($i = 1; $i <= rand(2, 3); $i++) {
                Group::create([
                    'hotel_id' => $hotel->id,
                    'name' => 'Groupe ' . ['Entreprise ABC', 'Séminaire XYZ', 'Conférence 2025'][rand(0, 2)],
                    'group_code' => 'GRP' . $hotel->id . '-' . strtoupper(substr(md5(rand()), 0, 6)),
                ]);
            }
        }
    }
}
