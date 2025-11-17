<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('locales.supported', ['fr', 'ar', 'en']));
        $defaultLocale = config('locales.default', 'fr');

        // Try to get locale from:
        // 1. Request header (Accept-Language or X-Locale)
        // 2. Query parameter (?lang=fr)
        // 3. User preference (if authenticated)
        // 4. Default locale

        $locale = $request->header('X-Locale')
            ?? $request->input('lang')
            ?? (auth()->check() ? auth()->user()->preferred_locale : null)
            ?? $defaultLocale;

        // Validate locale
        if (!in_array($locale, $supportedLocales)) {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
