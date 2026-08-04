<?php

namespace App\Http\Middleware;

use App\Models\Application;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $application = $request->user('sanctum');

        if (! $application instanceof Application || ! $application->is_active || ! $application->tenant->is_active) {
            abort(403, 'Aplicação ou tenant inativos.');
        }

        return $next($request);
    }
}
