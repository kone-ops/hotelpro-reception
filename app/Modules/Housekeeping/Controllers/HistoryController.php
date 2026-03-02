<?php

namespace App\Modules\Housekeeping\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    private const ACTION_TYPES = [
        'housekeeping_cleaning_started',
        'housekeeping_cleaning_completed',
    ];

    /**
     * Historique des activités personnelles de l'utilisateur service des étages (filtrable par période).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('housekeeping.dashboard')->with('error', 'Aucun hôtel assigné.');
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
            'housekeeping_cleaning_started' => 'Début nettoyage',
            'housekeeping_cleaning_completed' => 'Fin nettoyage',
        ];

        return view('housekeeping.history.index', [
            'hotel' => $hotel,
            'activities' => $activities,
            'actionTypeLabels' => $actionTypeLabels,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ]);
    }
}
