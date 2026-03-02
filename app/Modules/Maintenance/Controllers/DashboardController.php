<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Panne;
use App\Models\Room;
use App\Modules\Maintenance\Models\MaintenanceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tableau de bord du service technique : chambres + espaces (publics, techniques, etc.).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $stats = [
            'issue' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'issue')->count(),
            'maintenance' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'maintenance')->count(),
            'out_of_service' => Room::where('hotel_id', $hotel->id)->where('technical_state', 'out_of_service')->count(),
        ];
        $stats['total'] = $stats['issue'] + $stats['maintenance'] + $stats['out_of_service'];

        $areasToFollow = MaintenanceArea::where('hotel_id', $hotel->id)
            ->whereIn('technical_state', ['issue', 'maintenance', 'out_of_service'])
            ->count();

        $pannesCounts = [
            'signalée' => Panne::where('hotel_id', $hotel->id)->where('status', Panne::STATUS_SIGNALEE)->count(),
            'en_cours' => Panne::where('hotel_id', $hotel->id)->where('status', Panne::STATUS_EN_COURS)->count(),
            'résolue' => Panne::where('hotel_id', $hotel->id)->where('status', Panne::STATUS_RESOLUE)->count(),
        ];
        $pannesCounts['total'] = $pannesCounts['signalée'] + $pannesCounts['en_cours'] + $pannesCounts['résolue'];

        $pannesRecentes = Panne::where('hotel_id', $hotel->id)
            ->with(['panneType', 'panneCategory', 'room', 'maintenanceArea', 'reporter'])
            ->orderByDesc('reported_at')
            ->limit(10)
            ->get();

        return view('maintenance.dashboard', compact('hotel', 'stats', 'areasToFollow', 'pannesCounts', 'pannesRecentes'));
    }
}
