<?php

namespace App\Modules\Laundry\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    private const ACTION_TYPES = [
        'laundry_collection_updated',
        'laundry_collection_status_changed',
        'laundry_item_type_created',
        'laundry_item_type_updated',
        'laundry_item_type_deleted',
    ];

    /**
     * Historique des activités personnelles de l'utilisateur buanderie (filtrable par période).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('laundry.dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $dateDebut = $request->filled('date_debut')
            ? Carbon::parse($request->date_debut)->startOfDay()
            : now()->startOfWeek();
        $dateFin = $request->filled('date_fin')
            ? Carbon::parse($request->date_fin)->endOfDay()
            : now()->endOfDay();

        if ($dateDebut->gt($dateFin)) {
            $dateDebut = $dateFin->copy()->startOfDay();
        }

        $activities = ActivityLog::where('causer_id', $user->id)
            ->where('causer_type', get_class($user))
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get()
            ->filter(function ($log) {
                $type = is_array($log->properties) ? ($log->properties['action_type'] ?? null) : null;
                return $type && in_array($type, self::ACTION_TYPES, true);
            })
            ->values();

        $actionTypeLabels = [
            'laundry_collection_updated' => 'Collecte mise à jour',
            'laundry_collection_status_changed' => 'Statut collecte modifié',
            'laundry_item_type_created' => 'Type de linge créé',
            'laundry_item_type_updated' => 'Type de linge modifié',
            'laundry_item_type_deleted' => 'Type de linge supprimé',
        ];

        return view('laundry.history.index', [
            'hotel' => $hotel,
            'activities' => $activities->values(),
            'actionTypeLabels' => $actionTypeLabels,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ]);
    }
}
