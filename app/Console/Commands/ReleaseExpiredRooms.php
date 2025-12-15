<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libérer automatiquement les chambres après la date de check-out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des chambres à libérer...');
        
        // Récupérer toutes les réservations validées ou checked_in dont la date de départ est passée
        $expiredReservations = Reservation::whereIn('status', ['validated', 'checked_in'])
            ->whereNotNull('room_id')
            ->whereNotNull('check_out_date')
            ->where('check_out_date', '<', now()->toDateString())
            ->with('room')
            ->get();
        
        $releasedCount = 0;
        
        foreach ($expiredReservations as $reservation) {
            if ($reservation->room && $reservation->room->status === 'occupied') {
                // Libérer la chambre
                $reservation->room->updateStatus('available');
                
                // Mettre à jour le statut de la réservation
                if ($reservation->status === 'checked_in') {
                    // Si le client était check-in, faire le check-out automatique
                    $reservation->update([
                        'status' => 'checked_out',
                        'checked_out_at' => now(),
                    ]);
                } else {
                    // Sinon, marquer comme complétée
                    $reservation->update(['status' => 'completed']);
                }
                
                $releasedCount++;
                
                Log::info('Chambre libérée automatiquement', [
                    'room_id' => $reservation->room->id,
                    'room_number' => $reservation->room->room_number,
                    'reservation_id' => $reservation->id,
                    'check_out_date' => $reservation->check_out_date
                ]);
                
                $this->line("✅ Chambre {$reservation->room->room_number} libérée (Réservation #{$reservation->id})");
            }
        }
        
        if ($releasedCount > 0) {
            $this->info("✅ {$releasedCount} chambre(s) libérée(s) avec succès !");
        } else {
            $this->info('Aucune chambre à libérer.');
        }
        
        return Command::SUCCESS;
    }
}
