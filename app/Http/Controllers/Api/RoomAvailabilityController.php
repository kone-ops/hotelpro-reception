<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomAvailabilityController extends Controller
{
    /**
     * Obtenir les types de chambres disponibles pour un hôtel
     */
    public function getRoomTypes(Hotel $hotel)
    {
        $roomTypes = RoomType::where('hotel_id', $hotel->id)
            ->withCount(['rooms' => function($query) {
                $query->where('status', 'available');
            }])
            ->having('rooms_count', '>', 0)
            ->get()
            ->map(function($roomType) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'available_rooms' => $roomType->rooms_count,
                    'price' => $roomType->price ?? null,
                    'description' => $roomType->description ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'room_types' => $roomTypes
        ]);
    }

    /**
     * Obtenir les chambres disponibles pour un type de chambre et une période
     */
    public function getAvailableRooms(Request $request, Hotel $hotel)
    {
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $roomTypeId = $request->room_type_id;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        // Obtenir les chambres disponibles
        $availableRooms = Room::getAvailableRooms($hotel->id, $roomTypeId, $checkIn, $checkOut);

        if ($availableRooms->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune chambre disponible pour cette période',
                'rooms' => []
            ]);
        }

        $rooms = $availableRooms->map(function($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'status' => $room->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => count($rooms) . ' chambre(s) disponible(s)',
            'rooms' => $rooms
        ]);
    }

    /**
     * Vérifier la disponibilité d'une chambre spécifique
     */
    public function checkRoomAvailability(Request $request, Hotel $hotel, Room $room)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $isAvailable = $room->isAvailableForPeriod(
            $request->check_in,
            $request->check_out
        );

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'room' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'status' => $room->status,
            ],
            'message' => $isAvailable 
                ? 'Chambre disponible' 
                : 'Chambre non disponible pour cette période'
        ]);
    }
}
