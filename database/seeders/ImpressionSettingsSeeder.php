<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Hotel;

class ImpressionSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les hôtels
        $hotels = Hotel::all();
        
        // Paramètres d'impression par défaut
        $defaultSettings = [
            [
                'key' => 'signature_formulaire',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Signature Formulaire Public',
                'description' => 'Exiger une signature numérique dans le formulaire de pré-réservation',
                'is_active' => true
            ],
            [
                'key' => 'signature_fiche_police',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Signature Fiche de Police',
                'description' => 'Afficher la signature du client sur la fiche de police',
                'is_active' => true
            ],
            [
                'key' => 'auto_print_police',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression Automatique Fiche Police',
                'description' => 'Imprimer automatiquement la fiche police après validation de réservation',
                'is_active' => true
            ],
            [
                'key' => 'manual_print_only',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression Manuelle Uniquement',
                'description' => 'Désactiver l\'impression automatique et afficher un bouton manuel',
                'is_active' => true
            ],
        ];
        
        // Créer les paramètres pour chaque hôtel
        foreach ($hotels as $hotel) {
            foreach ($defaultSettings as $setting) {
                Setting::updateOrCreate(
                    [
                        'key' => $setting['key'],
                        'hotel_id' => $hotel->id
                    ],
                    $setting
                );
            }
        }
        
        $this->command->info('✅ Paramètres d\'impression créés pour ' . $hotels->count() . ' hôtel(s)');
    }
}
