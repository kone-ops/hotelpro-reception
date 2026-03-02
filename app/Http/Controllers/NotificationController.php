<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Models\User;
use App\Models\NotificationAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Types de notifications professionnelles (visibles par les admins)
     */
    protected const PROFESSIONAL_NOTIFICATION_TYPES = [
        'new_reservation',
        'check_in',
        'check_out',
        'reservation_validated',
        'reservation_validated_no_room',
        'check_in_no_room',
        'area_created',
        'area_state_updated',
        'area_deleted',
    ];

    /**
     * Afficher la page dédiée aux notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all'); // all, unread, read
        $userFilter = $request->get('user_filter', null); // Filtrer par utilisateur (pour les admins)
        
        // Construire la requête selon le rôle
        $query = $this->buildQueryForRole($user, $userFilter);
        
        // Appliquer les filtres
        if ($filter === 'unread') {
            $query->where('read', false);
        } elseif ($filter === 'read') {
            $query->where('read', true);
        }
        
        // Charger la relation user pour afficher les noms
        $query->with('user:id,name,email');
        
        // Pagination (10 par 10)
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        // Statistiques
        $statsQuery = $this->buildQueryForRole($user);
        $stats = [
            'total' => $statsQuery->count(),
            'unread' => $statsQuery->where('read', false)->count(),
            'read' => $statsQuery->where('read', true)->count(),
        ];
        
        // Liste des utilisateurs pour le filtre (seulement pour les admins)
        $receptionists = [];
        if ($user->hasRole('hotel-admin')) {
            $receptionists = User::where('hotel_id', $user->hotel_id)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'receptionist');
                })
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        }
        
        // Logger l'accès (pour les admins qui consultent les notifications d'autres utilisateurs)
        if ($userFilter && $userFilter != $user->id) {
            try {
                NotificationAuditLog::logView($user->id, $userFilter, null, 'list_view');
            } catch (\Exception $e) {
                // Ne pas bloquer l'affichage si le log échoue
                \Log::warning('Erreur log audit notifications: ' . $e->getMessage());
            }
        }
        
        return view('notifications.index', compact('notifications', 'stats', 'filter', 'userFilter', 'receptionists'));
    }
    
    /**
     * Construire la requête selon le rôle de l'utilisateur
     * Les admins peuvent voir les notifications professionnelles des réceptionnistes (lecture seule)
     */
    protected function buildQueryForRole(User $user, ?int $userFilter = null)
    {
        if ($user->hasRole('super-admin')) {
            // Super-admin : voit toutes les notifications professionnelles
            $query = UserNotification::whereIn('type', self::PROFESSIONAL_NOTIFICATION_TYPES);
            
            if ($userFilter) {
                $query->where('user_id', $userFilter);
            }
            
            return $query;
        } elseif ($user->hasRole('hotel-admin')) {
            // Hotel-admin : voit ses notifications + notifications professionnelles des réceptionnistes de son hôtel
            $receptionistIds = User::where('hotel_id', $user->hotel_id)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'receptionist');
                })
                ->pluck('id');
            
            $query = UserNotification::where(function($q) use ($user, $receptionistIds, $userFilter) {
                // Ses propres notifications (tous types)
                $q->where('user_id', $user->id);
                
                // Notifications professionnelles des réceptionnistes
                if ($receptionistIds->isNotEmpty()) {
                    $q->orWhere(function($subQ) use ($receptionistIds) {
                        $subQ->whereIn('user_id', $receptionistIds)
                            ->whereIn('type', self::PROFESSIONAL_NOTIFICATION_TYPES);
            });
                }
            });
            
            // Filtrer par utilisateur spécifique si demandé
            if ($userFilter) {
                if ($userFilter == $user->id || $receptionistIds->contains($userFilter)) {
                    $query->where('user_id', $userFilter);
                } else {
                    // Tentative d'accès non autorisé - retourner seulement ses propres notifications
                    $query->where('user_id', $user->id);
                }
            }
            
            return $query;
        } else {
            // Réceptionniste : voit seulement ses propres notifications
            return UserNotification::where('user_id', $user->id);
        }
    }
    
    /**
     * Marquer toutes les notifications comme lues
     * IMPORTANT : Ne marque que les notifications de l'utilisateur connecté
     * Les admins ne peuvent PAS marquer comme lues les notifications des réceptionnistes
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        // Sécurité : Ne marquer QUE les notifications de l'utilisateur connecté
        // Même si l'admin voit les notifications des réceptionnistes, il ne peut pas les modifier
        $count = UserNotification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
            'read' => true,
            'read_at' => now(),
        ]);
        
        return redirect()->back()->with('success', "{$count} notification(s) marquée(s) comme lue(s).");
    }
    
    /**
     * Charger plus de notifications (AJAX)
     */
    public function loadMore(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $filter = $request->get('filter', 'all');
        $userFilter = $request->get('user_filter', null);
        
        $query = $this->buildQueryForRole($user, $userFilter);
        
        if ($filter === 'unread') {
            $query->where('read', false);
        } elseif ($filter === 'read') {
            $query->where('read', true);
        }
        
        // Charger la relation user
        $query->with('user:id,name,email');
        
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);
        
        return response()->json([
            'notifications' => $notifications->items(),
            'has_more' => $notifications->hasMorePages(),
        ]);
    }
}

