<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanTwoFactorTerverifikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->twoFactorAktif() && $request->session()->get('two_factor_verified_user_id') !== $user->getKey()) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
