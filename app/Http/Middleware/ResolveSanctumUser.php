<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Populates $request->user() from a Sanctum bearer token WHEN one is present
 * and valid, but never blocks the request when it's missing or invalid —
 * unlike the auth:sanctum guard middleware, which 401s on no token. Cart and
 * checkout routes need this: they serve both guest and authenticated users,
 * and only branch on identity inside the controller/service layer.
 */
class ResolveSanctumUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
