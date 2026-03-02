<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales supportées par l'application.
     */
    public static function supportedLocales(): array
    {
        return config('app.supported_locales', ['fr', 'en']);
    }

    /**
     * Détermine la locale à utiliser (session > utilisateur > Accept-Language > défaut).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locales = self::supportedLocales();
        $locale = null;

        // 1. Préférence en session (choix explicite de l'utilisateur)
        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');
            if (in_array($sessionLocale, $locales, true)) {
                $locale = $sessionLocale;
            }
        }

        // 2. Préférence utilisateur (si colonne locale sur User)
        if ($locale === null && $request->user() && method_exists($request->user(), 'getPreferredLocale')) {
            $userLocale = $request->user()->getPreferredLocale();
            if ($userLocale && in_array($userLocale, $locales, true)) {
                $locale = $userLocale;
            }
        }

        // 3. En-tête Accept-Language du navigateur
        if ($locale === null && $request->hasHeader('Accept-Language')) {
            $locale = $this->matchPreferredLocale($request->header('Accept-Language'), $locales);
        }

        // 4. Locale par défaut de l'application
        if ($locale === null) {
            $locale = config('app.locale', 'fr');
        }

        if (! in_array($locale, $locales, true)) {
            $locale = config('app.fallback_locale', 'fr');
        }

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Trouve la première locale supportée dans la chaîne Accept-Language.
     */
    private function matchPreferredLocale(string $acceptLanguage, array $supported): ?string
    {
        $parts = array_map('trim', explode(',', $acceptLanguage));
        foreach ($parts as $part) {
            $code = strtolower(explode(';', $part)[0]);
            $code = preg_replace('/-.*/', '', $code); // fr-FR -> fr
            if (in_array($code, $supported, true)) {
                return $code;
            }
        }

        return null;
    }
}
