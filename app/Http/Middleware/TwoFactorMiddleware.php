<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // If a 2FA challenge is pending, hold the user at the verify page.
        if ($request->session()->has('two_factor_user_id')) {
            if (! $request->routeIs('two-factor.*')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
