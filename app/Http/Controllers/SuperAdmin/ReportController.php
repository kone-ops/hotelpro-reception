<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $global_stats = [
            'total_hotels' => Hotel::count(),
            'total_users' => User::count(),
            'total_reservations' => Reservation::withoutGlobalScopes()->count(),
            'validated_reservations' => Reservation::withoutGlobalScopes()->where('status', 'validated')->count(),
            'pending_reservations' => Reservation::withoutGlobalScopes()->where('status', 'pending')->count(),
        ];

        // Réservations par hôtel
        $hotels_stats = Hotel::withCount(['reservations', 'users'])
            ->withCount(['reservations as validated_count' => function($query) {
                $query->where('status', 'validated');
            }])
            ->withCount(['reservations as pending_count' => function($query) {
                $query->where('status', 'pending');
            }])
            ->get();

        // Évolution des réservations (12 derniers mois)
        // Utiliser strftime pour SQLite
        $monthly_evolution = Reservation::withoutGlobalScopes()
            ->select(
                DB::raw("strftime('%Y', created_at) as year"),
                DB::raw("strftime('%m', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('super.reports.index', compact('global_stats', 'hotels_stats', 'monthly_evolution'));
    }

    public function hotel(Hotel $hotel)
    {
        // Statistiques spécifiques à l'hôtel
        $hotel_stats = [
            'total_reservations' => $hotel->reservations()->count(),
            'validated_reservations' => $hotel->reservations()->where('status', 'validated')->count(),
            'pending_reservations' => $hotel->reservations()->where('status', 'pending')->count(),
            'rejected_reservations' => $hotel->reservations()->where('status', 'rejected')->count(),
            'checked_in' => $hotel->reservations()->where('status', 'checked_in')->count(),
            'total_users' => $hotel->users()->count(),
            'total_rooms' => $hotel->rooms()->count(),
            'available_rooms' => $hotel->rooms()->where('status', 'available')->count(),
        ];

        // Réservations récentes
        $recent_reservations = $hotel->reservations()
            ->with(['room', 'roomType'])
            ->latest()
            ->limit(10)
            ->get();

        // Évolution des réservations pour cet hôtel (30 derniers jours)
        // Utiliser strftime pour SQLite
        $daily_evolution = $hotel->reservations()
            ->select(
                DB::raw("strftime('%Y-%m-%d', created_at) as date"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('super.reports.hotel', compact('hotel', 'hotel_stats', 'recent_reservations', 'daily_evolution'));
    }
}


