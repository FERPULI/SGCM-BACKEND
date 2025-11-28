<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Si no se pasan roles, permitir (o bloquear según necesites)
        if (empty($roles)) {
            return $next($request);
        }

        // $roles viene como array de strings
        if (! in_array($user->rol, $roles)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return $next($request);
    }
}
