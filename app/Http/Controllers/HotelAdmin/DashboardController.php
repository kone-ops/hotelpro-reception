<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
	public function index()
	{
		$hotel = Auth::user()->hotel;
		
		if (!$hotel) {
			abort(404, 'Aucun hôtel assigné');
		}
		
		// Optimisation : Utiliser une seule requête pour obtenir toutes les statistiques de réservations
		$reservationStats = $hotel->reservations()
			->selectRaw('
				COUNT(*) as total,
				SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN status = "validated" THEN 1 ELSE 0 END) as validated,
				SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
				SUM(CASE WHEN status = "checked_in" THEN 1 ELSE 0 END) as checked_in,
				SUM(CASE WHEN json_extract(data, "$.type_reservation") = "Groupe" THEN 1 ELSE 0 END) as groups
			')
			->first();
		
		// Optimisation : Utiliser une seule requête pour obtenir toutes les statistiques de chambres
		$roomStats = $hotel->rooms()
			->selectRaw('
				SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available,
				SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied
			')
			->first();
		
		$stats = [
			'total' => $reservationStats->total ?? 0,
			'pending' => $reservationStats->pending ?? 0,
			'validated' => $reservationStats->validated ?? 0,
			'rejected' => $reservationStats->rejected ?? 0,
			'checked_in' => $reservationStats->checked_in ?? 0,
			'groups' => $reservationStats->groups ?? 0,
			'rooms_available' => $roomStats->available ?? 0,
			'rooms_occupied' => $roomStats->occupied ?? 0,
		];
		
		$recentReservations = $hotel->reservations()->with(['room', 'roomType'])->latest()->limit(10)->get();
		
		return view('hotel.dashboard', compact('stats', 'recentReservations', 'hotel'));
	}
}


