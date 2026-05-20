<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Team;
use App\Models\City;
use App\Models\GameSession;
use App\Models\Notification;
use App\Models\Enigma;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Liste les équipes de l'utilisateur et les équipes publiques.
     */
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

    /**
     * Crée une nouvelle équipe et définit le créateur comme leader.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create([
            'name' => $request->name,
            'creator_id' => auth()->id(),
            'invite_code' => strtoupper(Str::random(6)), // Génération d'un code court
            'member_limit' => 5,
        ]);

        $team->members()->attach(auth()->id(), ['role' => 'leader']);

        return redirect()->route('teams.index')->with('success', 'Équipe créée avec succès !');
    }

    /**
     * Permet à un joueur de rejoindre une équipe via un code d'invitation.
     */
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

    /**
     * Affiche les détails d'une équipe spécifique.
     */
    public function show(Team $team)
    {
        return Inertia::render('Player/Teams/Show', [
            'team' => $team->load('members', 'gameSessions.city'),
            'availableCities' => City::where('is_active', true)->get(),
        ]);
    }

    /**
     * Lance une quête pour toute l'équipe. 
     * Calcule une séquence de lieux optimisée par distance.
     */
    public function startQuest(Request $request, Team $team, City $city)
    {
        // Vérifier si l'utilisateur est membre de l'équipe
        if (!$team->members()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        $availableLocations = $city->locations()->get()->collect();
        $sequence = [];

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Logique de calcul du chemin le plus court (Nearest Neighbor)
        if ($lat && $lng && $availableLocations->isNotEmpty()) {
            $currentLat = $lat;
            $currentLng = $lng;

            while ($availableLocations->isNotEmpty()) {
                $closest = $availableLocations->sortBy(function ($location) use ($currentLat, $currentLng) {
                    $lat1 = deg2rad($currentLat);
                    $lon1 = deg2rad($currentLng);
                    $lat2 = deg2rad($location->latitude);
                    $lon2 = deg2rad($location->longitude);
                    return 6371000 * 2 * atan2(sqrt(pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon2 - $lon1) / 2), 2)), sqrt(1 - (pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon2 - $lon1) / 2), 2))));
                })->first();

                $sequence[] = $closest->id;
                $currentLat = $closest->latitude;
                $currentLng = $closest->longitude;
                $availableLocations = $availableLocations->reject(fn($l) => $l->id === $closest->id);
            }
        } else {
            $sequence = $availableLocations->pluck('id')->toArray();
        }

        // Création de la session d'équipe
        $session = GameSession::updateOrCreate(
            ['team_id' => $team->id, 'city_id' => $city->id, 'status' => 'in_progress'],
            [
                'start_time' => now(),
                'discovery_sequence' => $sequence,
                'current_location_id' => $sequence[0] ?? null,
                'current_enigma_id' => Enigma::where('location_id', $sequence[0] ?? null)
                    ->where('is_site_specific', false)
                    ->first()->id ?? null,
            ]
        );

        // Notification des coéquipiers avec lien direct vers la partie
        $membersToNotify = $team->members()->where('users.id', '!=', auth()->id())->get();
        foreach ($membersToNotify as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'team_game_start',
                'message' => "Votre équipe {$team->name} a commencé une partie à {$city->name} !",
                'link' => route('teams.join-game', ['team' => $team->id, 'city' => $city->id]),
            ]);
        }

        return redirect()->route('player.game', $city->id)->with('success', "L'aventure en équipe a démarré ! Les autres membres ont été notifiés.");
    }

    /**
     * Permet à un membre de rejoindre une partie d'équipe déjà lancée.
     */
    public function joinGame(Team $team, City $city)
    {
        $user = auth()->user();

        // Si l'utilisateur n'est pas dans l'équipe, on l'ajoute d'abord
        if (!$team->members()->where('user_id', $user->id)->exists()) {
            if ($team->members()->count() >= $team->member_limit) {
                return redirect()->route('teams.index')->with('error', 'Cette équipe est déjà complète.');
            }
            $team->members()->attach($user->id, ['role' => 'member']);
        }

        // Vérifier si une session est en cours
        $session = GameSession::where('team_id', $team->id)
            ->where('city_id', $city->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            // Si pas de session, on en crée une (le membre rejoint et lance la partie)
            return $this->startQuest(request(), $team, $city);
        }

        return redirect()->route('player.game', $city->id)->with('success', "Vous avez rejoint la partie en cours avec l'équipe {$team->name} !");
    }
}
