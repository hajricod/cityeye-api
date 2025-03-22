<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role->value, $roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
