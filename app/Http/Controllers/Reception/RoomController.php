<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Interface rapide de gestion des statuts de chambres (pour la réception)
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
        
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }
        
        $rooms = $query->orderBy('room_number')->get();
        $roomTypes = $hotel->roomTypes()->orderBy('name')->get();
        
        // Obtenir les étages uniques
        $floors = $hotel->rooms()
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->sort()
            ->values();
        
        // Statistiques
        $stats = [
            'total' => $hotel->rooms()->count(),
            'available' => $hotel->rooms()->where('status', 'available')->count(),
            'occupied' => $hotel->rooms()->where('status', 'occupied')->count(),
            'maintenance' => $hotel->rooms()->where('status', 'maintenance')->count(),
            'reserved' => $hotel->rooms()->where('status', 'reserved')->count(),
        ];
        
        return view('reception.rooms.index', compact('hotel', 'rooms', 'roomTypes', 'floors', 'stats'));
    }

    /**
     * Changer rapidement le statut d'une chambre
     */
    public function updateStatus(Request $request, Room $room)
    {
        try {
            // Nettoyer le buffer pour éviter les BOM
            if (ob_get_length()) ob_clean();
            
            // Vérifier que la chambre appartient à l'hôtel de l'utilisateur
            $user = Auth::user();
            if ($room->hotel_id !== $user->hotel_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette chambre n\'appartient pas à votre hôtel.'
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Vérifier les permissions (receptionist ou hotel-admin)
            if (!$user->hasRole('receptionist') && !$user->hasRole('hotel-admin') && !$user->hasRole('super-admin')) {
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
            \Log::error('Erreur updateStatus Reception RoomController', [
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
}

