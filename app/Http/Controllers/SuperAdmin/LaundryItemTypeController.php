<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Modules\Laundry\Models\LaundryItemType;
use Illuminate\Http\Request;

class LaundryItemTypeController extends Controller
{
    /**
     * Liste des types de linge d'un hôtel (Super Admin).
     */
    public function index(Hotel $hotel)
    {
        $itemTypes = LaundryItemType::where('hotel_id', $hotel->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('super.laundry-item-types.index', compact('hotel', 'itemTypes'));
    }

    /**
     * Créer un type de linge pour l'hôtel.
     */
    public function store(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['hotel_id'] = $hotel->id;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        LaundryItemType::create($validated);

        return redirect()
            ->route('super.hotels.laundry-item-types.index', $hotel)
            ->with('success', 'Type de linge créé avec succès.');
    }

    /**
     * Modifier un type de linge (doit appartenir à l'hôtel).
     */
    public function update(Request $request, Hotel $hotel, LaundryItemType $laundryItemType)
    {
        if ($laundryItemType->hotel_id !== $hotel->id) {
            abort(403, 'Ce type de linge n\'appartient pas à cet hôtel.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $laundryItemType->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('super.hotels.laundry-item-types.index', $hotel)
            ->with('success', 'Type de linge mis à jour.');
    }

    /**
     * Supprimer un type de linge (doit appartenir à l'hôtel).
     */
    public function destroy(Hotel $hotel, LaundryItemType $laundryItemType)
    {
        if ($laundryItemType->hotel_id !== $hotel->id) {
            abort(403, 'Ce type de linge n\'appartient pas à cet hôtel.');
        }

        $laundryItemType->delete();

        return redirect()
            ->route('super.hotels.laundry-item-types.index', $hotel)
            ->with('success', 'Type de linge supprimé.');
    }
}
