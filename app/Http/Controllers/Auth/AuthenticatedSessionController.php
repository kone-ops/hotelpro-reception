<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SessionManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // Enregistrer la nouvelle session
        $this->sessionManager->registerSession(
            $user,
            $sessionId,
            $request->ip(),
            $request->userAgent()
        );

        $request->session()->regenerate();

        // Mettre à jour avec le nouvel ID de session après régénération
        $newSessionId = $request->session()->getId();
        $this->sessionManager->registerSession(
            $user,
            $newSessionId,
            $request->ip(),
            $request->userAgent()
        );

        Log::info('Nouvelle session créée', [
            'user_id' => $user->id,
            'session_id' => $newSessionId,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // Supprimer la session de la base de données
        if ($user) {
            $this->sessionManager->removeSession($user, $sessionId);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
