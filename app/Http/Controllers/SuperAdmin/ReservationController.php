<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Afficher la liste de toutes les réservations
     */
    public function index(Request $request)
    {
        // Pour le super admin, on doit désactiver le scope global
        $query = Reservation::withoutGlobalScopes()->with([
            'hotel:id,name,city,logo',
            'room:id,room_number',
            'roomType:id,name',
        ]);
        
        // Filtre par hôtel
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par période (date de début)
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        // Filtre par période (date de fin)
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        
        // Filtre par type de réservation
        if ($request->filled('type')) {
            $query->where('data->type_reservation', $request->type);
        }
        
        // Recherche globale
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data->nom', 'like', "%{$search}%")
                  ->orWhere('data->email', 'like', "%{$search}%")
                  ->orWhere('data->prenom', 'like', "%{$search}%")
                  ->orWhere('data->telephone', 'like', "%{$search}%");
            });
        }
        
        $reservations = $query->latest()->paginate(20);
        
        // Liste des hôtels pour le filtre
        $hotels = Hotel::select('id', 'name', 'city')->orderBy('name')->get();
        
        // Statistiques globales
        $stats = [
            'total' => Reservation::withoutGlobalScopes()->count(),
            'pending' => Reservation::withoutGlobalScopes()->where('status', 'pending')->count(),
            'validated' => Reservation::withoutGlobalScopes()->where('status', 'validated')->count(),
            'checked_in' => Reservation::withoutGlobalScopes()->where('status', 'checked_in')->count(),
            'checked_out' => Reservation::withoutGlobalScopes()->where('status', 'checked_out')->count(),
            'rejected' => Reservation::withoutGlobalScopes()->where('status', 'rejected')->count(),
        ];
        
        return view('super.reservations.index', compact('reservations', 'hotels', 'stats'));
    }

    /**
     * Afficher une réservation
     */
    public function show($id)
    {
        $reservation = Reservation::withoutGlobalScopes()
            ->with([
                'hotel',
                'identityDocument',
                'signature',
                'room',
                'roomType'
            ])
            ->findOrFail($id);
        
        $formConfig = new \App\Services\FormConfigService($reservation->hotel);
        
        return view('super.reservations.show', compact('reservation', 'formConfig'));
    }
    
    /**
     * Supprimer plusieurs réservations
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'required|exists:reservations,id',
        ]);
        
        $reservationIds = $request->reservation_ids;
        $count = Reservation::withoutGlobalScopes()
            ->whereIn('id', $reservationIds)
            ->delete();
        
        return redirect()->route('super.reservations.index')
            ->with('success', $count . ' réservation(s) supprimée(s) avec succès');
    }
}
