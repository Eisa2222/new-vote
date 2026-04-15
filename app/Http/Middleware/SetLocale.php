<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale')
            ?? $request->session()->get('locale')
            ?? $request->getPreferredLanguage(['ar', 'en'])
            ?? config('app.locale');

        if (in_array($locale, ['ar', 'en'], true)) {
            app()->setLocale($locale);
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        }

        return $next($request);
    }
}
