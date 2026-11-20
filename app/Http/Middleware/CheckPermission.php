<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Exemplo:
     * ->middleware('permission:patients.view')
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'Você não possui permissão para acessar este recurso.');
        }

        return $next($request);
    }
}