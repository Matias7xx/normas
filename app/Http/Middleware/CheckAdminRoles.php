<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckAdminRoles
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Roles permitidas na área administrativa
        $rolesPermitidas = [1, 2, 3, 7];
        $userRoleId = Auth::user()->role_id;

        // Verificar se o usuário tem uma das roles permitidas
        if (!in_array($userRoleId, $rolesPermitidas)) {
            // Para requisições AJAX/JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso negado',
                    'message' => 'Você não possui permissão para acessar esta área.'
                ], 403);
            }

            // Para requisições normais - renderizar a página atual com mensagem flash
            return Inertia::render($request->route()->getName() ?: 'public.home', [
                'error' => 'Acesso negado. Você não possui permissão para acessar a área administrativa.',
                'alert_type' => 'error'
            ])->with(['error', 'alert_type']);
        }

        return $next($request);
    }
}