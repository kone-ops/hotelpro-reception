<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoomController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des chambres
     */
    public function index(Request $request)
    {
        $hotel = Auth::user()->hotel;
        
        $query = $hotel->rooms()->with('roomType');
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }
        
        $rooms = $query->orderBy('room_number')->paginate(20);
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();
        
        // Statistiques
        $stats = [
            'total' => $hotel->rooms()->count(),
            'available' => $hotel->rooms()->where('status', 'available')->count(),
            'occupied' => $hotel->rooms()->where('status', 'occupied')->count(),
            'maintenance' => $hotel->rooms()->where('status', 'maintenance')->count(),
            'reserved' => $hotel->rooms()->where('status', 'reserved')->count(),
        ];
        
        return view('hotel.rooms.index', compact('hotel', 'rooms', 'roomTypes', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $hotel = Auth::user()->hotel;
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();
        
        return view('hotel.rooms.create', compact('hotel', 'roomTypes'));
    }

    /**
     * Enregistrer une nouvelle chambre
     */
    public function store(Request $request)
    {
        $hotel = Auth::user()->hotel;
        
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'floor' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Vérifier que le type de chambre appartient à l'hôtel
        $roomType = RoomType::where('id', $validated['room_type_id'])
            ->where('hotel_id', $hotel->id)
            ->firstOrFail();
        
        $validated['hotel_id'] = $hotel->id;
        
        Room::create($validated);
        
        return redirect()
            ->route('hotel.rooms.index')
            ->with('success', 'Chambre créée avec succès !');
    }

    /**
     * Afficher les détails d'une chambre
     */
    public function show(Room $room)
    {
        $this->authorize('view', $room);
        
        $room->load('roomType');
        
        return view('hotel.rooms.show', compact('room'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Room $room)
    {
        $this->authorize('update', $room);
        
        $hotel = Auth::user()->hotel;
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();
        
        return view('hotel.rooms.edit', compact('room', 'roomTypes'));
    }

    /**
     * Mettre à jour une chambre
     */
    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);
        
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:50|unique:rooms,room_number,' . $room->id,
            'floor' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Vérifier que le type de chambre appartient à l'hôtel
        $hotel = Auth::user()->hotel;
        $roomType = RoomType::where('id', $validated['room_type_id'])
            ->where('hotel_id', $hotel->id)
            ->firstOrFail();
        
        $room->update($validated);
        
        return redirect()
            ->route('hotel.rooms.index')
            ->with('success', 'Chambre mise à jour avec succès !');
    }

    /**
     * Supprimer une chambre
     */
    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);
        
        $room->delete();
        
        return redirect()
            ->route('hotel.rooms.index')
            ->with('success', 'Chambre supprimée avec succès !');
    }

    /**
     * Changer rapidement le statut d'une chambre (pour la réception et admin-hotel)
     */
    public function updateStatus(Request $request, Room $room)
    {
        try {
            // Nettoyer le buffer pour éviter les BOM
            if (ob_get_length()) ob_clean();
            
            // Vérifier que la chambre appartient à l'hôtel de l'utilisateur
            $hotel = Auth::user()->hotel;
            if ($room->hotel_id !== $hotel->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette chambre n\'appartient pas à votre hôtel.'
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Vérifier les permissions (hotel-admin ou receptionist)
            $user = Auth::user();
            if (!$user->hasRole('hotel-admin') && !$user->hasRole('receptionist') && !$user->hasRole('super-admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de modifier le statut des chambres.'
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            
            $validated = $request->validate([
                'status' => 'required|in:available,occupied,maintenance,reserved'
            ]);
            
            $room->update($validated);
            
            $statusLabels = [
                'available' => 'Disponible',
                'occupied' => 'Occupée',
                'maintenance' => 'En maintenance',
                'reserved' => 'Réservée'
            ];
            
            return response()->json([
                'success' => true,
                'message' => "✅ Chambre {$room->room_number} marquée comme : {$statusLabels[$validated['status']]}",
                'status' => $validated['status']
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => false,
                'message' => 'Statut invalide. Veuillez sélectionner un statut valide.',
                'errors' => $e->errors()
            ], 422, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            if (ob_get_length()) ob_clean();
            \Log::error('Erreur updateStatus RoomController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'room_id' => $room->id ?? null,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Création en lot de chambres
     */
    public function bulkCreate()
    {
        $hotel = Auth::user()->hotel;
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();
        
        return view('hotel.rooms.bulk-create', compact('hotel', 'roomTypes'));
    }

    /**
     * Enregistrer plusieurs chambres en une fois
     */
    public function bulkStore(Request $request)
    {
        $hotel = Auth::user()->hotel;
        
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'quantity' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10',
            'start_number' => 'required|integer|min:1',
            'floor' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,maintenance,reserved',
        ]);
        
        // Vérifier que le type de chambre appartient à l'hôtel
        $roomType = RoomType::where('id', $validated['room_type_id'])
            ->where('hotel_id', $hotel->id)
            ->firstOrFail();
        
        $created = 0;
        $errors = [];
        
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $roomNumber = $validated['prefix'] . ($validated['start_number'] + $i);
            
            // Vérifier si le numéro existe déjà
            if (Room::where('room_number', $roomNumber)->exists()) {
                $errors[] = "Chambre {$roomNumber} existe déjà";
                continue;
            }
            
            Room::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $validated['room_type_id'],
                'room_number' => $roomNumber,
                'floor' => $validated['floor'],
                'status' => $validated['status'],
            ]);
            
            $created++;
        }
        
        $message = "{$created} chambre(s) créée(s) avec succès !";
        
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " erreur(s) : " . implode(', ', array_slice($errors, 0, 3));
        }
        
        return redirect()
            ->route('hotel.rooms.index')
            ->with('success', $message);
    }
}

