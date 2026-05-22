<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityEvent;
use App\Models\Competition;
use App\Models\UserPrize;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CompetitionController extends Controller
{
    /**
     * Liste les compétitions pour un événement donné (Admin/Mairie).
     */
    public function index(CityEvent $event)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        return Inertia::render('Mairie/Competitions', [
            'event' => $event->load('city'),
            'competitions' => Competition::where('city_event_id', $event->id)->latest()->get(),
        ]);
    }

    /**
     * Enregistre une nouvelle compétition ou met à jour une existante.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:competitions,id',
            'city_event_id' => 'required|exists:city_events,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:ranking,fixed',
            'objective_type' => 'required|in:xp,hearts,diamonds',
            'goal_amount' => 'nullable|required_if:type,fixed|integer|min:1',
            'ranking_limit' => 'nullable|required_if:type,ranking|integer|in:1,3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->id) {
            $competition = Competition::findOrFail($request->id);
            $competition->update($validated);
        } else {
            Competition::create($validated);
        }

        return redirect()->back()->with('success', 'Protocole de compétition enregistré.');
    }

    /**
     * Supprime une compétition.
     */
    public function destroy(Competition $competition)
    {
        $competition->delete();
        return redirect()->back()->with('success', 'Compétition supprimée.');
    }

    /**
     * Vue joueur pour les compétitions.
     */
    public function playerIndex()
    {
        $user = auth()->user();
        $competitions = Competition::where('is_active', true)
            ->where('end_date', '>=', now())
            ->with(['cityEvent.city'])
            ->get()
            ->map(function($comp) use ($user) {
                $participant = $comp->participants()->where('user_id', $user->id)->first();
                $comp->is_participating = !!$participant;
                $comp->progress = $comp->getProgress($user);
                return $comp;
            });

        return Inertia::render('Player/Competitions', [
            'competitions' => $competitions
        ]);
    }

    /**
     * Affiche les détails d'une compétition (Arène).
     */
    public function show(Competition $competition)
    {
        $user = auth()->user();
        $competition->load(['cityEvent.city']);
        
        $participant = $competition->participants()->where('user_id', $user->id)->first();
        
        // Classement des participants
        $leaderboard = $competition->participants()
            ->orderByPivot('current_amount', 'desc')
            ->get()
            ->map(function($p, $index) {
                $p->rank = $index + 1;
                return $p;
            });

        return Inertia::render('Player/CompetitionArena', [
            'competition' => $competition,
            'leaderboard' => $leaderboard,
            'userProgress' => $participant ? $participant->pivot->current_amount : 0,
            'isParticipating' => !!$participant,
        ]);
    }

    /**
     * Inscrit un joueur à une compétition.
     */
    public function join(Competition $competition)
    {
        $user = auth()->user();
        
        if (!$competition->participants()->where('user_id', $user->id)->exists()) {
            $competition->participants()->attach($user->id, ['current_amount' => 0]);
        }

        return redirect()->route('player.competitions.show', $competition->id)
            ->with('success', 'Déploiement réussi : Vous participez à la compétition.');
    }

    /**
     * "Charge" l'objectif pour augmenter son score.
     */
    public function charge(Request $request, Competition $competition)
    {
        $user = auth()->user();
        $amountToAdd = $request->input('amount', 1); // Montant à charger

        // Vérifier si l'utilisateur a assez de ressources
        $type = $competition->objective_type; // 'xp', 'hearts', 'diamonds'
        
        if ($user->$type < $amountToAdd) {
            return back()->with('error', "Ressources insuffisantes ({$type}) pour charger l'objectif.");
        }

        DB::transaction(function () use ($user, $competition, $amountToAdd, $type) {
            // Déduire les ressources de l'utilisateur
            $user->decrement($type, $amountToAdd);

            // Augmenter le score dans la compétition
            $participant = $competition->participants()->where('user_id', $user->id)->first();
            $newAmount = $participant->pivot->current_amount + $amountToAdd;
            
            $competition->participants()->updateExistingPivot($user->id, [
                'current_amount' => $newAmount
            ]);

            // Vérifier si l'objectif est atteint (pour le type 'fixed')
            if ($competition->type === 'fixed' && $newAmount >= $competition->goal_amount && !$participant->pivot->is_winner) {
                $competition->participants()->updateExistingPivot($user->id, ['is_winner' => true]);
                
                // Créer le lot (prize)
                UserPrize::create([
                    'user_id' => $user->id,
                    'competition_id' => $competition->id,
                    'title' => "Victoire : {$competition->title}",
                    'description' => "Vous avez atteint l'objectif de {$competition->goal_amount} {$competition->objective_type} !",
                    'reward_type' => 'victory_pack',
                    'reward_value' => 100, // Valeur arbitraire
                    'qr_code_data' => "COMP-{$competition->id}-USER-{$user->id}-" . uniqid(),
                ]);

                // Envoyer une notification de félicitations
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'reward',
                    'message' => "Félicitations ! Vous avez remporté la compétition '{$competition->title}'. Votre lot vous attend dans votre coffre.",
                    'link' => route('player.rewards'),
                    'read_at' => null,
                ]);
            }
        });

        return back()->with('success', "Objectif chargé (+{$amountToAdd} {$competition->objective_type}).");
    }
}
