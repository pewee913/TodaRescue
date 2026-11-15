<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Ensure the authenticated user is an admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_admin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Admin access required.'], 403);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Admin access required. Please log in.');
        }

        return $next($request);
    }
}