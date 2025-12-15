<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Liste des routes à exclure du logging
     */
    protected $excludedPaths = [
        'login',
        'logout',
        'password',
        'sanctum',
        '_debugbar',
        'livewire',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Logger uniquement les actions importantes (POST, PUT, PATCH, DELETE) avec succès (2xx)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) 
            && $response->getStatusCode() >= 200 
            && $response->getStatusCode() < 300) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Logger l'activité dans la base de données
     */
    protected function logActivity(Request $request, Response $response): void
    {
        // Ne pas logger les routes exclues
        if ($this->shouldExclude($request->path())) {
            return;
        }

        $user = Auth::user();
        $path = $request->path();
        $method = $request->method();
        
        // Déterminer la description de l'activité
        $description = $this->generateDescription($path, $method);
        
        if (!$description) {
            return; // Ne pas logger si on ne peut pas déterminer l'action
        }

        // Récupérer le nom de l'hôtel si disponible
        $hotelName = null;
        if ($user && $user->hotel) {
            $hotelName = $user->hotel->name;
        }

        try {
            ActivityLog::create([
                'log_name' => 'application',
                'description' => $description,
                'subject_type' => $this->getSubjectType($path),
                'subject_id' => $this->getSubjectId($path),
                'causer_type' => $user ? get_class($user) : null,
                'causer_id' => $user?->id,
                'properties' => [
                    'method' => $method,
                    'path' => $path,
                    'hotel_name' => $hotelName,
                    'user_name' => $user?->name,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                ],
                'event' => $this->getEventType($method),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Logger l'erreur sans interrompre la requête
            Log::error('Erreur lors du logging d\'activité', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);
        }
    }

    /**
     * Générer une description lisible de l'activité
     */
    protected function generateDescription(string $path, string $method): ?string
    {
        // Hotels
        if (preg_match('#^super/hotels/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return "Hôtel modifié";
        }
        if (preg_match('#^super/hotels$#', $path) && $method === 'POST') {
            return "Nouvel hôtel créé";
        }
        if (preg_match('#^super/hotels/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return "Hôtel supprimé";
        }

        // Users
        if (preg_match('#^super/users/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return "Utilisateur modifié";
        }
        if (preg_match('#^super/users$#', $path) && $method === 'POST') {
            return "Nouvel utilisateur créé";
        }
        if (preg_match('#^super/users/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return "Utilisateur supprimé";
        }

        // Reservations
        if (preg_match('#reservations/(\d+)/validate#', $path)) {
            return "Réservation validée";
        }
        if (preg_match('#reservations/(\d+)/reject#', $path)) {
            return "Réservation rejetée";
        }
        if (preg_match('#^(hotel|reception)/reservations$#', $path) && $method === 'POST') {
            return "Nouvelle réservation créée";
        }
        if (preg_match('#^(hotel|reception)/reservations/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return "Réservation modifiée";
        }
        if (preg_match('#^(hotel|reception)/reservations/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return "Réservation supprimée";
        }

        // Rooms
        if (preg_match('#^hotel/rooms$#', $path) && $method === 'POST') {
            return "Nouvelle chambre créée";
        }
        if (preg_match('#^hotel/rooms/bulk-store#', $path) && $method === 'POST') {
            return "Chambres créées en lot";
        }
        if (preg_match('#^hotel/rooms/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return "Chambre modifiée";
        }
        if (preg_match('#^hotel/rooms/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return "Chambre supprimée";
        }
        if (preg_match('#^hotel/rooms/(\d+)/status#', $path)) {
            return "Statut de chambre modifié";
        }

        // Room Types
        if (preg_match('#^hotel/room-types$#', $path) && $method === 'POST') {
            return "Nouveau type de chambre créé";
        }
        if (preg_match('#^hotel/room-types/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return "Type de chambre modifié";
        }
        if (preg_match('#^hotel/room-types/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return "Type de chambre supprimé";
        }

        // Hotel Data Operations
        if (preg_match('#^super/hotel-data/(\d+)/reset#', $path)) {
            return "Données hôtel réinitialisées";
        }
        if (preg_match('#^super/hotel-data/(\d+)/purge#', $path)) {
            return "Purge complète de l'hôtel effectuée";
        }
        if (preg_match('#^super/hotel-data/(\d+)/import#', $path)) {
            return "Importation de données hôtel";
        }

        return null;
    }

    /**
     * Obtenir le type de sujet (modèle)
     */
    protected function getSubjectType(string $path): ?string
    {
        if (str_contains($path, 'hotels')) {
            return 'App\\Models\\Hotel';
        }
        if (str_contains($path, 'users')) {
            return 'App\\Models\\User';
        }
        if (str_contains($path, 'reservations')) {
            return 'App\\Models\\Reservation';
        }
        if (str_contains($path, 'room-types')) {
            return 'App\\Models\\RoomType';
        }
        if (str_contains($path, 'rooms')) {
            return 'App\\Models\\Room';
        }
        
        return null;
    }

    /**
     * Obtenir l'ID du sujet depuis le path
     */
    protected function getSubjectId(string $path): ?int
    {
        // Extraire l'ID numérique du path (ex: /hotels/123 -> 123)
        if (preg_match('#/(\d+)(?:/|$)#', $path, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    /**
     * Obtenir le type d'événement selon la méthode HTTP
     */
    protected function getEventType(string $method): string
    {
        return match($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'modified',
        };
    }

    /**
     * Vérifier si le path doit être exclu
     */
    protected function shouldExclude(string $path): bool
    {
        foreach ($this->excludedPaths as $excludedPath) {
            if (str_contains($path, $excludedPath)) {
                return true;
            }
        }
        
        return false;
    }
}

