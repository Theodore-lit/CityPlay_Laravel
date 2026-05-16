<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MairieController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Mairie/Dashboard', [
            'cities' => City::where('creator_id', auth()->id())->withCount('locations')->get(),
            'stats' => [
                'total_sessions' => GameSession::count(),
                'active_players' => GameSession::where('status', 'in_progress')->count(),
            ]
        ]);
    }

    public function toggleStatus(City $city)
    {
        $city->is_active = !$city->is_active;
        $city->save();

        return redirect()->back()->with('success', 'Statut de la ville mis à jour.');
    }
}
