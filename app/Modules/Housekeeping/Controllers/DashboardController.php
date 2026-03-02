<?php

namespace App\Modules\Housekeeping\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tableau de bord du service des étages.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $pendingTasks = HousekeepingTask::where('hotel_id', $hotel->id)
            ->pending()
            ->with(['room', 'room.roomType'])
            ->orderBy('created_at')
            ->get();

        $inProgressTasks = HousekeepingTask::where('hotel_id', $hotel->id)
            ->inProgress()
            ->with(['room', 'room.roomType', 'assignedTo'])
            ->orderBy('started_at')
            ->get();

        $roomsPending = Room::where('hotel_id', $hotel->id)
            ->where('cleaning_state', 'pending')
            ->with('roomType')
            ->orderBy('room_number')
            ->get();

        $roomsInProgress = Room::where('hotel_id', $hotel->id)
            ->where('cleaning_state', 'in_progress')
            ->with('roomType')
            ->orderBy('room_number')
            ->get();

        $stats = [
            'pending' => $roomsPending->count(),
            'in_progress' => $roomsInProgress->count(),
            'total_tasks_today' => HousekeepingTask::where('hotel_id', $hotel->id)
                ->whereDate('created_at', today())->count(),
            'done_today' => HousekeepingTask::where('hotel_id', $hotel->id)
                ->whereDate('completed_at', today())->count(),
        ];

        return view('housekeeping.dashboard', compact('hotel', 'roomsPending', 'roomsInProgress', 'stats', 'pendingTasks', 'inProgressTasks'));
    }
}
