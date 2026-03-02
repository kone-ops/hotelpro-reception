<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Panne;
use App\Models\PanneCategory;
use App\Models\PanneType;
use App\Models\Room;
use App\Modules\Maintenance\Models\MaintenanceArea;
use App\Modules\Maintenance\Services\PanneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanneController extends Controller
{
    public function __construct(
        protected PanneService $panneService
    ) {}

    /**
     * Liste des pannes par statut (onglets: signalées, en cours, résolues).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('maintenance.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $status = $request->get('status', Panne::STATUS_SIGNALEE);
        if (!array_key_exists($status, Panne::STATUSES)) {
            $status = Panne::STATUS_SIGNALEE;
        }

        $pannes = Panne::where('hotel_id', $hotel->id)
            ->byStatus($status)
            ->with(['panneType', 'panneCategory', 'room', 'maintenanceArea', 'reporter'])
            ->orderByDesc('reported_at')
            ->paginate(20);

        $counts = [
            Panne::STATUS_SIGNALEE => Panne::where('hotel_id', $hotel->id)->byStatus(Panne::STATUS_SIGNALEE)->count(),
            Panne::STATUS_EN_COURS => Panne::where('hotel_id', $hotel->id)->byStatus(Panne::STATUS_EN_COURS)->count(),
            Panne::STATUS_RESOLUE => Panne::where('hotel_id', $hotel->id)->byStatus(Panne::STATUS_RESOLUE)->count(),
        ];

        return view('maintenance.pannes.index', compact('hotel', 'pannes', 'status', 'counts'));
    }

    /**
     * Page détaillée d'une panne.
     */
    public function show(Panne $panne)
    {
        $user = Auth::user();
        if ($user->hotel_id === null || $panne->hotel_id !== $user->hotel_id) {
            abort(403, 'Cette panne n\'appartient pas à votre hôtel.');
        }
        $panne->load(['panneType', 'panneCategory', 'room', 'maintenanceArea', 'reporter', 'resolver', 'interventions.user']);
        return view('maintenance.pannes.show', compact('panne'));
    }

    /**
     * Formulaire de signalement d'une panne.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('maintenance.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $categories = PanneCategory::where('hotel_id', $hotel->id)->orderBy('order')->orderBy('name')->get();
        $types = PanneType::where('hotel_id', $hotel->id)->with('panneCategory')->orderBy('order')->orderBy('name')->get();
        $rooms = Room::where('hotel_id', $hotel->id)->orderBy('room_number')->get();
        $areas = MaintenanceArea::where('hotel_id', $hotel->id)->orderBy('category')->orderBy('name')->get();

        return view('maintenance.pannes.create', compact('hotel', 'categories', 'types', 'rooms', 'areas'));
    }

    /**
     * Enregistrer un signalement de panne.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        if (!$hotel) {
            return redirect()->route('maintenance.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $request->validate([
            'panne_type_id' => 'required|exists:panne_types,id',
            'panne_category_id' => 'required|exists:panne_categories,id',
            'location_type' => 'required|in:room,area',
            'room_id' => 'required_if:location_type,room|nullable|exists:rooms,id',
            'maintenance_area_id' => 'required_if:location_type,area|nullable|exists:maintenance_areas,id',
            'description' => 'required|string|max:2000',
        ]);

        $roomId = $request->location_type === 'room' ? (int) $request->room_id : null;
        $areaId = $request->location_type === 'area' ? (int) $request->maintenance_area_id : null;

        try {
            $panne = $this->panneService->report(
                $hotel->id,
                (int) $request->panne_type_id,
                (int) $request->panne_category_id,
                $request->location_type,
                $roomId,
                $areaId,
                $request->description,
                $user
            );
            return redirect()->route('maintenance.pannes.show', $panne)
                ->with('success', 'Panne signalée. Elle apparaît dans la liste des pannes signalées.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Passer en "en cours de maintenance".
     */
    public function startMaintenance(Request $request, Panne $panne)
    {
        $user = Auth::user();
        $request->validate(['notes' => 'nullable|string|max:500']);
        try {
            $this->panneService->startMaintenance($panne, $user, $request->notes);
            return redirect()->back()->with('success', 'Panne mise en cours de maintenance.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Marquer comme résolue.
     */
    public function resolve(Request $request, Panne $panne)
    {
        $user = Auth::user();
        $request->validate(['notes' => 'nullable|string|max:500']);
        try {
            $this->panneService->resolve($panne, $user, $request->notes);
            return redirect()->back()->with('success', 'Panne marquée comme résolue.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Ajouter une note d'intervention.
     */
    public function addNote(Request $request, Panne $panne)
    {
        $user = Auth::user();
        $request->validate(['notes' => 'required|string|max:1000']);
        try {
            $this->panneService->addInterventionNote($panne, $user, $request->notes);
            return redirect()->back()->with('success', 'Note ajoutée.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
