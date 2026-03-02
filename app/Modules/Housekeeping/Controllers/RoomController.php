<?php

namespace App\Modules\Housekeeping\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function __construct(
        protected HousekeepingService $housekeepingService
    ) {}

    /**
     * Liste des chambres à nettoyer (pending + in_progress).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $query = Room::where('hotel_id', $hotel->id)
            ->whereIn('cleaning_state', ['pending', 'in_progress'])
            ->with('roomType');

        if ($request->filled('state')) {
            $query->where('cleaning_state', $request->state);
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        $rooms = $query->orderBy('room_number')->get();
        $floors = Room::where('hotel_id', $hotel->id)
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->sort()
            ->values();

        $stats = [
            'pending' => Room::where('hotel_id', $hotel->id)->where('cleaning_state', 'pending')->count(),
            'in_progress' => Room::where('hotel_id', $hotel->id)->where('cleaning_state', 'in_progress')->count(),
        ];

        return view('housekeeping.rooms.index', compact('hotel', 'rooms', 'floors', 'stats'));
    }

    /**
     * Démarrer le nettoyage (pending → in_progress).
     */
    public function startCleaning(Room $room)
    {
        try {
            $this->housekeepingService->startCleaning($room, Auth::user());
            $user = Auth::user();
            ActivityLog::log(
                "Début de nettoyage : chambre {$room->room_number}",
                $room,
                [
                    'action_type' => 'housekeeping_cleaning_started',
                    'hotel_name' => $user->hotel?->name,
                    'room_number' => $room->room_number,
                ],
                'application',
                'updated'
            );
            return redirect()->back()->with('success', "Nettoyage de la chambre {$room->room_number} démarré.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Terminer le nettoyage (in_progress → done, chambre disponible).
     * Option : signaler du linge client oublié en chambre (notifie la buanderie).
     */
    public function completeCleaning(Request $request, Room $room)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'client_linen_flagged' => 'nullable|boolean',
            'client_linen_description' => 'required_if:client_linen_flagged,true|nullable|string|max:1000',
        ]);
        $notes = $validated['notes'] ?? null;
        $clientLinenDescription = !empty($validated['client_linen_flagged']) && !empty(trim((string) ($validated['client_linen_description'] ?? '')))
            ? trim($validated['client_linen_description'])
            : null;
        try {
            $this->housekeepingService->completeCleaning($room, Auth::user(), $notes, $clientLinenDescription);
            $user = Auth::user();
            ActivityLog::log(
                "Nettoyage terminé : chambre {$room->room_number}",
                $room,
                [
                    'action_type' => 'housekeeping_cleaning_completed',
                    'hotel_name' => $user->hotel?->name,
                    'room_number' => $room->room_number,
                ],
                'application',
                'updated'
            );
            return redirect()->back()->with('success', "Chambre {$room->room_number} nettoyée et disponible.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
