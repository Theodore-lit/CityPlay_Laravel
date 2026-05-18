<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Team;
use App\Models\City;
use App\Models\GameSession;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $myTeams = $user->teams()->withCount('members')->get();
        $allTeams = Team::withCount('members')->take(10)->get();

        return Inertia::render('Player/Teams/Index', [
            'myTeams' => $myTeams,
            'allTeams' => $allTeams,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create([
            'name' => $request->name,
            'creator_id' => auth()->id(),
            'invite_code' => strtoupper(Str::random(6)),
            'member_limit' => 5,
        ]);

        $team->members()->attach(auth()->id(), ['role' => 'leader']);

        return redirect()->route('teams.index')->with('success', 'Équipe créée avec succès !');
    }

    public function join(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string',
        ]);

        $team = Team::where('invite_code', $request->invite_code)->first();

        if (!$team) {
            return back()->with('error', 'Code d\'invitation invalide.');
        }

        if ($team->members()->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'Vous êtes déjà membre de cette équipe.');
        }

        if ($team->members()->count() >= $team->member_limit) {
            return back()->with('error', 'Cette équipe est déjà complète.');
        }

        $team->members()->attach(auth()->id(), ['role' => 'member']);

        return redirect()->route('teams.index')->with('success', "Vous avez rejoint l'équipe {$team->name} !");
    }

    public function show(Team $team)
    {
        return Inertia::render('Player/Teams/Show', [
            'team' => $team->load('members', 'gameSessions.city'),
            'availableCities' => City::where('is_active', true)->get(),
        ]);
    }

    public function startQuest(Request $request, Team $team, City $city)
    {
        // Vérifier si l'utilisateur est membre de l'équipe
        if (!$team->members()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        // Créer une session de jeu pour l'équipe
        $session = GameSession::create([
            'team_id' => $team->id,
            'city_id' => $city->id,
            'start_time' => now(),
            'status' => 'in_progress',
            'current_enigma_id' => $city->locations()->first()->enigmas()->first()->id ?? null,
        ]);

        return redirect()->route('player.game', ['city' => $city->id, 'team_id' => $team->id]);
    }
}
