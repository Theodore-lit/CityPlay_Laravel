<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Inertia\Inertia;
use App\Models\CityEvent;
use Illuminate\Http\Request;
use App\Models\EventRedemption;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RewardsController extends Controller
{
    /**
     * Affiche la page des récompenses avec le solde du joueur
     * et la liste des événements proposant des pass.
     * Logique centrale pour la gestion des inventaires et récompenses joueurs.
     *
     * Kamal
     */
    public function index()
    {
        $user = auth()->user();

        // Récupération des événements actifs proposant des récompenses (Pass VIP, etc.)
        // Filtre sur les événements futurs et actifs kamal
        // Tri par date croissante kamal
        // Récupération des événements kamal
        $events = CityEvent::where('is_active', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get()
            ->map(function ($event) use ($user) {
                // Vérifier si l'utilisateur possède déjà un pass pour cet événement kamal
                $event->is_owned = EventRedemption::where('user_id', $user->id)
                    ->where('city_event_id', $event->id)
                    ->exists();

                return $event;
            });

        // Historique des récompenses déjà récupérées par le joueur (Pass validés) kamal
        $myRewards = EventRedemption::where('user_id', $user->id)
            ->with('cityEvent')
            ->latest()
            ->get();

        // Nouveaux lots (prizes) à ouvrir gagnés lors de compétitions kamal
        // Tri par date croissante kamal
        // Récupération des lots kamal
        $prizes = \App\Models\UserPrize::where('user_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Player/Rewards', [
            'events' => $events,
            'myRewards' => $myRewards,
            'prizes' => $prizes,
            'stats' => [
                'current_coins' => $user->coins,
                'current_diamonds' => $user->diamonds,
                'total_redemptions' => $myRewards->count(),
            ],
        ]);
    }

    /**
     * Ouvre un lot (prize) gagné.
     * Change le statut du lot pour permettre l'accès au contenu (QR Code, etc.)
     */
    public function openPrize(\App\Models\UserPrize $prize)
    {
        // Sécurité : Vérifier que le lot appartient bien à l'utilisateur connecté
        if ($prize->user_id !== auth()->id()) {
            abort(403);
        }

        // Éviter la double ouverture
        if ($prize->is_opened) {
            return back()->with('error', 'Ce lot a déjà été ouvert.');
        }

        // Mise à jour du statut d'ouverture
        $prize->update([
            'is_opened' => true,
            'opened_at' => now(),
        ]);

        return back()->with('success', 'Lot ouvert avec succès !');
    }

    /**
     * Permet au joueur de convertir ses Points (xp) en Diamants.
     * Logique d'économie interne : Taux actuel 1000 xp = 1 Diamant.
     */
    public function convertCoinsToDiamonds(Request $request)
    {
        $user = auth()->user();
        $conversionRate = 1000; // Taux de conversion configurable

        if ($user->xp < $conversionRate) {
            return back()->with('error', 'Points insuffisants pour la conversion (1000 xp requis).');
        }

        // Transaction atomique pour garantir l'intégrité du solde
        DB::transaction(function () use ($user, $conversionRate) {
            $user->decrement('xp', $conversionRate);
            $user->increment('diamonds', 1);

            // Création d'une notification système pour confirmer la conversion
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'reward',
                'message' => 'Trésor converti ! Vous avez obtenu 1 diamant 💎 en échange de vos points.',
                'link' => route('player.rewards.index'), // Redirige vers la page des récompenses
                'read_at' => null,
            ]);
        });

        return back()->with('success', 'Conversion réussie ! +1 Diamant 💎');
    }

    /**
     * Gère l'achat d'un pass d'événement (VIP, Repas, etc.) avec des diamants.
     * Crée un EventRedemption unique servant de preuve d'achat.
     */
    public function buyEventPass(Request $request, CityEvent $event)
    {
        $user = auth()->user();

        // 1. Vérifications de sécurité et de solde
        if (!$event->has_vip_pass) {
            return back()->with('error', 'Cet événement ne propose pas de pass.');
        }

        if ($user->diamonds < $event->diamond_price) {
            return back()->with('error', 'Diamants insuffisants pour obtenir ce pass.');
        }

        $alreadyOwned = EventRedemption::where('user_id', $user->id)
            ->where('city_event_id', $event->id)
            ->exists();

        if ($alreadyOwned) {
            return back()->with('error', 'Vous possédez déjà un pass pour cet événement.');
        }

        // 2. Traitement de la transaction et génération du code unique
        DB::transaction(function () use ($user, $event) {
            // Débiter les diamants du compte joueur
            $user->decrement('diamonds', $event->diamond_price);

            // Créer le pass unique (Redemption) avec un code de sécurité
            EventRedemption::create([
                'user_id' => $user->id,
                'city_event_id' => $event->id,
                'redemption_code' => 'CP-' . strtoupper(Str::random(8)) . '-' . $user->id,
                'redeemed_at' => now(),
            ]);

            // Notification de succès pour informer le joueur
            Notification::create([
                'user_id' => $user->id,
                'type' => 'event',
                'message' => "Félicitations ! Votre pass pour {$event->title} est disponible dans vos récompenses.",
                'link' => route('player.rewards.index'), // Redirige le joueur vers sa liste de pass
                'read_at' => null,
            ]);
        });

        return back()->with('success', 'Votre pass a été généré avec succès !');
    }

    /**
     * Affiche les détails d'un pass spécifique (Code QR / Code de validation).
     */
    public function showPass(EventRedemption $redemption)
    {
        // Sécurité : s'assurer que le pass appartient bien au joueur
        if ($redemption->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Player/PassDetail', [
            'redemption' => $redemption->load('cityEvent'),
        ]);
    }
}
