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
    public function index(Request $request)
    {
        $user = auth()->user();
        $myTeams = $user->teams()->withCount('members')->get();
        $allTeams = Team::withCount('members')->take(10)->get();
        $cityId = $request->query('city_id');
        $city = $cityId ? City::find($cityId) : null;

        return Inertia::render('Player/Teams/Index', [
            'myTeams' => $myTeams,
            'allTeams' => $allTeams,
            'city' => $city,
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

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with('success', 'Équipe créée avec succès !');
        }

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

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with('success', "Vous avez rejoint l'équipe {$team->name} !");
        }

        return redirect()->route('teams.index')->with('success', "Vous avez rejoint l'équipe {$team->name} !");
    }

    public function joinWithCode(Request $request)
    {
        $code = $request->query('code');
        
        if (!$code) {
            return redirect()->route('teams.index')->with('error', 'Code d\'invitation manquant.');
        }

        $team = Team::where('invite_code', $code)->first();

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'Code d\'invitation invalide.');
        }

        if ($team->members()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('teams.index')->with('info', 'Vous êtes déjà membre de cette équipe.');
        }

        if ($team->members()->count() >= $team->member_limit) {
            return redirect()->route('teams.index')->with('error', 'Cette équipe est déjà complète.');
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

        // Récupérer les lieux de la ville pour créer une séquence de découverte
        $locations = $city->locations()->get();
        $sequence = $locations->pluck('id')->toArray();

        if (empty($sequence)) {
            return back()->with('error', 'Cette ville n\'a pas encore de missions disponibles.');
        }

        // Récupérer la première énigme du premier lieu
        $firstLocationId = $sequence[0];
        $firstEnigma = \App\Models\Enigma::where('location_id', $firstLocationId)
            ->where('is_site_specific', false)
            ->first();

        // Créer ou mettre à jour une session de jeu pour l'équipe
        $session = GameSession::updateOrCreate(
            [
                'team_id' => $team->id, 
                'city_id' => $city->id, 
                'status' => 'in_progress'
            ],
            [
                'start_time' => now(),
                'discovery_sequence' => $sequence,
                'current_location_id' => $firstLocationId,
                'current_enigma_id' => $firstEnigma?->id ?? \App\Models\Enigma::where('location_id', $firstLocationId)->first()?->id,
            ]
        );

        // Envoyer une notification aux autres membres de l'équipe
        $members = $team->members()->where('user_id', '!=', auth()->id())->get();
        foreach ($members as $member) {
            \App\Models\Notification::create([
                'user_id' => $member->id,
                'type' => 'squad_deployment',
                'message' => auth()->user()->name . " a lancé une mission d'escouade à " . $city->name . " !",
                'data' => [
                    'city_id' => $city->id,
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'action_url' => route('player.game', ['city' => $city->id, 'team_id' => $team->id]),
                ],
            ]);
        }

        return redirect()->route('player.game', ['city' => $city->id, 'team_id' => $team->id]);
    }
}
