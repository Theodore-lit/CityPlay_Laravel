<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Vérifier si le compte est désactivé manuellement
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Votre compte a été désactivé par un administrateur.');
            }

            // Logique d'inactivité pour les joueurs (2 mois)
            if ($user->role === 'player') {
                $lastActivity = $user->last_active_at ?? $user->created_at;
                
                if (Carbon::parse($lastActivity)->addMonths(2)->isPast()) {
                    $user->update(['is_active' => false]);
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'Votre compte a été désactivé pour inactivité (2 mois).');
                }

                // Mettre à jour la dernière activité
                $user->update([
                    'last_active_at' => now(),
                    // Si le compte était marqué pour expiration, on peut réinitialiser ou laisser
                    // Mais ici on met à jour l'activité
                ]);
            } else {
                // Pour les autres rôles, on met juste à jour l'activité
                $user->update(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
