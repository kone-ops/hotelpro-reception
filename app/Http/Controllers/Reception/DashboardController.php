<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard de la réception
     */
    public function index()
    {
        $user = Auth::user();
        $hotelId = $user->hotel_id;
        
        // Utiliser Reservation
        if (true) {
            // Après migration
            $arriveesAujourdhui = Reservation::where('hotel_id', $hotelId)
                ->whereDate('check_in_date', today())
                ->with(['room', 'roomType'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $departsAujourdhui = Reservation::where('hotel_id', $hotelId)
                ->whereDate('check_out_date', today())
                ->whereIn('status', ['checked_in'])
                ->with(['room', 'roomType'])
                ->get();
            
            $nouvellesDemandes = Reservation::where('hotel_id', $hotelId)
                ->where('status', 'pending')
                ->with(['roomType'])
                ->latest()
                ->get();
        } else {
            // Avant migration (défaut)
            $arriveesAujourdhui = Reservation::where('hotel_id', $hotelId)
                ->whereDate('check_in_date', today())
                ->with(['room', 'roomType'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $departsAujourdhui = Reservation::where('hotel_id', $hotelId)
                ->whereDate('check_out_date', today())
                ->whereIn('status', ['checked_in'])
                ->with(['room', 'roomType'])
                ->get();
            
            $nouvellesDemandes = Reservation::where('hotel_id', $hotelId)
                ->where('status', 'pending')
                ->with(['roomType'])
                ->latest()
                ->get();
        }
        
        // Optimisation : Calculer les statistiques depuis les collections déjà chargées
        // (évite des requêtes supplémentaires)
        $stats = [
            'arrivees_aujourd_hui' => $arriveesAujourdhui->count(),
            'departs_aujourd_hui' => $departsAujourdhui->count(),
            'chambres_occupees' => Room::where('hotel_id', $hotelId)->where('status', 'occupied')->count(),
            'chambres_total' => Room::where('hotel_id', $hotelId)->count(),
            'en_attente' => $nouvellesDemandes->count(),
        ];
        
        return view('reception.dashboard', compact(
            'arriveesAujourdhui',
            'departsAujourdhui',
            'nouvellesDemandes',
            'stats'
        ));
    }
}
