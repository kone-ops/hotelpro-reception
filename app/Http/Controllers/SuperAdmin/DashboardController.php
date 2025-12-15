<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Reservation;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
	public function index()
	{
		$stats = [
			'total_hotels' => Hotel::count(),
			'total_users' => User::count(),
			'total_reservations' => Reservation::withoutGlobalScopes()->count(),
			'reservations_today' => Reservation::withoutGlobalScopes()->whereDate('created_at', today())->count(),
		];

		// Récupérer les activités des dernières 24 heures
		$recentActivities = ActivityLog::with(['subject', 'causer'])
			->where('created_at', '>=', Carbon::now()->subHours(24))
			->orderBy('created_at', 'desc')
			->limit(10)
			->get();

		return view('super.dashboard', compact('stats', 'recentActivities'));
	}
}


