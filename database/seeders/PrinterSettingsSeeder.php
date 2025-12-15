<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrinterSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'signature_obligatoire',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Signature obligatoire',
                'description' => 'Exiger une signature numérique lors de la réservation',
                'is_active' => true,
            ],
            [
                'key' => 'auto_print_police',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression automatique fiche police',
                'description' => 'Imprimer automatiquement la fiche police après validation',
                'is_active' => true,
            ],
            [
                'key' => 'manual_print_only',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'impression',
                'label' => 'Impression manuelle uniquement',
                'description' => 'Désactiver l\'impression automatique et afficher un bouton manuel',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
