<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Printer;
use App\Models\Hotel;

class PrinterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le premier hôtel
        $hotel = Hotel::first();
        
        if (!$hotel) {
            $this->command->warn('Aucun hôtel trouvé. Créez d\'abord un hôtel.');
            return;
        }
        
        // Créer quelques imprimantes de test
        $printers = [
            [
                'name' => 'Imprimante Réception',
                'ip_address' => '192.168.1.100',
                'port' => 9100,
                'type' => 'ticket',
                'is_active' => true,
            ],
            [
                'name' => 'Imprimante Bureau',
                'ip_address' => '192.168.1.101',
                'port' => 9100,
                'type' => 'a4',
                'is_active' => true,
            ],
            [
                'name' => 'Imprimante Cuisine',
                'ip_address' => '192.168.1.102',
                'port' => 9100,
                'type' => 'ticket',
                'is_active' => false, // Hors ligne pour tester
            ],
        ];
        
        foreach ($printers as $printerData) {
            Printer::create([
                'hotel_id' => $hotel->id,
                ...$printerData
            ]);
        }
        
        $this->command->info('Imprimantes de test créées avec succès !');
    }
}