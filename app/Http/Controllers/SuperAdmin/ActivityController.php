<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_activities'   => ActivityLog::count(),
            'activities_today'   => ActivityLog::whereDate('created_at', today())->count(),
            'activities_week'    => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'activities_month'   => ActivityLog::whereMonth('created_at', now()->month)->count(),
            'unique_users'       => ActivityLog::whereNotNull('causer_id')->distinct('causer_id')->count('causer_id'),
        ];

        $query = ActivityLog::with(['subject', 'causer'])->latest();

        if ($request->filled('hotel_id')) {
            $hotel = Hotel::find($request->hotel_id);
            if ($hotel) {
                $query->where(function ($q) use ($hotel) {
                    $q->where('properties->hotel_name', $hotel->name)
                        ->orWhere(function ($sub) use ($hotel) {
                            $sub->where('subject_type', Hotel::class)
                                ->where('subject_id', $hotel->id);
                        });
                });
            }
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        
        // Filtre par type d'action (dans properties)
        if ($request->filled('action_type')) {
            $query->where('properties->action_type', $request->action_type);
        }
        
        // Filtre par catégorie d'action (critique, sensible, normale)
        if ($request->filled('action_category')) {
            $category = $request->action_category;
            if ($category === 'critical') {
                $query->whereIn('properties->action_type', [
                    'reservation_validated',
                    'reservation_rejected',
                    'reservation_checkin',
                    'reservation_checkout',
                    'price_modified',
                    'payment_received',
                    'user_deleted',
                    'data_deleted',
                ]);
            } elseif ($category === 'sensitive') {
                $query->whereIn('properties->action_type', [
                    'reservation_updated',
                    'reservation_pending',
                    'room_status_changed',
                    'user_created',
                    'user_updated',
                    'hotel_updated',
                    'settings_changed',
                    'data_exported',
                    'data_imported',
                ]);
            }
        }
        
        // Filtre par rôle de l'utilisateur
        if ($request->filled('user_role')) {
            $role = $request->user_role;
            $query->whereHas('causer', function($q) use ($role) {
                $q->whereHas('roles', function($r) use ($role) {
                    $r->where('name', $role);
                });
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        // Calculer le total AVANT d'appliquer la limite
        $totalActivities = (clone $query)->count();
        
        // Limiter à 10 activités initialement
        $activities = $query->limit(10)->get();

        // Graphique des 7 derniers jours (compatible MySQL et SQLite)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $activities_chart = ActivityLog::select(
                    DB::raw("strftime('%Y-%m-%d', created_at) as date"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            // MySQL, PostgreSQL, etc.
            $activities_chart = ActivityLog::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        $top_users = User::select('users.id', 'users.name', 'users.email', DB::raw('COUNT(activity_logs.id) as activities_count'))
            ->join('activity_logs', 'activity_logs.causer_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('activities_count')
            ->limit(5)
            ->get();

        $hotels = Hotel::orderBy('name')->get();
        $users  = User::orderBy('name')->get();
        
        // Types d'événements avec catégories
        $events = [
            'created' => 'Créé',
            'updated' => 'Modifié',
            'deleted' => 'Supprimé',
        ];
        
        // Types d'actions critiques et sensibles
        $actionTypes = [
            'reservation_validated' => 'Validation de réservation',
            'reservation_rejected' => 'Rejet de réservation',
            'reservation_checkin' => 'Check-in',
            'reservation_checkout' => 'Check-out',
            'reservation_pending' => 'Remise en attente',
            'reservation_updated' => 'Modification de réservation',
            'room_status_changed' => 'Changement statut chambre',
            'price_modified' => 'Modification de prix',
            'payment_received' => 'Paiement reçu',
            'user_created' => 'Création utilisateur',
            'user_updated' => 'Modification utilisateur',
            'user_deleted' => 'Suppression utilisateur',
            'hotel_updated' => 'Modification hôtel',
            'settings_changed' => 'Changement de paramètres',
            'data_exported' => 'Export de données',
            'data_imported' => 'Import de données',
            'data_deleted' => 'Suppression de données',
        ];
        
        // Catégories d'actions pour filtrage
        $actionCategories = [
            'critical' => 'Actions Critiques',
            'sensitive' => 'Actions Sensibles',
            'normal' => 'Actions Normales',
        ];

        return view('super.activity.index', compact(
            'stats',
            'activities',
            'totalActivities',
            'activities_chart',
            'top_users',
            'hotels',
            'users',
            'events',
            'actionTypes',
            'actionCategories'
        ));
    }
    
    /**
     * Charger plus d'activités (AJAX)
     */
    public function loadMore(Request $request)
    {
        try {
            $offset = (int) $request->get('offset', 0);
            $limit = (int) $request->get('limit', 10);
            
            $query = ActivityLog::with(['subject', 'causer'])->latest();
        
        // Appliquer les mêmes filtres que la page principale
        if ($request->filled('hotel_id')) {
            $hotel = Hotel::find($request->hotel_id);
            if ($hotel) {
                $query->where(function ($q) use ($hotel) {
                    $q->where('properties->hotel_name', $hotel->name)
                        ->orWhere(function ($sub) use ($hotel) {
                            $sub->where('subject_type', Hotel::class)
                                ->where('subject_id', $hotel->id);
                        });
                });
            }
        }
        
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }
        
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        
        // Filtre par type d'action (dans properties)
        if ($request->filled('action_type')) {
            $query->where('properties->action_type', $request->action_type);
        }
        
        // Filtre par catégorie d'action (critique, sensible, normale)
        if ($request->filled('action_category')) {
            $category = $request->action_category;
            if ($category === 'critical') {
                $query->whereIn('properties->action_type', [
                    'reservation_validated',
                    'reservation_rejected',
                    'reservation_checkin',
                    'reservation_checkout',
                    'price_modified',
                    'payment_received',
                    'user_deleted',
                    'data_deleted',
                ]);
            } elseif ($category === 'sensitive') {
                $query->whereIn('properties->action_type', [
                    'reservation_updated',
                    'reservation_pending',
                    'room_status_changed',
                    'user_created',
                    'user_updated',
                    'hotel_updated',
                    'settings_changed',
                    'data_exported',
                    'data_imported',
                ]);
            }
        }
        
        // Filtre par rôle de l'utilisateur
        if ($request->filled('user_role')) {
            $role = $request->user_role;
            $query->whereHas('causer', function($q) use ($role) {
                $q->whereHas('roles', function($r) use ($role) {
                    $r->where('name', $role);
                });
            });
        }
        
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }
        
        // Cloner la requête AVANT d'appliquer offset/limit pour compter le total
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        
        // Récupérer les activités avec offset et limit
        $activities = $query->offset($offset)->limit($limit)->get();
        
        // Vérifier s'il y a plus d'activités après celles-ci
        $hasMore = ($offset + $limit) < $totalCount;
        
        $activitiesData = [];
        foreach ($activities as $activity) {
            try {
                $subjectName = null;
                $subjectType = null;
                
                if ($activity->subject) {
                    $subjectType = class_basename($activity->subject_type ?? '');
                    if (method_exists($activity->subject, 'name')) {
                        $subjectName = $activity->subject->name;
                    } elseif (method_exists($activity->subject, 'room_number')) {
                        $subjectName = $activity->subject->room_number;
                    } else {
                        $subjectName = '#' . ($activity->subject_id ?? 'N/A');
                    }
                } elseif ($activity->subject_type) {
                    $subjectType = class_basename($activity->subject_type);
                    $subjectName = '#' . ($activity->subject_id ?? 'N/A');
                }
                
                $properties = is_array($activity->properties) ? $activity->properties : [];
                
                // Récupérer les rôles de l'utilisateur
                $userRoles = [];
                if ($activity->causer && $activity->causer->roles) {
                    foreach ($activity->causer->roles as $role) {
                        $userRoles[] = $role->name;
                    }
                }
                
                $activitiesData[] = [
                    'id' => $activity->id ?? 0,
                    'description' => $activity->description ?? 'Activité',
                    'event' => $activity->event ?? 'modified',
                    'created_at' => $activity->created_at ? $activity->created_at->toIso8601String() : now()->toIso8601String(),
                    'created_at_human' => $activity->created_at ? $activity->created_at->diffForHumans() : 'Récemment',
                    'created_at_formatted' => $activity->created_at ? $activity->created_at->format('d/m/Y à H:i:s') : now()->format('d/m/Y à H:i:s'),
                    'causer' => $activity->causer ? [
                        'name' => $activity->causer->name ?? 'Utilisateur inconnu',
                        'roles' => $userRoles,
                    ] : null,
                    'subject' => ($subjectType && $subjectName) ? [
                        'type' => $subjectType,
                        'name' => $subjectName,
                    ] : null,
                    'hotel_name' => $properties['hotel_name'] ?? null,
                    'ip_address' => $activity->ip_address ?? null,
                    'user_agent' => $activity->user_agent ?? null,
                    'action_type' => $properties['action_type'] ?? null,
                    'properties' => $properties,
                ];
            } catch (\Exception $e) {
                // En cas d'erreur sur une activité, logger et continuer
                Log::warning('Erreur lors du mapping d\'une activité', [
                    'activity_id' => $activity->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Ajouter quand même l'activité avec des valeurs par défaut
                $activitiesData[] = [
                    'id' => $activity->id ?? 0,
                    'description' => $activity->description ?? 'Activité',
                    'event' => $activity->event ?? 'modified',
                    'created_at' => now()->toIso8601String(),
                    'created_at_human' => 'Récemment',
                    'created_at_formatted' => now()->format('d/m/Y à H:i:s'),
                    'causer' => null,
                    'subject' => null,
                    'hotel_name' => null,
                    'ip_address' => null,
                ];
            }
        }
        
        return response()->json([
            'activities' => $activitiesData,
            'has_more' => $hasMore,
            'next_offset' => $offset + $limit,
        ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans loadMore ActivityController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Une erreur est survenue lors du chargement des activités.',
                'message' => $e->getMessage(),
                'activities' => [],
                'has_more' => false,
                'next_offset' => 0,
            ], 500);
        }
    }
}


