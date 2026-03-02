<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PanneCategory;
use App\Models\PanneType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PanneTypeCategoryController extends Controller
{
    /**
     * Liste des catégories et types de pannes (paramétrage).
     */
    public function index()
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('maintenance.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $categories = PanneCategory::where('hotel_id', $hotel->id)
            ->withCount('panneTypes')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        $types = PanneType::where('hotel_id', $hotel->id)
            ->with('panneCategory')
            ->orderBy('panne_category_id')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('maintenance.pannes.types-index', compact('hotel', 'categories', 'types'));
    }

    /**
     * Créer une catégorie.
     */
    public function storeCategory(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->back()->with('error', 'Aucun hôtel assigné.');
        }
        $request->validate(['name' => 'required|string|max:100']);
        $maxOrder = PanneCategory::where('hotel_id', $hotel->id)->max('order') ?? 0;
        PanneCategory::create([
            'hotel_id' => $hotel->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'order' => $maxOrder + 1,
        ]);
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Catégorie créée.');
    }

    /**
     * Mettre à jour une catégorie.
     */
    public function updateCategory(Request $request, PanneCategory $panneCategory)
    {
        $user = Auth::user();
        if ($panneCategory->hotel_id !== $user->hotel_id) {
            abort(403);
        }
        $request->validate(['name' => 'required|string|max:100']);
        $panneCategory->update(['name' => $request->name, 'slug' => Str::slug($request->name)]);
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Catégorie mise à jour.');
    }

    /**
     * Supprimer une catégorie (si aucun type ni panne).
     */
    public function destroyCategory(PanneCategory $panneCategory)
    {
        $user = Auth::user();
        if ($panneCategory->hotel_id !== $user->hotel_id) {
            abort(403);
        }
        if ($panneCategory->panneTypes()->exists() || $panneCategory->pannes()->exists()) {
            return redirect()->back()->with('error', 'Impossible de supprimer : des types ou pannes utilisent cette catégorie.');
        }
        $panneCategory->delete();
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Catégorie supprimée.');
    }

    /**
     * Créer un type de panne.
     */
    public function storeType(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->back()->with('error', 'Aucun hôtel assigné.');
        }
        $request->validate([
            'name' => 'required|string|max:100',
            'panne_category_id' => 'required|exists:panne_categories,id',
        ]);
        if (PanneCategory::where('id', $request->panne_category_id)->where('hotel_id', $hotel->id)->doesntExist()) {
            return redirect()->back()->with('error', 'Catégorie invalide.');
        }
        $maxOrder = PanneType::where('hotel_id', $hotel->id)->where('panne_category_id', $request->panne_category_id)->max('order') ?? 0;
        PanneType::create([
            'hotel_id' => $hotel->id,
            'panne_category_id' => $request->panne_category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'order' => $maxOrder + 1,
        ]);
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Type de panne créé.');
    }

    /**
     * Mettre à jour un type.
     */
    public function updateType(Request $request, PanneType $panneType)
    {
        $user = Auth::user();
        if ($panneType->hotel_id !== $user->hotel_id) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:100',
            'panne_category_id' => 'required|exists:panne_categories,id',
        ]);
        $panneType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'panne_category_id' => $request->panne_category_id,
        ]);
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Type mis à jour.');
    }

    /**
     * Supprimer un type (si aucune panne).
     */
    public function destroyType(PanneType $panneType)
    {
        $user = Auth::user();
        if ($panneType->hotel_id !== $user->hotel_id) {
            abort(403);
        }
        if ($panneType->pannes()->exists()) {
            return redirect()->back()->with('error', 'Impossible de supprimer : des pannes utilisent ce type.');
        }
        $panneType->delete();
        return redirect()->route('maintenance.panne-types.index')->with('success', 'Type supprimé.');
    }
}
