<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            TenantContext::clear();
            return $next($request);
        }

        $user = Auth::user();

        if ($user->business_id === null) {
            TenantContext::clear();

            if (!$request->routeIs('onboarding*') && !$request->routeIs('logout')) {
                return redirect()->route('onboarding');
            }

            return $next($request);
        }

        $business = $user->business;

        if (!$business || $business->status !== 'active') {
            Auth::logout();
            TenantContext::clear();
            return redirect()->route('login')->withErrors([
                'email' => 'Your business account is suspended or inactive.',
            ]);
        }

        TenantContext::setTenant($business);

        if ($request->routeIs('onboarding*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
