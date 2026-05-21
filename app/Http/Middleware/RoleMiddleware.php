<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Le rôle requis pour accéder à la route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifie si l'utilisateur est authentifié
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Vérification du rôle
        // 'super_admin' a accès à tout (admin)
        // Les autres rôles sont 'joueur' et 'mairie'
        $userRole = $request->user()->role;

        if ($userRole === 'super_admin' || $userRole === $role) {
            return $next($request);
        }

        // Redirection si le rôle ne correspond pas
        return redirect()->route('dashboard')->with('error', "Vous n'avez pas l'autorisation d'accéder à cette page.");
    }
}
