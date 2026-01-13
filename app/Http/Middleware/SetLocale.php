<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get language from session, header, or default to 'en'
        $locale = Session::get('app_language') 
            ?? $request->header('Accept-Language')
            ?? $request->get('lang')
            ?? 'en';

        // Validate locale (only allow en or bn)
        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        // Set application locale
        App::setLocale($locale);
        
        // Store in session for persistence
        Session::put('app_language', $locale);

        return $next($request);
    }
}
