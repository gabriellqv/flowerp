<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Bloqueia o acesso se o perfil (role) do usuario nao estiver na lista permitida.
     *
     * @param  Request  $request  Request atual
     * @param  Closure  $next  Proximo middleware
     * @param  string  ...$roles  Lista de roles permitidas (ex: 'admin', 'manager')
     * @return Response Retorna 403 se negado, senao continua
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Acesso negado. Permissao insuficiente.',
                'required_roles' => $roles,
                'your_role' => $user?->role ?? 'unauthenticated',
            ], 403);
        }

        return $next($request);
    }
}
