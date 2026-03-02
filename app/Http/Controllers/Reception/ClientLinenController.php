<?php

namespace App\Http\Controllers\Reception;

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
     * Liste du linge client (réception) : en attente de retrait ou prêt pour retrait.
     */
    public function index()
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('reception.dashboard')->with('error', 'Aucun hôtel assigné.');
        }
        $items = ClientLinen::where('hotel_id', $hotel->id)
            ->where('source', ClientLinen::SOURCE_RECEPTION)
            ->with(['room', 'reservation', 'receivedByUser'])
            ->latest('received_at')
            ->get();
        return view('reception.client-linen.index', compact('hotel', 'items'));
    }

    /**
     * Formulaire d'enregistrement d'un dépôt de linge client à la réception.
     * Même logique que le service des étages : chambre, nom client, description des linges, notes.
     */
    public function create()
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('reception.dashboard')->with('error', 'Aucun hôtel assigné.');
        }
        $rooms = Room::where('hotel_id', $hotel->id)->orderBy('room_number')->get(['id', 'room_number', 'floor']);
        return view('reception.client-linen.create', compact('hotel', 'rooms'));
    }

    /**
     * Enregistrer un dépôt de linge client et notifier la buanderie.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('reception.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'client_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);

        $roomId = null;
        if (!empty($validated['room_id'])) {
            $room = Room::where('id', $validated['room_id'])->where('hotel_id', $hotel->id)->first();
            if ($room) {
                $roomId = $room->id;
            }
        }

        $validated['reservation_id'] = $validated['reservation_id'] ?? null;
        if ($validated['reservation_id']) {
            $res = \App\Models\Reservation::where('id', $validated['reservation_id'])->where('hotel_id', $hotel->id)->first();
            if (!$res) {
                return redirect()->back()->withErrors(['reservation_id' => 'Réservation invalide pour cet hôtel.'])->withInput();
            }
        }

        $clientLinen = ClientLinen::create([
            'hotel_id' => $hotel->id,
            'source' => ClientLinen::SOURCE_RECEPTION,
            'room_id' => $roomId,
            'reservation_id' => $validated['reservation_id'],
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
            'client_linen_reception',
            'Linge client – Réception',
            'Un client a déposé du linge à la réception (à récupérer par la buanderie).',
            'info',
            null,
            $clientLinen,
            route('laundry.client-linen.index', ['source' => 'reception']),
            'Voir le linge client – Réception'
        );

        ActivityLog::log(
            'Dépôt linge client enregistré à la réception',
            $clientLinen,
            [
                'action_type' => 'reception_client_linen_deposit',
                'hotel_name' => $hotel->name,
                'client_linen_id' => $clientLinen->id,
            ],
            'application',
            'created'
        );

        return redirect()->route('reception.client-linen.index')->with('success', 'Dépôt de linge client enregistré. La buanderie a été notifiée.');
    }

    /**
     * Marquer un linge client comme « récupéré par le client » (côté réception).
     */
    public function markPickedUp(ClientLinen $clientLinen)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel || $clientLinen->hotel_id !== $hotel->id || $clientLinen->source !== ClientLinen::SOURCE_RECEPTION) {
            abort(403, 'Accès non autorisé.');
        }
        if ($clientLinen->status === ClientLinen::STATUS_PICKED_UP) {
            return redirect()->route('reception.client-linen.index')->with('info', 'Ce linge est déjà marqué comme récupéré.');
        }
        $clientLinen->update([
            'status' => ClientLinen::STATUS_PICKED_UP,
            'picked_up_at' => now(),
            'picked_up_by' => $user->id,
        ]);
        ActivityLog::log(
            'Linge client marqué récupéré par le client (réception)',
            $clientLinen,
            [
                'action_type' => 'reception_client_linen_picked_up',
                'hotel_name' => $hotel->name,
                'client_linen_id' => $clientLinen->id,
            ],
            'application',
            'updated'
        );
        return redirect()->route('reception.client-linen.index')->with('success', 'Linge client marqué comme récupéré par le client.');
    }
}
