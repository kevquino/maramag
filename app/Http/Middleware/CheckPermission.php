<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Allow if user is admin (admins have all permissions)
        if (auth()->user()->isAdmin()) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!auth()->user()->hasPermission($permission)) {
            // For Inertia requests, return proper error response
            if ($request->header('X-Inertia')) {
                return Inertia::render('Error', [
                    'status' => 403,
                    'message' => 'Unauthorized action. You do not have permission to access this resource.'
                ])->toResponse($request)->setStatusCode(403);
            }

            abort(403, 'Unauthorized action. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}