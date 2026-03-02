<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Panne;
use App\Models\Room;
use App\Modules\Maintenance\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function __construct(
        protected MaintenanceService $maintenanceService
    ) {}

    /**
     * Liste des chambres avec filtre par état technique (issue, maintenance, out_of_service).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $query = Room::where('hotel_id', $hotel->id)
            ->whereIn('technical_state', ['issue', 'maintenance', 'out_of_service'])
            ->with(['roomType', 'outOfServiceByUser']);

        if ($request->filled('state')) {
            $query->where('technical_state', $request->state);
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
            'issue' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'issue')->count(),
            'maintenance' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'maintenance')->count(),
            'out_of_service' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'out_of_service')->count(),
            'pannes_resolues' => Panne::where('hotel_id', $hotel->id)->where('status', Panne::STATUS_RESOLUE)->count(),
        ];

        return view('maintenance.rooms.index', compact('hotel', 'rooms', 'floors', 'stats'));
    }

    /**
     * Mise à jour de l'état technique d'une chambre.
     * Pour "hors service" : raison et période (from/until) optionnels.
     */
    public function updateTechnicalState(Request $request, Room $room)
    {
        $rules = [
            'technical_state' => 'required|in:normal,issue,maintenance,out_of_service',
            'notes' => 'nullable|string|max:500',
        ];
        if ($request->technical_state === 'out_of_service') {
            $rules['out_of_service_reason'] = 'nullable|string|max:1000';
            $rules['out_of_service_from'] = 'nullable|date';
            $rules['out_of_service_until'] = 'nullable|date|after_or_equal:out_of_service_from';
        }
        $request->validate($rules);

        $from = $request->filled('out_of_service_from') ? \Carbon\Carbon::parse($request->out_of_service_from) : null;
        $until = $request->filled('out_of_service_until') ? \Carbon\Carbon::parse($request->out_of_service_until) : null;

        try {
            $this->maintenanceService->updateTechnicalState(
                $room,
                $request->technical_state,
                Auth::user(),
                $request->notes,
                $request->out_of_service_reason,
                $from,
                $until
            );
            $label = match ($request->technical_state) {
                'normal' => 'remise en service',
                'issue' => 'pannes signalées',
                'maintenance' => 'mise en maintenance',
                'out_of_service' => 'hors service',
                default => $request->technical_state,
            };
            return redirect()->back()->with('success', "Chambre {$room->room_number} : {$label}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
