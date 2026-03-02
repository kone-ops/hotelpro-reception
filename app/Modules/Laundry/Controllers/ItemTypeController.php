<?php

namespace App\Modules\Laundry\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Modules\Laundry\Models\LaundryItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemTypeController extends Controller
{
    /**
     * Liste des types de linge de l'hôtel.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $itemTypes = LaundryItemType::where('hotel_id', $hotel->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('laundry.item-types.index', compact('hotel', 'itemTypes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $itemType = LaundryItemType::create([
            'hotel_id' => $hotel->id,
            'name' => $request->name,
            'code' => $request->code ?: null,
            'sort_order' => (int) ($request->sort_order ?? 0),
        ]);

        ActivityLog::log(
            "Type de linge créé : {$itemType->name}",
            $itemType,
            [
                'action_type' => 'laundry_item_type_created',
                'hotel_name' => $hotel->name,
            ],
            'application',
            'created'
        );

        return redirect()->route('laundry.item-types.index')->with('success', 'Type de linge ajouté.');
    }

    public function update(Request $request, LaundryItemType $itemType)
    {
        if (Auth::user()->hotel_id !== $itemType->hotel_id) {
            abort(403, 'Ce type de linge n\'appartient pas à votre hôtel.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $itemType->update([
            'name' => $request->name,
            'code' => $request->code ?: null,
            'sort_order' => (int) ($request->sort_order ?? 0),
        ]);

        ActivityLog::log(
            "Type de linge modifié : {$itemType->name}",
            $itemType,
            [
                'action_type' => 'laundry_item_type_updated',
                'hotel_name' => Auth::user()->hotel?->name,
            ],
            'application',
            'updated'
        );

        return redirect()->route('laundry.item-types.index')->with('success', 'Type de linge mis à jour.');
    }

    public function destroy(LaundryItemType $itemType)
    {
        if (Auth::user()->hotel_id !== $itemType->hotel_id) {
            abort(403, 'Ce type de linge n\'appartient pas à votre hôtel.');
        }

        $name = $itemType->name;
        $itemType->delete();

        ActivityLog::log(
            "Type de linge supprimé : {$name}",
            null,
            [
                'action_type' => 'laundry_item_type_deleted',
                'hotel_name' => Auth::user()->hotel?->name,
            ],
            'application',
            'deleted'
        );

        return redirect()->route('laundry.item-types.index')->with('success', 'Type de linge supprimé.');
    }
}
