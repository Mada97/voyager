<?php

namespace App\Http\Middleware;

use Closure;

class ApiGuestUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth('api')->check()) {
            return $next($request);
        } else {
            return response()->json(['status' => 'Unauthenticated', 'message' => 'You must be logged in'], 401);
        }
    }
}
