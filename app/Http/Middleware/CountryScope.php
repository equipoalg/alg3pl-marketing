<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CountryScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->country_id) {
            // User is scoped to a specific country
            app()->instance('current_country_id', $user->country_id);
        } else {
            // Admin or global user — no country filter
            app()->instance('current_country_id', null);
        }

        return $next($request);
    }
}
