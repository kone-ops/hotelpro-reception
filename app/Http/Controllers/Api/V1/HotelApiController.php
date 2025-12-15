<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * API V1 pour intégration avec logiciels de gestion hôtelière
 * 
 * Endpoints disponibles :
 * - GET /api/v1/hotels - Liste des hôtels
 * - GET /api/v1/hotels/{id} - Détails d'un hôtel
 * - GET /api/v1/hotels/{id}/reservations - Réservations d'un hôtel
 * - POST /api/v1/hotels/{id}/reservations - Créer une réservation
 * - GET /api/v1/hotels/{id}/rooms - Chambres d'un hôtel
 * - GET /api/v1/hotels/{id}/availability - Disponibilités
 */
class HotelApiController extends Controller
{
    /**
     * Liste de tous les hôtels
     * GET /api/v1/hotels
     */
    public function index(): JsonResponse
    {
        $hotels = Hotel::select('id', 'name', 'city', 'email', 'phone', 'address')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $hotels,
            'count' => $hotels->count()
        ]);
    }

    /**
     * Détails d'un hôtel
     * GET /api/v1/hotels/{id}
     */
    public function show(int $id): JsonResponse
    {
        $hotel = Hotel::with(['roomTypes', 'rooms'])
            ->find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $hotel
        ]);
    }

    /**
     * Réservations d'un hôtel
     * GET /api/v1/hotels/{id}/reservations
     */
    public function reservations(Request $request, int $id): JsonResponse
    {
        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        $query = $hotel->reservations()
            ->with(['room', 'roomType', 'identityDocument', 'signature']);
        
        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }
        
        $perPage = $request->input('per_page', 15);
        $reservations = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $reservations->items(),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total()
            ]
        ]);
    }

    /**
     * Créer une réservation via API
     * POST /api/v1/hotels/{id}/reservations
     */
    public function createReservation(Request $request, int $id): JsonResponse
    {
        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'client_name' => 'required|string',
            'client_email' => 'required|email',
            'client_phone' => 'required|string',
            'guests_count' => 'required|integer|min:1',
            'data' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $reservation = Reservation::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $request->room_type_id,
            'room_id' => $request->room_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'status' => 'pending',
            'data' => array_merge($request->data ?? [], [
                'nom' => $request->client_name,
                'email' => $request->client_email,
                'telephone' => $request->client_phone,
                'nombre_adultes' => $request->guests_count,
                'api_created' => true,
            ])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès',
            'data' => $reservation->load(['room', 'roomType'])
        ], 201);
    }

    /**
     * Chambres d'un hôtel
     * GET /api/v1/hotels/{id}/rooms
     */
    public function rooms(Request $request, int $id): JsonResponse
    {
        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        $query = $hotel->rooms()->with('roomType');
        
        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        $rooms = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $rooms,
            'count' => $rooms->count()
        ]);
    }

    /**
     * Vérifier la disponibilité des chambres
     * GET /api/v1/hotels/{id}/availability
     */
    public function availability(Request $request, int $id): JsonResponse
    {
        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $checkIn = $request->check_in_date;
        $checkOut = $request->check_out_date;
        
        // Obtenir les chambres occupées pendant cette période
        $occupiedRoomIds = Reservation::where('hotel_id', $hotel->id)
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                          ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->pluck('room_id')
            ->toArray();
        
        // Obtenir les chambres disponibles
        $query = $hotel->rooms()
            ->with('roomType')
            ->where('status', 'available')
            ->whereNotIn('id', $occupiedRoomIds);
        
        if ($request->has('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        $availableRooms = $query->get();
        
        return response()->json([
            'success' => true,
            'period' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut
            ],
            'available_rooms' => $availableRooms,
            'count' => $availableRooms->count()
        ]);
    }

    /**
     * Types de chambres d'un hôtel
     * GET /api/v1/hotels/{id}/room-types
     */
    public function roomTypes(int $id): JsonResponse
    {
        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hôtel non trouvé'
            ], 404);
        }
        
        $roomTypes = $hotel->roomTypes()
            ->where('is_available', true)
            ->withCount('rooms')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $roomTypes,
            'count' => $roomTypes->count()
        ]);
    }
}

