<?php

namespace App\Modules\Housekeeping\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Modules\Laundry\Models\ClientLinen;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientLinenController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Formulaire d'enregistrement d'un dépôt de linge client (trouvé en chambre).
     * Le service des étages renseigne la chambre, le nom du client et la description des linges.
     */
    public function create()
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('housekeeping.dashboard')->with('error', 'Aucun hôtel assigné.');
        }
        $rooms = Room::where('hotel_id', $hotel->id)->orderBy('room_number')->get(['id', 'room_number', 'floor']);
        return view('housekeeping.client-linen.create', compact('hotel', 'rooms'));
    }

    /**
     * Enregistrer un dépôt de linge client (source = chambre) et notifier la buanderie.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('housekeeping.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'client_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $room = Room::where('id', $validated['room_id'])->where('hotel_id', $hotel->id)->firstOrFail();

        $clientLinen = ClientLinen::create([
            'hotel_id' => $hotel->id,
            'source' => ClientLinen::SOURCE_ROOM,
            'room_id' => $room->id,
            'reservation_id' => null,
            'housekeeping_task_id' => null,
            'received_at' => now(),
            'received_by' => $user->id,
            'status' => ClientLinen::STATUS_PENDING_PICKUP,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
        ]);

        $this->notificationService->notifyLaundry(
            $hotel->id,
            'client_linen_room',
            'Linge client – Chambre',
            "Linge client signalé en chambre {$room->room_number} par le service des étages (dépôt manuel).",
            'info',
            null,
            $clientLinen,
            route('laundry.client-linen.index', ['source' => 'room']),
            'Voir le linge client – Chambre'
        );

        ActivityLog::log(
            "Dépôt linge client enregistré (chambre {$room->room_number})",
            $clientLinen,
            [
                'action_type' => 'housekeeping_client_linen_deposit',
                'hotel_name' => $hotel->name,
                'room_number' => $room->room_number,
                'client_linen_id' => $clientLinen->id,
            ],
            'application',
            'created'
        );

        return redirect()->route('housekeeping.client-linen.create')->with('success', 'Dépôt de linge client enregistré. La buanderie a été notifiée.');
    }
}
