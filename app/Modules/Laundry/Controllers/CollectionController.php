<?php

namespace App\Modules\Laundry\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Modules\Laundry\Models\LaundryCollection;
use App\Modules\Laundry\Services\LaundryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function __construct(
        protected LaundryService $laundryService
    ) {}

    /**
     * Liste des collectes de linge.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $query = LaundryCollection::where('hotel_id', $hotel->id)
            ->with(['room', 'room.roomType', 'collectedByUser', 'lines.itemType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('collected_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('collected_at', '<=', $request->date_to);
        }

        $collections = $query->latest('collected_at')->paginate(20);

        $stats = [
            'pending' => LaundryCollection::where('hotel_id', $hotel->id)->pending()->count(),
            'in_wash' => LaundryCollection::where('hotel_id', $hotel->id)->inWash()->count(),
            'done' => LaundryCollection::where('hotel_id', $hotel->id)->done()->count(),
        ];

        return view('laundry.collections.index', compact('hotel', 'collections', 'stats'));
    }

    /**
     * Détail d'une collecte + formulaire des quantités par type de linge.
     */
    public function show(LaundryCollection $collection)
    {
        $this->authorizeHotel($collection);
        $collection->load(['room', 'room.roomType', 'collectedByUser', 'lines.itemType']);
        $hotel = Auth::user()->hotel;
        $itemTypes = $hotel && $hotel->id
            ? \App\Modules\Laundry\Models\LaundryItemType::where('hotel_id', $hotel->id)->orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('laundry.collections.show', compact('collection', 'itemTypes', 'hotel'));
    }

    /**
     * Mise à jour des lignes (quantités) d'une collecte.
     */
    public function update(Request $request, LaundryCollection $collection)
    {
        $this->authorizeHotel($collection);
        $request->validate([
            'lines' => 'nullable|array',
            'lines.*' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $lines = $request->input('lines', []);
        $this->laundryService->saveCollectionLines($collection, $lines);
        if ($request->has('notes')) {
            $collection->update(['notes' => $request->notes]);
        }

        ActivityLog::log(
            "Quantités linge mises à jour : collecte chambre {$collection->room->room_number}",
            $collection,
            [
                'action_type' => 'laundry_collection_updated',
                'hotel_name' => Auth::user()->hotel?->name,
                'room_number' => $collection->room->room_number,
            ],
            'application',
            'updated'
        );

        return redirect()->route('laundry.collections.show', $collection)->with('success', 'Quantités enregistrées.');
    }

    /**
     * Changer le statut (en lavage, terminée).
     */
    public function updateStatus(Request $request, LaundryCollection $collection)
    {
        $this->authorizeHotel($collection);
        $request->validate(['status' => 'required|in:pending,in_wash,done']);

        $this->laundryService->updateCollectionStatus($collection, $request->status, Auth::user());

        ActivityLog::log(
            "Statut collecte linge : chambre {$collection->room->room_number} → {$request->status}",
            $collection,
            [
                'action_type' => 'laundry_collection_status_changed',
                'hotel_name' => Auth::user()->hotel?->name,
                'new_status' => $request->status,
            ],
            'application',
            'updated'
        );

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    private function authorizeHotel(LaundryCollection $collection): void
    {
        if (Auth::user()->hotel_id !== $collection->hotel_id) {
            abort(403, 'Cette collecte n\'appartient pas à votre hôtel.');
        }
    }
}
