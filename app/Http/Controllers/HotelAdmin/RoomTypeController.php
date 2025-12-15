<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoomTypeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des types de chambres
     */
    public function index()
    {
        $hotel = Auth::user()->hotel;
        
        $roomTypes = $hotel->roomTypes()
            ->withCount(['rooms'])
            ->orderBy('name')
            ->get();
        
        return view('hotel.room-types.index', compact('hotel', 'roomTypes'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $hotel = Auth::user()->hotel;
        return view('hotel.room-types.create', compact('hotel'));
    }

    /**
     * Enregistrer un nouveau type de chambre
     */
    public function store(Request $request)
    {
        $hotel = Auth::user()->hotel;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $validated['hotel_id'] = $hotel->id;
        // Gérer la checkbox is_available séparément
        $validated['is_available'] = $request->has('is_available') ? true : false;
        
        RoomType::create($validated);
        
        return redirect()
            ->route('hotel.room-types.index')
            ->with('success', '✅ Type de chambre créé avec succès !');
    }

    /**
     * Afficher les détails d'un type de chambre
     */
    public function show(RoomType $roomType)
    {
        $this->authorize('view', $roomType);
        
        $roomType->load(['rooms' => function($query) {
            $query->orderBy('room_number');
        }]);
        
        return view('hotel.room-types.show', compact('roomType'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(RoomType $roomType)
    {
        $this->authorize('update', $roomType);
        
        return view('hotel.room-types.edit', compact('roomType'));
    }

    /**
     * Mettre à jour un type de chambre
     */
    public function update(Request $request, RoomType $roomType)
    {
        $this->authorize('update', $roomType);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);
        
        // Gérer la checkbox is_available séparément
        $validated['is_available'] = $request->has('is_available') ? true : false;
        
        $roomType->update($validated);
        
        return redirect()
            ->route('hotel.room-types.index')
            ->with('success', '✅ Type de chambre mis à jour avec succès !');
    }

    /**
     * Supprimer un type de chambre
     */
    public function destroy(RoomType $roomType)
    {
        $this->authorize('delete', $roomType);
        
        // Vérifier s'il y a des chambres associées
        if ($roomType->rooms()->count() > 0) {
            return back()->withErrors([
                'error' => 'Impossible de supprimer ce type car des chambres y sont associées. Supprimez d\'abord les chambres.'
            ]);
        }
        
        $roomType->delete();
        
        return redirect()
            ->route('hotel.room-types.index')
            ->with('success', 'Type de chambre supprimé avec succès !');
    }

    /**
     * Basculer la disponibilité d'un type
     */
    public function toggleAvailability(RoomType $roomType)
    {
        $this->authorize('update', $roomType);
        
        $roomType->update([
            'is_available' => !$roomType->is_available
        ]);
        
        $status = $roomType->is_available ? 'disponible' : 'indisponible';
        
        return back()->with('success', "Type de chambre marqué comme {$status}");
    }
}

