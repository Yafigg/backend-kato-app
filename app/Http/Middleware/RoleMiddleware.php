<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();
        
        // Check if user has any of the required roles
        if (!in_array($user->user_type, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles)
            ], 403);
        }

        // Check if user is verified (except for admin)
        if (!$user->is_verified && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Account not verified. Please contact admin.'
            ], 403);
        }

        return $next($request);
    }
}
