<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

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
        // Gérer les erreurs 403 (accès refusé) et les erreurs de rôle
        if ($exception instanceof AuthorizationException || 
            $exception instanceof AccessDeniedHttpException ||
            ($exception->getCode() === 403) ||
            (str_contains($exception->getMessage(), 'does not have the right roles'))) {
            
            $user = auth()->user();
            $errorMessage = $exception->getMessage() ?: 'Accès non autorisé à cette ressource.';
            
            // Si l'utilisateur a une page précédente, le rediriger
            if ($request->hasHeader('Referer') || session()->has('previous_url')) {
                $previousUrl = $request->header('Referer') ?? session('previous_url');
                
                // Vérifier que l'URL précédente est du même domaine
                if ($previousUrl && parse_url($previousUrl, PHP_URL_HOST) === $request->getHost()) {
                    return redirect($previousUrl)
                        ->with('error', $errorMessage);
                }
            }
            
            // Sinon, rediriger vers le dashboard approprié
            if ($user) {
                if ($user->hasRole('super-admin')) {
                    return redirect()->route('super.dashboard')
                        ->with('error', $errorMessage);
                } elseif ($user->hasRole('hotel-admin')) {
                    return redirect()->route('hotel.dashboard')
                        ->with('error', $errorMessage);
                } elseif ($user->hasRole('receptionist')) {
                    return redirect()->route('reception.dashboard')
                        ->with('error', $errorMessage);
                }
            }
            
            // Si pas d'utilisateur, rediriger vers login
            return redirect()->route('login')
                ->with('error', $errorMessage);
        }

        return parent::render($request, $exception);
    }
}

