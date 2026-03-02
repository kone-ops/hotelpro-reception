<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PanneCategory;
use App\Models\PanneType;
use App\Modules\Maintenance\Models\MaintenanceArea;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class MaintenanceAreaController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}
    /**
     * Détermine le préfixe des routes et la route dashboard selon le contexte (réception, housekeeping, hotel, maintenance).
     */
    private function getAreaContext(Request $request): array
    {
        $name = $request->route()->getName() ?? '';
        if (str_starts_with($name, 'reception.areas')) {
            return ['areasRoutePrefix' => 'reception.areas', 'dashboardRoute' => 'reception.dashboard'];
        }
        if (str_starts_with($name, 'housekeeping.areas')) {
            return ['areasRoutePrefix' => 'housekeeping.areas', 'dashboardRoute' => 'housekeeping.dashboard'];
        }
        if (str_starts_with($name, 'hotel.areas')) {
            return ['areasRoutePrefix' => 'hotel.areas', 'dashboardRoute' => 'hotel.dashboard'];
        }
        return ['areasRoutePrefix' => 'maintenance.areas', 'dashboardRoute' => 'maintenance.dashboard'];
    }

    /**
     * Vue d'ensemble des espaces par catégorie (liens vers chaque catégorie).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        $context = $this->getAreaContext($request);

        if (!$hotel) {
            return redirect()->route($context['dashboardRoute'])->with('error', 'Aucun hôtel assigné.');
        }

        $categoriesStats = [];
        try {
            foreach (MaintenanceArea::CATEGORIES as $key => $label) {
                $categoriesStats[$key] = [
                    'label' => $label,
                    'total' => MaintenanceArea::where('hotel_id', $hotel->id)->where('category', $key)->count(),
                    'issue' => MaintenanceArea::where('hotel_id', $hotel->id)->where('category', $key)->where('technical_state', 'issue')->count(),
                    'maintenance' => MaintenanceArea::where('hotel_id', $hotel->id)->where('category', $key)->where('technical_state', 'maintenance')->count(),
                    'out_of_service' => MaintenanceArea::where('hotel_id', $hotel->id)->where('category', $key)->where('technical_state', 'out_of_service')->count(),
                ];
            }
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route($context['dashboardRoute'])->with('error', 'Erreur base de données (table maintenance_areas ?). Exécutez : php artisan migrate');
        }

        return view('maintenance.areas.index', compact('hotel', 'categoriesStats', 'context'));
    }

    /**
     * Liste des espaces d'une catégorie.
     */
    public function category(Request $request, string $category)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        $context = $this->getAreaContext($request);

        if (!$hotel) {
            return redirect()->route($context['dashboardRoute'])->with('error', 'Aucun hôtel assigné.');
        }

        if (!isset(MaintenanceArea::CATEGORIES[$category])) {
            return redirect()->route($context['areasRoutePrefix'] . '.index')->with('error', 'Catégorie invalide.');
        }

        $areas = MaintenanceArea::where('hotel_id', $hotel->id)
            ->where('category', $category)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $areas->count(),
            'issue' => $areas->where('technical_state', 'issue')->count(),
            'maintenance' => $areas->where('technical_state', 'maintenance')->count(),
            'out_of_service' => $areas->where('technical_state', 'out_of_service')->count(),
        ];

        $categoryLabel = MaintenanceArea::CATEGORIES[$category];

        return view('maintenance.areas.category', compact('hotel', 'category', 'categoryLabel', 'areas', 'stats', 'context'));
    }

    /**
     * Formulaire de création d'un espace.
     */
    public function create(Request $request, string $category)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        $context = $this->getAreaContext($request);

        if (!$hotel || !isset(MaintenanceArea::CATEGORIES[$category])) {
            return redirect()->route($context['areasRoutePrefix'] . '.index')->with('error', 'Catégorie invalide.');
        }

        $categoryLabel = MaintenanceArea::CATEGORIES[$category];
        $categories = PanneCategory::where('hotel_id', $hotel->id)->orderBy('order')->orderBy('name')->get();
        $types = PanneType::where('hotel_id', $hotel->id)->with('panneCategory')->orderBy('order')->orderBy('name')->get();
        return view('maintenance.areas.create', compact('hotel', 'category', 'categoryLabel', 'context', 'categories', 'types'));
    }

    /**
     * Enregistrement d'un nouvel espace.
     */
    public function store(Request $request, string $category)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        $context = $this->getAreaContext($request);

        if (!$hotel || !isset(MaintenanceArea::CATEGORIES[$category])) {
            return redirect()->route($context['areasRoutePrefix'] . '.index')->with('error', 'Catégorie invalide.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'panne_category_id' => 'nullable|exists:panne_categories,id',
            'panne_type_id' => 'nullable|exists:panne_types,id',
        ]);
        if (!empty($validated['panne_category_id']) && PanneCategory::where('id', $validated['panne_category_id'])->where('hotel_id', $hotel->id)->doesntExist()) {
            return redirect()->back()->withErrors(['panne_category_id' => 'Catégorie invalide.'])->withInput();
        }
        if (!empty($validated['panne_type_id']) && PanneType::where('id', $validated['panne_type_id'])->where('hotel_id', $hotel->id)->doesntExist()) {
            return redirect()->back()->withErrors(['panne_type_id' => 'Type de panne invalide.'])->withInput();
        }

        $area = MaintenanceArea::create([
            'hotel_id' => $hotel->id,
            'category' => $category,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'technical_state' => MaintenanceArea::STATE_NORMAL,
            'panne_category_id' => $validated['panne_category_id'] ?? null,
            'panne_type_id' => $validated['panne_type_id'] ?? null,
        ]);

        $categoryLabel = MaintenanceArea::CATEGORIES[$category] ?? $category;
        $this->notificationService->notifyHotelUsers(
            $hotel->id,
            'area_created',
            'Nouvel espace ajouté',
            $user->name . ' a ajouté l\'espace « ' . $area->name . ' » dans ' . $categoryLabel . '.',
            'info',
            null,
            $area,
            ['area_id' => $area->id, 'category' => $category, 'area_name' => $area->name],
            $this->areaCategoryUrl($category),
            'Voir les espaces'
        );

        return redirect()->route($context['areasRoutePrefix'] . '.category', $category)
            ->with('success', 'Espace ajouté.');
    }

    /**
     * Mise à jour de l'état technique d'un espace.
     */
    public function updateState(Request $request, MaintenanceArea $area)
    {
        $user = Auth::user();
        if ($area->hotel_id !== $user->hotel_id) {
            return redirect()->back()->with('error', 'Cet espace n\'appartient pas à votre hôtel.');
        }

        $request->validate([
            'technical_state' => 'required|in:normal,issue,maintenance,out_of_service',
            'notes' => 'nullable|string|max:500',
        ]);

        $area->update([
            'technical_state' => $request->technical_state,
            'notes' => $request->notes,
        ]);

        $labels = [
            'normal' => 'Normal',
            'issue' => 'Problème signalé',
            'maintenance' => 'En maintenance',
            'out_of_service' => 'Hors service',
        ];
        $stateLabel = $labels[$request->technical_state];
        $icon = match ($request->technical_state) {
            'normal' => 'success',
            'issue' => 'warning',
            'maintenance' => 'info',
            'out_of_service' => 'danger',
            default => 'info',
        };
        $this->notificationService->notifyHotelUsers(
            $area->hotel_id,
            'area_state_updated',
            'État d\'espace modifié',
            $user->name . ' a mis « ' . $area->name . ' » en : ' . $stateLabel . '.',
            $icon,
            null,
            $area,
            ['area_id' => $area->id, 'category' => $area->category, 'technical_state' => $request->technical_state],
            $this->areaCategoryUrl($area->category),
            'Voir les espaces'
        );

        return redirect()->back()->with('success', "{$area->name} : {$stateLabel}.");
    }

    /**
     * Suppression d'un espace.
     */
    public function destroy(MaintenanceArea $area)
    {
        $user = Auth::user();
        if ($area->hotel_id !== $user->hotel_id) {
            return redirect()->back()->with('error', 'Cet espace n\'appartient pas à votre hôtel.');
        }
        $context = $this->getAreaContext(request());
        $category = $area->category;
        $categoryLabel = MaintenanceArea::CATEGORIES[$category] ?? $category;
        $areaName = $area->name;
        $area->delete();

        $this->notificationService->notifyHotelUsers(
            $user->hotel_id,
            'area_deleted',
            'Espace supprimé',
            $user->name . ' a supprimé l\'espace « ' . $areaName . ' » (' . $categoryLabel . ').',
            'danger',
            null,
            null,
            ['category' => $category, 'area_name' => $areaName],
            $this->areaCategoryUrl($category),
            'Voir les espaces'
        );

        return redirect()->route($context['areasRoutePrefix'] . '.category', $category)->with('success', 'Espace supprimé.');
    }

    /**
     * URL vers la liste des espaces de la catégorie (pour les notifications).
     */
    private function areaCategoryUrl(string $category): string
    {
        try {
            if (Route::has('hotel.areas.category')) {
                return route('hotel.areas.category', $category);
            }
            if (Route::has('maintenance.areas.category')) {
                return route('maintenance.areas.category', $category);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return url('/');
    }
}
