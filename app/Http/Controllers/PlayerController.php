<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Enigma;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PlayerController extends Controller
{
    /**
     * Affiche le tableau de bord principal du joueur.
     * Calcule les statistiques, les villes débloquées et la progression du niveau.
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Récupération des villes actives avec leur statut de mission (lock, new, good)
        $cities = City::where('is_active', true)
            ->withCount('locations')
            ->orderBy('name')
            ->get()
            ->map(function ($city, $index) use ($user) {
                // Compte les lieux découverts par l'utilisateur dans cette ville
                $discoveredInCity = \App\Models\UserLocationProgress::where('user_id', $user->id)
                    ->whereHas('location', fn($q) => $q->where('city_id', $city->id))
                    ->where('is_discovered', true)
                    ->count();

                // Détermination du statut visuel de la ville
                if ($city->locations_count === 0) {
                    $city->mission_status = 'lock';
                } elseif ($discoveredInCity === 0 && $city->created_at?->gt(now()->subDays(14))) {
                    $city->mission_status = 'new';
                } else {
                    $city->mission_status = 'good';
                }

                return $city;
            });

        // Nombre de villes où l'utilisateur a au moins une découverte
        $citiesUnlocked = \App\Models\UserLocationProgress::where('user_id', $user->id)
            ->where('is_discovered', true)
            ->with('location')
            ->get()
            ->pluck('location.city_id')
            ->filter()
            ->unique()
            ->count();

        // Total des missions (Lieux + Quiz) terminées
        $missionsCompleted = \App\Models\UserLocationProgress::where('user_id', $user->id)
            ->where('is_discovered', true)
            ->count()
            + \App\Models\QuizResult::where('user_id', $user->id)->count();

        // Logique de progression par niveau (4000 XP par niveau)
        $xpPerLevel = 4000;
        $xpInLevel = $user->xp % $xpPerLevel;

        return Inertia::render('Player/Dashboard', [
            'cities' => $cities,
            'stats' => [
                'missions' => $missionsCompleted,
                'total_xp' => $user->xp,
                'cities_unlocked' => $citiesUnlocked,
                'cities_total' => $cities->count(),
                'streak_days' => $user->last_activity_at?->isToday() ? 1 : 0,
                'xp_in_level' => $xpInLevel,
                'xp_per_level' => $xpPerLevel,
            ],
        ]);
    }

    /**
     * Met à jour la position GPS du joueur en temps réel.
     * Retourne également la position des membres de l'équipe si applicable.
     */
    public function updatePosition(Request $request)
    {
        $user = auth()->user();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $teamId = $request->input('team_id');

        $user->update([
            'latitude' => $lat,
            'longitude' => $lng
        ]);

        // Diffusion instantanée de la position via Reverb kamal
        if ($teamId) {
            broadcast(new \App\Events\PlayerPositionUpdated(
                $user->id,
                $user->name,
                $lat,
                $lng,
                $teamId
            ))->toOthers();
        }

        $teamPositions = [];
        if ($teamId) {
            $team = \App\Models\Team::find($teamId);
            if ($team) {
                // Récupère les positions des coéquipiers
                $teamPositions = $team->members()
                    ->where('users.id', '!=', $user->id)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get(['users.id', 'users.name', 'latitude as lat', 'longitude as lng']);
            }
        }

        return back()->with('teamPositions', $teamPositions);
    }

    /**
     * Permet au joueur d'acheter un cœur supplémentaire en échange de 500 XP.
     * Kamal
     */
    public function buyHeart(Request $request)
    {
        $user = auth()->user();
        if ($user->xp >= 500) {
            $user->decrement('xp', 500);
            $user->increment('hearts', 1);
            return back()->with('success', 'Un cœur ajouté !');
        }
        return back()->with('error', 'XP insuffisants.');
    }

    /**
     * Permet au joueur d'utiliser un indice pour 50 XP.
     * Kamal
     */
    public function useHint(Request $request)
    {
        $user = auth()->user();
        if ($user->xp >= 50) {
            $user->decrement('xp', 50);
            return back()->with('success', 'Indice débloqué (-50 PX)');
        }
        return back()->with('error', 'XP insuffisants pour un indice.');
    }

    /**
     * Achat d'une boussole visuelle pour 1000 XP.
     * Kamal
     */
    public function purchaseCompass(Request $request)
    {
        $user = auth()->user();

        if ($user->xp >= 1000) {
            $user->decrement('xp', 1000);
            return back()->with('success', 'Boussole débloquée !');
        }

        return back()->with('error', 'XP insuffisants.');
    }

    /**
     * Permet d'acheter un pack de 10 diamants contre 1000 XP.
     * Kamal
     */
    public function buyDiamonds(Request $request)
    {
        $user = auth()->user();
        if ($user->xp >= 1000) {
            $user->decrement('xp', 1000);
            $user->increment('diamonds', 1);
            return back()->with('success', '1 diamants ajoutés !');
        }
        return back()->with('error', 'XP insuffisants.');
    }

    /**
     * Active le passe exploration pour 24 heures contre 2000 XP.
     * Kamal
     */
    public function buyExplorerPass(Request $request)
    {
        $user = auth()->user();
        if ($user->xp >= 2000) {
            $user->decrement('xp', 2000);
            $user->update(['boost_expires_at' => now()->addHours(24)]);
            return back()->with('success', 'Passe exploration 24h activé !');
        }
        return back()->with('error', 'XP insuffisants.');
    }

    /**
     * Active le boost multiplicateur x2 pour 1 heure contre 10 diamants.
     * Kamal
     */
    public function buyXpBoost(Request $request)
    {
        $user = auth()->user();
        if ($user->diamonds >= 10) {
            $user->decrement('diamonds', 10);

            // On active le boost pour 1 heure
            $user->boost_expires_at = now()->addHour();
            $user->save();

            return back()->with('success', 'Boost Multiplicateur x2 activé pour 1 heure !');
        }
        return back()->with('error', 'Diamants insuffisants (10 requis).');
    }

    /**
     * Recommencer un quiz en payant 1 cœur.
     */
    public function retryQuiz(\App\Models\Quiz $quiz)
    {
        $user = auth()->user();

        if ($user->hearts >= 1) {
            $user->decrement('hearts', 1);
            return redirect()
                ->route('player.quiz', $quiz)
                ->with('success', 'Quiz réinitialisé ! -1 ❤️');
        }

        return redirect()
            ->route('player.quiz', $quiz)
            ->with('error', 'Vous n\'avez plus de cœurs pour recommencer.');
    }

    /**
     * Sélectionne le mode de jeu (Quiz ou Aventure) et le stocke en session.
     */
    public function selectMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:quiz,aventure'
        ]);

        session(['game_mode' => $request->mode]);

        return redirect()->route('player.cities');
    }

    /**
     * Liste les villes disponibles selon le mode de jeu choisi.
     */
    public function cities()
    {
        $user = auth()->user();
        $mode = session('game_mode', 'aventure');

        $query = City::where('is_active', true);

        // Filtrage des villes selon le contenu disponible pour le mode choisi
        if ($mode === 'quiz') {
            $query->whereHas('quizzes');
        } else {
            $query->whereHas('locations.enigmas');
        }

        $cities = $query->with(['events', 'locations' => function ($query) use ($user) {
            $query->with(['userProgress' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        }])
            ->get()
            ->map(function ($city) use ($user) {
                // Calcul de la progression dans la ville (Lieux découverts / Total)
                $totalLocations = $city->locations->count();
                $discoveredLocations = $city->locations->filter(function ($location) {
                    return $location->userProgress->where('is_discovered', true)->isNotEmpty();
                })->count();

                $city->progress_percentage = $totalLocations > 0
                    ? round(($discoveredLocations / $totalLocations) * 100)
                    : 0;
                $city->discovered_count = $discoveredLocations;
                $city->total_count = $totalLocations;

                // Vérifier si une session d'aventure a déjà été complétée
                $city->has_completed_adventure = \App\Models\GameSession::where('city_id', $city->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->exists();

                return $city;
            });

        return Inertia::render('Player/Cities', [
            'cities' => $cities,
            'gameMode' => $mode,
        ]);
    }

    /**
     * Affiche la sélection des modes de jeu avec les quiz disponibles.
     */
    public function modes()
    {
        return Inertia::render('Player/Modes', [
            'quizzes' => \App\Models\Quiz::withCount('questions')->get()
        ]);
    }

    /**
     * Affiche le classement mondial des joueurs par XP kamal.
     */
    public function leaderboard()
    {
        $today = now()->toDateString();

        $topPlayersGlobal = \App\Models\User::where('role', 'joueur')
            ->orderBy('xp', 'desc')
            ->take(10)
            ->get();

        $topPlayersDaily = \App\Models\User::where('role', 'joueur')
            ->leftJoin('daily_xp_earnings', function ($join) use ($today) {
                $join->on('users.id', '=', 'daily_xp_earnings.user_id')
                    ->where('daily_xp_earnings.date', '=', $today);
            })
            ->orderBy('daily_xp_earnings.xp_earned', 'desc')
            ->select('users.*', 'daily_xp_earnings.xp_earned as daily_xp')
            ->take(10)
            ->get()
            ->map(function ($user) {
                $user->xp = $user->daily_xp ?? 0;
                return $user;
            });

        return Inertia::render('Player/Leaderboard', [
            'topPlayersGlobal' => $topPlayersGlobal,
            'topPlayersDaily' => $topPlayersDaily
        ]);
    }

    /**
     * Redirige vers l'index des récompenses.
     * Kamal
     */
    public function rewards()
    {
        return redirect()->route('player.rewards.index');
    }

    /**
     * Affiche le Hub (Paramètres/Raccourcis) du joueur kamal.
     */
    public function hub()
    {
        return Inertia::render('Player/Hub');
    }

    /**
     * Affiche la boutique (Shop) avec le solde du joueur.
     * Kamal
     */
    public function shop()
    {
        $user = auth()->user();
        return Inertia::render('Player/Shop', [
            'user' => [
                'id' => $user->id,
                'xp' => $user->xp,
                'hearts' => $user->hearts,
                'diamonds' => $user->diamonds ?? 0,
            ]
        ]);
    }

    /**
     * Initialise et affiche une vue de Quiz.
     */
    public function quiz(\App\Models\Quiz $quiz = null)
    {
        if (!$quiz) {
            $quiz = \App\Models\Quiz::first();
        }

        return Inertia::render('Player/Quiz', [
            'quiz' => $quiz->load('questions'),
        ]);
    }

    /**
     * Traite les réponses d'un quiz, calcule le score, les étoiles et attribue les XP.
     */
    public function submitQuiz(\Illuminate\Http\Request $request, \App\Models\Quiz $quiz)
    {
        $answers = $request->input('answers');
        $hintsUsed = $request->input('hints_used', 0);
        $heartsLeft = $request->input('hearts_left', 3);

        $score = 0;
        $total = $quiz->questions->count();

        // Comparaison des réponses envoyées avec les réponses correctes en DB
        foreach ($quiz->questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            if ($userAnswer && (string)$userAnswer === (string)$question->correct_option) {
                $score++;
            }
        }

        // Calcul des étoiles basé sur la performance (Min 3/5 pour 1 étoile)
        $stars = 0;
        if ($score === 5) {
            $stars = 3;
        } elseif ($score === 4) {
            $stars = 2;
        } elseif ($score === 3) {
            $stars = 1;
        }

        // Attribution de 100 XP par bonne réponse
        $xpEarned = $score * 100;

        // Si le quiz est lié à un lieu spécifique, on valide la découverte du lieu
        if ($quiz->location_id && $score >= 3) {
            \App\Models\UserLocationProgress::updateOrCreate(
                ['user_id' => auth()->id(), 'location_id' => $quiz->location_id],
                ['is_discovered' => true, 'stars' => max($stars, 1), 'discovered_at' => now()]
            );
        }

        // Enregistrement du résultat du quiz
        \App\Models\QuizResult::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $total,
            'xp_earned' => $xpEarned,
        ]);

        // Mise à jour des XP globaux du joueur avec prise en compte du boost
        $user = auth()->user();
        $totalXp = $user->addReward('xp', $xpEarned);

        return redirect()
            ->route('player.quiz.result', $quiz)
            ->with('success', "Quiz terminé ! Vous avez gagné $totalXp XP.")
            ->with('quiz_result', [
                'score' => $score,
                'total' => $total,
                'xp_earned' => $xpEarned,
                'stars' => $stars,
                'hints_used' => (int) $request->input('hints_used', 0),
                'time_left' => (int) $request->input('time_left', 0),
                'failed' => $request->boolean('failed'),
            ]);
    }

    /**
     * Affiche l'écran de résultat après la soumission d'un quiz.
     */
    public function quizResult(\App\Models\Quiz $quiz)
    {
        $result = session('quiz_result');

        if (!$result) {
            return redirect()->route('player.quiz', $quiz);
        }

        $quiz->load('city');

        $nextQuiz = \App\Models\Quiz::where('city_id', $quiz->city_id)
        ->where('id', '>', $quiz->id)
        ->orderBy('id', 'asc')
        ->first();

        return Inertia::render('Player/QuizResult', [
            'quiz' => $quiz->only(['id', 'title', 'category']),
            'city' => $quiz->city,
            'result' => $result,
            'nextQuizId' => $nextQuiz ? $nextQuiz->id : null,
        ]);
    }

    /**
     * Point d'entrée principal du mode de jeu (Aventure ou Quiz dans une ville).
     */
    public function game(City $city, Request $request)
    {
        $user = auth()->user();
        $mode = session('game_mode', 'aventure');
        $requestedLocationId = $request->input('location_id');
        $enigmaId = $request->input('enigma_id');
        $lobbySessionId = $request->input('lobby_session_id');

        // GESTION DU MODE QUIZ DANS UNE VILLE
        if ($mode === 'quiz') {
            // Récupération de tous les quiz de la ville
            $quizzes = \App\Models\Quiz::where('city_id', $city->id)
                ->withCount('questions')
                ->get();

            // Quiz déjà réussis (min 60% de bonnes réponses)
            $completedQuizIds = \App\Models\QuizResult::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizzes->pluck('id'))
                ->whereRaw('score >= (total_questions * 0.6)')
                ->pluck('quiz_id')
                ->toArray();

            // Catégorisation par difficulté pour l'affichage et le déblocage progressif
            $categorizedQuizzes = [
                'easy' => $quizzes->where('difficulty', 'easy')->values(),
                'medium' => $quizzes->where('difficulty', 'medium')->values(),
                'hard' => $quizzes->where('difficulty', 'hard')->values(),
            ];

            // Logique de déblocage des niveaux : Facile -> Moyen -> Difficile
            $allEasyDone = $categorizedQuizzes['easy']->every(fn($q) => in_array($q->id, $completedQuizIds));
            $allMediumDone = $categorizedQuizzes['medium']->every(fn($q) => in_array($q->id, $completedQuizIds));

            return Inertia::render('Player/QuizLobby', [
                'city' => $city,
                'quizzes' => $categorizedQuizzes,
                'completedQuizIds' => $completedQuizIds,
                'levels' => [
                    'easy' => ['unlocked' => true, 'label' => 'Facile'],
                    'medium' => ['unlocked' => $allEasyDone, 'label' => 'Moyen'],
                    'hard' => ['unlocked' => $allEasyDone && $allMediumDone, 'label' => 'Difficile'],
                ]
            ]);
        }

        // GESTION DU MODE AVENTURE (SOLO OU ÉQUIPE)
        // Recherche d'une session en cours pour l'utilisateur ou son équipe
        $session = null;
        
        // First, try to find by lobby_session_id if provided
        if ($lobbySessionId) {
            $session = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
                ->where('user_id', $user->id)
                ->first();
        }
        
        // If not found by lobby_session_id, try the original query
        if (!$session) {
            $session = \App\Models\GameSession::where('city_id', $city->id)
                ->where('status', 'in_progress')
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('team_id', $user->teams->pluck('id'));
                })
                ->first();
        }

        // Si un location_id différent est demandé, mettre à jour la session
        if ($requestedLocationId && (!$session || $session->current_location_id != $requestedLocationId)) {
            $requestedLocation = $city->locations()->find($requestedLocationId);
            if ($requestedLocation) {
                $firstEnigma = $requestedLocation->enigmas()->first();

                if ($session) {
                    // Mettre à jour la session existante
                    $session->update([
                        'current_location_id' => $requestedLocationId,
                        'current_enigma_id' => $firstEnigma?->id,
                    ]);
                } else {
                    // Créer une nouvelle session pour ce location
                    $session = \App\Models\GameSession::create([
                        'user_id' => $user->id,
                        'city_id' => $city->id,
                        'team_id' => null,
                        'start_time' => now(),
                        'status' => 'in_progress',
                        'discovery_sequence' => [$requestedLocationId],
                        'current_location_id' => $requestedLocationId,
                        'current_enigma_id' => $firstEnigma?->id,
                        'lobby_session_id' => \Illuminate\Support\Str::uuid(),
                    ]);
                }
            }
        }

        // Position des membres de l'équipe si applicable
        $teamPositions = [];
        if ($session && $session->team_id) {
            $teamPositions = \App\Models\Team::find($session->team_id)
                ->members()
                ->where('users.id', '!=', $user->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['users.id', 'users.name', 'latitude as lat', 'longitude as lng']);
        }

        $city->load(['locations.enigmas.questions.options']);

        // Transformation des lieux pour gérer le statut de découverte (Brouillard de guerre)
        $locations = $city->locations->map(function ($location) use ($user, $session) {
            $progress = $location->userProgress->where('user_id', $user->id)->first();

            $location->is_discovered = $progress ? $progress->is_discovered : false;
            $location->stars = $progress ? $progress->stars : 0;

            // Identification de la cible actuelle de la quête
            $location->is_current_target = $session && $session->current_location_id == $location->id;

            // Masquage des infos si le lieu n'est pas encore découvert
            if (!$location->is_discovered && !$location->is_current_target) {
                $location->display_name = "???";
                $location->display_description = "Zone inconnue";
                $location->status = 'locked';
            } elseif (!$location->is_discovered && $location->is_current_target) {
                $location->display_name = "Prochaine destination";
                $location->display_description = "Résolvez l'énigme pour localiser";
                $location->status = 'target';
            } else {
                $location->display_name = $location->name;
                $location->display_description = $location->description;
                $location->status = 'discovered';
            }

            return $location;
        });

        // dd($session);



        return Inertia::render('Player/Game', [
            'city' => $city,
            'locations' => $locations,
            'enigmaId' => $enigmaId,
            'gameMode' => session('game_mode', 'aventure'),
            'currentSession' => $session,
            'initialTeamPositions' => $teamPositions,
            'lobbySessionId' => $session?->lobby_session_id
        ]);
    }

    /**
     * Affiche la page de configuration d'une nouvelle aventure (Solo vs Équipe).
     */
    public function adventureSetup(City $city)
    {
        return Inertia::render('Player/AdventureSetup', [
            'city' => $city,
            'teams' => auth()->user()->teams()->withCount('members')->get()
        ]);
    }

    /**
     * Initialise le Lobby de l'explorateur en filtrant les énigmes par distance et difficulté.
     */  // Theodore
    public function startSoloQuest(Request $request, City $city)
    {
        $user = auth()->user();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $transport = $request->input('transport', 'bike');
        $difficulty = $request->input('difficulty', 'medium');

        // Historique de session pour affichage informatif
        $completedSession = \App\Models\GameSession::where('city_id', $city->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->first();

        // Récupération des sessions non terminées (en cours ou en attente) kamal
        $unfinishedSessions = \App\Models\GameSession::where('city_id', $city->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['in_progress', 'waiting'])
            ->with(['currentLocation', 'currentEnigma'])
            ->get();

        // Filtrage des lieux ayant des énigmes correspondant à la difficulté
        $availableLocations = $city->locations()->whereHas('enigmas', function ($q) use ($difficulty) {
            $q->where('difficulty', $difficulty);
        })->with(['enigmas' => function ($q) use ($difficulty) {
            $q->where('difficulty', $difficulty);
        }, 'userProgress' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])->get();

        // Filtrage géographique basé sur le mode de transport
        if ($lat && $lng) {
            $maxDistance = 2000; // Pied/Vélo : 2 km
            if ($transport === 'moto') $maxDistance = 15000; // Moto : 10 km
            if ($transport === 'car') $maxDistance = 100000; // Voiture : 100 km

            $availableLocations = $availableLocations->filter(function ($location) use ($lat, $lng, $maxDistance, $transport) {
                $distance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                if ($transport === 'moto') {
                    return $distance <= $maxDistance && $distance > 2000;
                }
                if ($transport === 'car') {
                    return $distance <= $maxDistance && $distance > 15000;
                }
                return $distance <= $maxDistance;
            });
        }

        // Marquer les lieux qui ont une session en cours kamal
        $availableLocations = $availableLocations->map(function ($location) use ($unfinishedSessions) {
            $session = $unfinishedSessions->firstWhere('current_location_id', $location->id);
            $location->unfinished_session = $session;
            return $location;
        });

        // Trier pour mettre les sessions non terminées en premier (Besoins Kamal)
        $availableLocations = $availableLocations->sortByDesc(function ($location) {
            return $location->unfinished_session ? 1 : 0;
        });

        return Inertia::render('Player/ExplorerLobby', [
            'city' => $city,
            'locations' => $availableLocations->values(),
            'completedSession' => $completedSession,
            'unfinishedSessions' => $unfinishedSessions,
            'config' => [
                'transport' => $transport,
                'difficulty' => $difficulty,
                'lat' => $lat,
                'lng' => $lng
            ]
        ]);
    }

    /**
     * Lance officiellement l'aventure en créant ou mettant à jour une GameSession.
     */
    public function launchAdventure(Request $request, City $city)
    {
        $user = auth()->user();
        $locationId = $request->input('location_id');
        $enigmaId = $request->input('enigma_id');
        $difficulty = $request->input('difficulty', 'medium');

        $location = \App\Models\Location::findOrFail($locationId);

        // Générer un UUID pour la session lobby
        $lobbySessionId = \Illuminate\Support\Str::uuid();

        // Création de la session de jeu avec lobby_session_id
        $session = \App\Models\GameSession::create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'team_id' => null,
            'start_time' => now(),
            'status' => 'waiting',
            'discovery_sequence' => [$locationId],
            'current_location_id' => $locationId,
            'current_enigma_id' => $enigmaId,
            'lobby_session_id' => $lobbySessionId,
        ]);

        session(['adventure_start_time' => now()]);

        return redirect()->route('player.mission-lobby', $lobbySessionId);
    }

    /**
     * Calcule la distance en mètres entre deux points GPS (Formule Haversine).
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // mètres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Débloque un lieu (lorsque le joueur arrive à destination) et passe à l'énigme du site.
     */
    public function unlockLocation(Request $request, \App\Models\Location $location)
    {
        $user = auth()->user();
        $teamId = $request->input('team_id');

        $session = \App\Models\GameSession::where('city_id', $location->city_id)
            ->where('status', 'in_progress');

        if ($teamId) {
            $session = $session->where('team_id', $teamId);
        } else {
            $session = $session->where('user_id', $user->id)->whereNull('team_id');
        }

        $session = $session->first();

        if ($session) {
            // Activation de l'énigme spécifique au site (si elle existe)
            $siteEnigma = $location->enigmas()->where('is_site_specific', true)->first();
            $session->update([
                'current_enigma_id' => $siteEnigma->id ?? $session->current_enigma_id
            ]);

            // Attribution des XP pour la localisation réussie (Valeur définie par la mairie)
            $xp = $location->reward_xp_arrival ?? 100;
            $user->addReward('xp', $xp);
        }

        return back()->with('success', 'Lieu localisé ! Allez-y maintenant.');
    }

    /**
     * Finalise la découverte d'un lieu après la réussite de tous ses défis.
     */
    public function completeLocation(Request $request, \App\Models\Location $location)
    {
        $user = auth()->user();
        $stars = $request->input('stars', 1);
        $teamId = $request->input('team_id');
        $duration = $request->input('duration', 0); // Durée en secondes envoyée par le front
        $lobbySessionId = $request->input('lobby_session_id');

        // XP de résolution d'énigme défini par la mairie
        $xpToAward = $location->reward_xp_enigma ?? 150;

        // Mark as discovered for the user
        \App\Models\UserLocationProgress::updateOrCreate(
            ['user_id' => $user->id, 'location_id' => $location->id],
            ['is_discovered' => true, 'stars' => max(1, $stars), 'discovered_at' => now()]
        );

        // Advance session to next location in sequence
        $session = null;
        
        if ($lobbySessionId) {
            $session = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
                ->where('user_id', $user->id)
                ->first();
        }
        
        if (!$session) {
            $session = \App\Models\GameSession::where('city_id', $location->city_id)
                ->where('status', 'in_progress');

            if ($teamId) {
                $session = $session->where('team_id', $teamId);
            } else {
                $session = $session->where('user_id', $user->id)->whereNull('team_id');
            }

            $session = $session->first();
        }

        if ($session) {
            // Diffusion de la réussite aux autres membres de l'équipe (Kamal)
            if ($session->team_id) {
                broadcast(new \App\Events\MissionCompleted(
                    $user->id,
                    $user->name,
                    $session->team_id,
                    $location->name
                ))->toOthers();
            }

            // Récupération des récompenses de l'énigme actuelle avant de passer à la suivante
            if ($session->current_enigma_id) {
                $enigma = \App\Models\Enigma::find($session->current_enigma_id);
                if ($enigma) {
                    if ($enigma->reward_hearts > 0) {
                    }
                    if ($enigma->reward_coins > 0) { // On utilise reward_coins comme XP ou diamonds selon le cas, ici le user a dit XP
                        $user->addReward('xp', $enigma->reward_coins);
                    }
                }
            }

            if ($session->discovery_sequence) {
                $sequence = $session->discovery_sequence;
                $currentIndex = array_search($location->id, $sequence);

                if ($currentIndex !== false && isset($sequence[$currentIndex + 1])) {
                    $nextLocationId = $sequence[$currentIndex + 1];
                    $nextEnigma = \App\Models\Enigma::where('location_id', $nextLocationId)
                        ->where('is_site_specific', false)
                        ->first();

                    $session->update([
                        'current_location_id' => $nextLocationId,
                        'current_enigma_id' => $nextEnigma->id ?? \App\Models\Enigma::where('location_id', $nextLocationId)->first()->id ?? null
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Lieu complété ! Passage au suivant !',
                        'isComplete' => false
                    ]);
                } else {
                    // End of city!
                    $winnerId = null;
                    $diamondBonus = 0;

                    // Si c'est une session multijoueur, vérifier si quelqu'un d'autre a déjà terminé
                    if ($session->lobby_session_id) {
                        $existingWinner = \App\Models\GameSession::where('lobby_session_id', $session->lobby_session_id)
                            ->where('status', 'completed')
                            ->where('winner_id', '!=', null)
                            ->first();

                        if (!$existingWinner) {
                            // Ce joueur est le premier à terminer!
                            $winnerId = $user->id;
                            $diamondBonus = 10;
                            $user->addReward('diamonds', 10);
                        }
                    } else {
                        // Session solo - le joueur gagne automatiquement
                        $winnerId = $user->id;
                        $diamondBonus = 10;
                        $user->addReward('diamonds', 10);
                    }

                    $session->update([
                        'status' => 'completed',
                        'end_time' => now(),
                        'date_completion' => now(),
                        'total_time' => $duration,
                        'final_score' => $user->xp + $xpToAward,
                        'items_found' => count($sequence),
                        'winner_id' => $winnerId,
                    ]);

                    $message = $diamondBonus > 0 ? "Félicitations ! Vous êtes vainqueur et gagnez des diamants !" : "Lieu terminé ! Une autre équipe a remporté le bonus.";
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'isComplete' => true
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lieu complété !',
            'isComplete' => false
        ]);
    }

    // $user->addReward('xp', $xpToAward);
    public function markNotificationRead(\App\Models\Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back();
    }

    /**
     * Récupère les joueurs disponibles pour une mission.
     * Kamal
     */
    public function getAvailablePlayers(Request $request)
    {
        $locationId = $request->input('location_id');

        $availableUsers = User::where('id', '!=', auth()->id())
            ->where('role', 'joueur')
            ->whereDoesntHave('locationProgress', function ($query) use ($locationId) {
                $query->where('location_id', $locationId)
                    ->where('is_discovered', true);
            })
            ->select('id', 'name', 'xp')
            ->get();

        return response()->json([
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Affiche le lobby d'attente pour une session multijoueur.
     * Kamal
     */
    public function showMissionLobby($lobbySessionId)
    {
        // Chercher une session existante pour ce joueur dans ce lobby
        $userSession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->where('user_id', auth()->id())
            ->first();

        // Si le joueur n'a pas de session, en créer une (il a été invité)
        if (!$userSession) {
            $existingSession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
                ->with(['currentLocation', 'currentEnigma', 'city'])
                ->first();

            if (!$existingSession) {
                abort(404, 'Lobby non trouvé');
            }

            $userSession = \App\Models\GameSession::create([
                'user_id' => auth()->id(),
                'city_id' => $existingSession->city_id,
                'start_time' => $existingSession->start_time ?? now(),
                'status' => 'waiting',
                'current_location_id' => $existingSession->current_location_id,
                'current_enigma_id' => $existingSession->current_enigma_id,
                'lobby_session_id' => $lobbySessionId,
            ]);
        }

        // Charger les relations
        $lobbySession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->first()
            ->load(['currentLocation', 'currentEnigma', 'city']);

        $players = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->with('user')
            ->get()
            ->map(fn($session) => [
                'id' => $session->user_id,
                'name' => $session->user->name,
                'status' => $session->status,
                'xp' => $session->user->xp,
            ]);

        return Inertia::render('Player/MissionLobby', [
            'lobbySessionId' => $lobbySessionId,
            'location' => $lobbySession->currentLocation ? [
                'id' => $lobbySession->currentLocation->id,
                'name' => $lobbySession->currentLocation->name,
                'description' => $lobbySession->currentLocation->description,
            ] : null,
            'enigma' => $lobbySession->currentEnigma ? [
                'id' => $lobbySession->currentEnigma->id,
                'difficulty' => $lobbySession->currentEnigma->difficulty ?? 'Normal',
            ] : null,
            'city' => $lobbySession->city ? [
                'id' => $lobbySession->city->id,
                'name' => $lobbySession->city->name,
            ] : null,
            'players' => $players,
        ]);
    }

    /**
     * Inviter un joueur à rejoindre la session de mission.
     * Kamal
     */
    public function invitePlayer(Request $request, $lobbySessionId)
    {
        $user = auth()->user();
        $lobbySession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->where('user_id', $user->id)
            ->with(['currentLocation', 'city'])
            ->firstOrFail();

        $shareLink = URL::temporarySignedRoute(
            'mission.join-link',
            now()->addMinutes(30), // Durée de validité (ex: 30 minutes)
            ['lobbySessionId' => $lobbySessionId]
        );

        $invitedUserId = $request->input('user_id');
        if ($invitedUserId) {
            $invitedUser = User::findOrFail($invitedUserId);
            (new \App\Notifications\MissionInvitation($user, $lobbySessionId, $lobbySession->currentLocation, $lobbySession->city))->send($invitedUser);
            return back()->with('success', "{$invitedUser->name} a été invité !");
        }

        // 2. On passe le lien à la vue du lobby si on veut juste l'afficher
        return view('player.mission-lobby-view', compact('lobbySession', 'shareLink'));
    }

    /**
     * Rejoindre une session de mission existante.
     * Kamal
     */
    public function joinMissionLobby(Request $request, $lobbySessionId)
    {
        $user = auth()->user();

        // Vérifier que la session existe
        $existingSession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)->firstOrFail();

        //     // Si vous n'utilisez pas le middleware 'signed', vous pouvez faire la vérification ici :
        // if (! $request->hasValidSignature()) {
        //     return redirect()->route('player.dashboard')
        //         ->with('error', 'Désolé, ce lien d\'invitation CityPlay a expiré ! Demandez un nouveau lien à l\'hôte.');
        // }

        // Créer une session pour l'utilisateur courant avec le même lobby_session_id
        $session = \App\Models\GameSession::create([
            'user_id' => $user->id,
            'city_id' => $existingSession->city_id,
            'start_time' => $existingSession->start_time ?? now(),
            'status' => 'waiting',
            'current_location_id' => $existingSession->current_location_id,
            'current_enigma_id' => $existingSession->current_enigma_id,
            'lobby_session_id' => $lobbySessionId,
        ]);

        return redirect()->route('player.mission-lobby', $lobbySessionId)
            ->with('success', 'Vous avez rejoint la session !');
    }

    /**
     * Démarre la mission avec les joueurs rassemblés.
     * Kamal
     */
    public function startMissionWithPlayers(Request $request, $lobbySessionId)
    {
        $user = auth()->user();
        $lobbySession = \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Mettre à jour le statut de toutes les sessions
        \App\Models\GameSession::where('lobby_session_id', $lobbySessionId)
            ->update(['status' => 'in_progress']);

        return redirect()->route('player.game', [
            'city' => $lobbySession->city_id,
            'lobby_session_id' => $lobbySessionId
        ])
            ->with('success', 'Aventure lancée avec les joueurs !');
    }
}
