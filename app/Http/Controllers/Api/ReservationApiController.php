<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['hotel', 'identityDocument', 'signature']);
        
        if ($request->has('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $reservations = $query->latest()->get();
        
        return response()->json($reservations);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['hotel', 'identityDocument', 'signature']);
        return response()->json($reservation);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'status' => 'sometimes|in:pending,validated,rejected',
            'notes' => 'sometimes|string|max:1000',
        ]);

        $reservation->update($data);

        return response()->json($reservation);
    }
}













