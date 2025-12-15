<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupérer les notifications de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->get('limit', 20);
        $unreadOnly = $request->get('unread_only', false);

        $query = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('read', false);
        }

        $notifications = $query->limit($limit)->get();
        $unreadCount = UserNotification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marquer une notification comme lue
     * IMPORTANT : Seulement pour les notifications de l'utilisateur connecté
     */
    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();
        
        // Sécurité stricte : seulement les notifications de l'utilisateur connecté
        if ($notification->user_id !== $user->id) {
            // Logger la tentative d'accès non autorisé
            \App\Models\NotificationAuditLog::logView(
                $user->id,
                $notification->user_id,
                $notification->id,
                'unauthorized_mark_read_attempt'
            );
            
            return response()->json(['error' => 'Unauthorized - Vous ne pouvez marquer comme lues que vos propres notifications'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'notification' => $notification->fresh(),
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Request $request, UserNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', $request->user()->id)
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Vérifier les opérations en attente (réservations, arrivées, départs)
     */
    public function checkPendingOperations(Request $request): JsonResponse
    {
        $user = $request->user();
        $hotelId = $user->hotel_id;
        
        $now = now();
        $currentHour = $now->hour;
        $isMorningCheck = $currentHour >= 6 && $currentHour < 7; // Entre 6h et 7h
        
        // Vérifier si c'est la première connexion aujourd'hui ou la vérification matinale
        $lastCheckKey = 'last_operations_check_' . $user->id;
        $lastCheckDate = cache()->get($lastCheckKey);
        $today = $now->format('Y-m-d');
        $isFirstCheckToday = !$lastCheckDate || $lastCheckDate !== $today;
        
        // Mettre à jour la date de dernière vérification
        if ($isFirstCheckToday || $isMorningCheck) {
            cache()->put($lastCheckKey, $today, now()->addDay());
        }
        
        $operations = [];
        
        // 1. Réservations en attente
        $pendingReservations = \App\Models\Reservation::where('hotel_id', $hotelId)
            ->where('status', 'pending')
            ->count();
        
        if ($pendingReservations > 0) {
            $operations[] = [
                'type' => 'pending_reservations',
                'icon' => 'warning',
                'title' => 'Réservations en attente',
                'message' => "Vous avez {$pendingReservations} réservation(s) en attente de traitement.",
                'action_text' => "Vérifiez et traitez les réservations en attente de validation.",
                'url' => route('reception.reservations.index', ['status' => 'pending']),
                'priority' => 'high',
                'count' => $pendingReservations
            ];
        }
        
        // 2. Arrivées du jour
        $arrivalsToday = \App\Models\Reservation::where('hotel_id', $hotelId)
            ->whereDate('check_in_date', today())
            ->where('status', '!=', 'checked_in')
            ->count();
        
        if ($arrivalsToday > 0) {
            $operations[] = [
                'type' => 'arrivals_today',
                'icon' => 'info',
                'title' => 'Arrivées prévues aujourd\'hui',
                'message' => "{$arrivalsToday} client(s) arrivent aujourd'hui. Préparez l'accueil.",
                'action_text' => "Vérifiez les arrivées du jour et préparez les chambres.",
                'url' => route('reception.dashboard'),
                'priority' => 'high',
                'count' => $arrivalsToday
            ];
        }
        
        // 3. Départs du jour
        $departuresToday = \App\Models\Reservation::where('hotel_id', $hotelId)
            ->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->count();
        
        if ($departuresToday > 0) {
            $operations[] = [
                'type' => 'departures_today',
                'icon' => 'info',
                'title' => 'Départs prévus aujourd\'hui',
                'message' => "{$departuresToday} client(s) partent aujourd'hui. Préparez les départs.",
                'action_text' => "Vérifiez les départs du jour et préparez les factures.",
                'url' => route('reception.dashboard'),
                'priority' => 'medium',
                'count' => $departuresToday
            ];
        }
        
        // 4. Notifications non lues
        $unreadNotifications = UserNotification::where('user_id', $user->id)
            ->where('read', false)
            ->count();
        
        if ($unreadNotifications > 0) {
            $operations[] = [
                'type' => 'unread_notifications',
                'icon' => 'info',
                'title' => 'Notifications non lues',
                'message' => "Vous avez {$unreadNotifications} notification(s) non lue(s).",
                'action_text' => "Consultez vos notifications pour ne rien manquer.",
                'url' => '#',
                'priority' => 'low',
                'count' => $unreadNotifications
            ];
        }
        
        // 5. Opérations non effectuées depuis plus de 24h
        if ($isMorningCheck || $isFirstCheckToday) {
            $oldPendingReservations = \App\Models\Reservation::where('hotel_id', $hotelId)
                ->where('status', 'pending')
                ->where('created_at', '<', now()->subDay())
                ->count();
            
            if ($oldPendingReservations > 0) {
                $operations[] = [
                    'type' => 'old_pending_reservations',
                    'icon' => 'error',
                    'title' => '⚠️ Opérations non effectuées',
                    'message' => "{$oldPendingReservations} réservation(s) en attente depuis plus de 24h nécessitent votre attention.",
                    'action_text' => "Traitez rapidement ces réservations pour éviter les retards.",
                    'url' => route('reception.reservations.index', ['status' => 'pending']),
                    'priority' => 'critical',
                    'count' => $oldPendingReservations
                ];
            }
        }
        
        return response()->json([
            'operations' => $operations,
            'has_operations' => count($operations) > 0,
            'is_morning_check' => $isMorningCheck,
            'is_first_check_today' => $isFirstCheckToday,
            'timestamp' => $now->toIso8601String()
        ]);
    }
}
