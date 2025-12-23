<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Gérer les erreurs Spatie Permission (403)
        if ($exception instanceof UnauthorizedException) {
            return $this->handleUnauthorizedException($request, $exception);
        }

        // Gérer les erreurs 403 (accès refusé) et les erreurs de rôle
        if ($exception instanceof AuthorizationException || 
            $exception instanceof AccessDeniedHttpException ||
            ($exception->getCode() === 403) ||
            (str_contains($exception->getMessage(), 'does not have the right roles')) ||
            (str_contains($exception->getMessage(), 'does not have the right permissions'))) {
            
            return $this->handleAuthorizationException($request, $exception);
        }

        // Gérer les erreurs 404
        if ($exception instanceof NotFoundHttpException) {
            // Log pour debugging si nécessaire
            if (config('app.debug')) {
                Log::info('Page non trouvée', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => Auth::id(),
                ]);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Gérer les exceptions UnauthorizedException de Spatie
     */
    protected function handleUnauthorizedException($request, UnauthorizedException $exception)
    {
        $user = Auth::user();
        $errorMessage = $exception->getMessage() ?: 'Accès non autorisé à cette ressource.';
        
        // Logging détaillé de la tentative d'accès refusé
        Log::warning('Tentative d\'accès refusé (Spatie Permission)', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_roles' => $user ? $user->getRoleNames()->toArray() : [],
            'required_roles' => $exception->getRequiredRoles(),
            'required_permissions' => $exception->getRequiredPermissions(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
        ]);

        // Si la requête est AJAX, retourner une réponse JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $errorMessage,
                'error' => 'Forbidden',
                'code' => 403,
            ], 403);
        }

        // Pour les requêtes normales, afficher la page d'erreur 403
        return response()->view('errors.403', [
            'exception' => $exception,
        ], 403);
    }

    /**
     * Gérer les exceptions d'autorisation générales
     */
    protected function handleAuthorizationException($request, Throwable $exception)
    {
        $user = Auth::user();
        $errorMessage = $exception->getMessage() ?: 'Accès non autorisé à cette ressource.';
        
        // Logging détaillé
        Log::warning('Tentative d\'accès refusé', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_roles' => $user ? $user->getRoleNames()->toArray() : [],
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'exception_message' => $exception->getMessage(),
            'exception_class' => get_class($exception),
        ]);

        // Si la requête est AJAX, retourner une réponse JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $errorMessage,
                'error' => 'Forbidden',
                'code' => 403,
            ], 403);
        }

        // Protection contre les boucles de redirection
        $currentRoute = $request->route()?->getName();
        $dashboardRoutes = ['super.dashboard', 'hotel.dashboard', 'reception.dashboard'];
        
        if (in_array($currentRoute, $dashboardRoutes)) {
            // Si on est déjà sur un dashboard, afficher la page d'erreur
            return response()->view('errors.403', [
                'exception' => $exception,
            ], 403);
        }

        // Essayer de rediriger vers la page précédente si elle existe et est sûre
        if ($request->hasHeader('Referer') || session()->has('previous_url')) {
            $previousUrl = $request->header('Referer') ?? session('previous_url');
            
            if ($previousUrl && parse_url($previousUrl, PHP_URL_HOST) === $request->getHost()) {
                return redirect($previousUrl)
                    ->with('error', $errorMessage);
            }
        }
        
        // Rediriger vers le dashboard approprié selon le rôle
        if ($user) {
            $dashboardRoute = null;
            if ($user->hasRole('super-admin')) {
                $dashboardRoute = 'super.dashboard';
            } elseif ($user->hasRole('hotel-admin')) {
                $dashboardRoute = 'hotel.dashboard';
            } elseif ($user->hasRole('receptionist')) {
                $dashboardRoute = 'reception.dashboard';
            }

            if ($dashboardRoute) {
                return redirect()->route($dashboardRoute)
                    ->with('error', $errorMessage);
            }
        }
        
        // Si pas d'utilisateur ou pas de dashboard, afficher la page d'erreur
        return response()->view('errors.403', [
            'exception' => $exception,
        ], 403);
    }
}

