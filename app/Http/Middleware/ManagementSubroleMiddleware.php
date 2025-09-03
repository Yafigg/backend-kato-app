<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagementSubroleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$subroles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();
        
        // Check if user is management
        if (!$user->isManagement()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Required role: management'
            ], 403);
        }

        // Check if user has any of the required subroles
        if (!in_array($user->management_subrole, $subroles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Required subrole: ' . implode(' or ', $subroles)
            ], 403);
        }

        return $next($request);
    }
}
