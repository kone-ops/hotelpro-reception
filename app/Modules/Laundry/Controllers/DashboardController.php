<?php

namespace App\Modules\Laundry\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Laundry\Models\LaundryCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tableau de bord buanderie.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;

        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'Aucun hôtel assigné.');
        }

        $pendingCollections = LaundryCollection::where('hotel_id', $hotel->id)
            ->pending()
            ->with(['room', 'room.roomType', 'collectedByUser'])
            ->latest('collected_at')
            ->limit(15)
            ->get();

        $stats = [
            'pending' => LaundryCollection::where('hotel_id', $hotel->id)->pending()->count(),
            'in_wash' => LaundryCollection::where('hotel_id', $hotel->id)->inWash()->count(),
            'done_today' => LaundryCollection::where('hotel_id', $hotel->id)
                ->done()
                ->whereDate('updated_at', today())->count(),
            'total_today' => LaundryCollection::where('hotel_id', $hotel->id)
                ->whereDate('collected_at', today())->count(),
        ];

        return view('laundry.dashboard', compact('hotel', 'pendingCollections', 'stats'));
    }
}
