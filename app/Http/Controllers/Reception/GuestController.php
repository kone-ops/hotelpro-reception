<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    /**
     * Afficher la liste des clients en séjour
     */
    public function staying(Request $request)
    {
        $user = Auth::user();
        $hotelId = $user->hotel_id;
        
        $query = Reservation::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with(['room', 'roomType', 'checkedInBy']);
        
        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data->nom', 'like', "%{$search}%")
                  ->orWhere('data->prenom', 'like', "%{$search}%")
                  ->orWhere('data->email', 'like', "%{$search}%")
                  ->orWhere('data->telephone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        
        $guests = $query->orderBy('check_in_date', 'asc')
            ->orderBy('checked_in_at', 'desc')
            ->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => Reservation::where('hotel_id', $hotelId)->where('status', 'checked_in')->count(),
            'today_checkin' => Reservation::where('hotel_id', $hotelId)
                ->where('status', 'checked_in')
                ->whereDate('checked_in_at', today())
                ->count(),
            'today_checkout' => Reservation::where('hotel_id', $hotelId)
                ->where('status', 'checked_in')
                ->whereDate('check_out_date', today())
                ->count(),
        ];
        
        // Liste des chambres occupées pour le filtre
        $rooms = Reservation::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereNotNull('room_id')
            ->with('room')
            ->get()
            ->pluck('room')
            ->unique('id')
            ->sortBy('room_number')
            ->values();
        
        return view('reception.guests.staying', compact('guests', 'stats', 'rooms'));
    }
}

