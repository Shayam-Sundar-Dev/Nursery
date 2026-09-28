<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasRole
{
    /**
     * Handle an incoming request checking for role authorization.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Active administrator access required.',
                ], 403);
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', 'Please sign in with authorized administrator credentials.');
        }

        // If specific roles are specified, check if the user has any of them
        if (! empty($roles) && ! $user->hasRole(...$roles)) {
            $roleLabel = $user->roleLabel();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "Access denied. Your role ({$roleLabel}) does not have permission to access this resource.",
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', "Access restricted: Your role ({$roleLabel}) does not have permission to perform that action.");
        }

        return $next($request);
    }
}
