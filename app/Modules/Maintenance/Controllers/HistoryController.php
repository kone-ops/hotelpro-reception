<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RoomStateHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Historique des interventions techniques (changements d'état technique).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $roomIds = $hotel->rooms()->pluck('id');

        $query = RoomStateHistory::where('state_type', 'technical')
            ->whereIn('room_id', $roomIds)
            ->with(['room', 'room.roomType', 'user'])
            ->orderByDesc('changed_at');

        if ($request->filled('service') && $request->service === 'maintenance') {
            $query->where('service', 'maintenance');
        }

        $history = $query->paginate(20);

        $stateLabels = [
            'normal' => 'Normal',
            'issue' => 'Problème signalé',
            'maintenance' => 'En maintenance',
            'out_of_service' => 'Hors service',
        ];

        return view('maintenance.history.index', compact('hotel', 'history', 'stateLabels'));
    }
}
