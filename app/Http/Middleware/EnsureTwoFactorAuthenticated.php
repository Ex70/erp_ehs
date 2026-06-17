<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (
            $user &&
            $user->two_factor_enabled &&
            ! $request->session()->get('auth.two_factor_confirmed')
        ) {
            // Guardar URL destino original
            if (! $request->is('two-factor*')) {
                $request->session()->put('url.intended', $request->url());
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}