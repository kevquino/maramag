<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNewsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user() || !auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        return $next($request);
    }
}