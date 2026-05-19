<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response|RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user && $user->role == 'joueur' && !$user->is_verified) {
            return Inertia::render('Auth/Register', [
                'status' => 'otp_sent',
                'email' => $user->email,
            ]);
        }

        $request->authenticate();

        // Check if user is active
        if (!auth()->user()->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Votre compte est archivé ou désactivé. Veuillez contacter un administrateur pour le réactiver.',
            ]);
        }

        $request->session()->regenerate();

        // Update last activity
        $user = auth()->user();
        $user->last_activity_at = now();
        $user->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if ($user->role == 'joueur') {
            $user->is_active = $request->input('deactivate_on_logout') ? false : true;
            $user->save();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
