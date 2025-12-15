<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Afficher le calendrier des réservations
     */
    public function index(Request $request)
    {
        $hotel = Auth::user()->hotel;

        if (!$hotel) {
            abort(404, 'Aucun hôtel assigné');
        }

        // Date de début et fin pour le mois actuel (ou sélectionné)
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer toutes les réservations du mois
        $reservations = Reservation::where('hotel_id', $hotel->id)
            ->where('status', '!=', 'rejected')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('check_in_date', '<=', $startDate)
                          ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->with(['roomType', 'room'])
            ->get();

        // Statistiques
        $stats = [
            'total' => $reservations->count(),
            'available_rooms' => Room::where('hotel_id', $hotel->id)
                ->where('status', 'available')
                ->count(),
            'occupied_rooms' => Room::where('hotel_id', $hotel->id)
                ->where('status', 'occupied')
                ->count(),
            'pending' => $reservations->where('status', 'pending')->count(),
        ];

        return view('hotel.calendar', compact('reservations', 'stats', 'month', 'year'));
    }

    /**
     * API: Récupérer les réservations pour une période donnée
     */
    public function getReservations(Request $request)
    {
        $hotel = Auth::user()->hotel;

        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $reservations = Reservation::where('hotel_id', $hotel->id)
            ->where('status', '!=', 'rejected')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('check_in_date', '<=', $startDate)
                          ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->with(['roomType', 'room'])
            ->get();

        return response()->json($reservations);
    }
}

