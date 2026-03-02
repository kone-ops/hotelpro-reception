<?php

namespace App\Modules\Laundry\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Modules\Laundry\Models\ClientLinen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientLinenController extends Controller
{
    /**
     * Liste du linge client (onglets Réception / Chambre), filtres par période et statut.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $source = $request->get('source', 'reception');
        if (!in_array($source, ['reception', 'room'], true)) {
            $source = 'reception';
        }

        $query = ClientLinen::where('hotel_id', $hotel->id)
            ->when($source === 'reception', fn ($q) => $q->reception())
            ->when($source === 'room', fn ($q) => $q->room())
            ->with(['room', 'receivedByUser', 'pickedUpByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        $items = $query->latest('received_at')->paginate(20)->withQueryString();

        $statsReception = [
            'pending_pickup' => ClientLinen::where('hotel_id', $hotel->id)->reception()->pendingPickup()->count(),
            'total' => ClientLinen::where('hotel_id', $hotel->id)->reception()->count(),
        ];
        $statsRoom = [
            'pending_pickup' => ClientLinen::where('hotel_id', $hotel->id)->room()->pendingPickup()->count(),
            'total' => ClientLinen::where('hotel_id', $hotel->id)->room()->count(),
        ];

        return view('laundry.client-linen.index', compact('hotel', 'items', 'source', 'statsReception', 'statsRoom'));
    }

    /**
     * Mise à jour du statut d'un linge client.
     */
    public function updateStatus(Request $request, ClientLinen $clientLinen)
    {
        $this->authorizeHotel($clientLinen);
        $request->validate([
            'status' => 'required|in:pending_pickup,at_laundry,ready_for_pickup,picked_up,sent_to_laundry',
        ]);

        $previous = $clientLinen->status;
        $clientLinen->update(['status' => $request->status]);

        if ($request->status === ClientLinen::STATUS_PICKED_UP) {
            $clientLinen->update([
                'picked_up_at' => now(),
                'picked_up_by' => Auth::id(),
            ]);
        }

        ActivityLog::log(
            "Statut linge client #{$clientLinen->id} : {$previous} → {$request->status}",
            $clientLinen,
            [
                'action_type' => 'laundry_client_linen_status_changed',
                'hotel_name' => Auth::user()->hotel?->name,
                'client_linen_id' => $clientLinen->id,
                'source' => $clientLinen->source,
            ],
            'application',
            'updated'
        );

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    private function authorizeHotel(ClientLinen $clientLinen): void
    {
        if (Auth::user()->hotel_id !== $clientLinen->hotel_id) {
            abort(403, 'Ce linge client n\'appartient pas à votre hôtel.');
        }
    }
}
