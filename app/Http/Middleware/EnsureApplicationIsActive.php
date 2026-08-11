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

        if (! $application instanceof Application) {
            abort(403, 'Aplicação não autenticada.');
        }

        if (! $application->is_active) {
            abort(403, 'Aplicação inativa.');
        }

        if (! $application->tenant || ! $application->tenant->is_active) {
            abort(403, 'Tenant inativo.');
        }

        return $next($request);
    }
}
