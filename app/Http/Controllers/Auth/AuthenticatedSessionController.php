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
use App\Services\DeviceDetectionService;

class AuthenticatedSessionController extends Controller
{
    protected SessionManagerService $sessionManager;
    protected DeviceDetectionService $deviceDetectionService;

    public function __construct(SessionManagerService $sessionManager, DeviceDetectionService $deviceDetectionService)
    {
        $this->sessionManager = $sessionManager;
        $this->deviceDetectionService = $deviceDetectionService;
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

        // Vérifier si l'utilisateur veut se souvenir de cet appareil
        $rememberDevice = $request->boolean('remember_device', false);
        
        // Générer l'empreinte digitale de l'appareil
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        // Enregistrer la nouvelle session avec toutes les informations
        $this->sessionManager->registerSession(
            $user,
            $sessionId,
            $request->ip(),
            $request->userAgent(),
            $rememberDevice,
            $deviceFingerprint
        );

        $request->session()->regenerate();

        // Mettre à jour avec le nouvel ID de session après régénération
        $newSessionId = $request->session()->getId();
        $this->sessionManager->registerSession(
            $user,
            $newSessionId,
            $request->ip(),
            $request->userAgent(),
            $rememberDevice,
            $deviceFingerprint
        );

        // Si l'utilisateur veut se souvenir de l'appareil, marquer comme de confiance
        if ($rememberDevice) {
            $this->sessionManager->markAsTrustedDevice($user, $newSessionId);
        }

        Log::info('Nouvelle session créée', [
            'user_id' => $user->id,
            'session_id' => $newSessionId,
            'ip' => $request->ip(),
            'remember_device' => $rememberDevice,
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Générer l'empreinte digitale de l'appareil
     */
    protected function generateDeviceFingerprint(Request $request): string
    {
        return $this->deviceDetectionService->generateDeviceFingerprint(
            $request->userAgent(),
            $request->header('X-Screen-Resolution'),
            $request->header('X-Timezone')
        );
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
