<?php

namespace App\Http\Controllers;

use App\Models\City;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $cities = City::where('is_active', true)
            ->withCount('locations')
            ->orderBy('name')
            ->get()
            ->map(function ($city, $index) use ($user) {
                $discoveredInCity = \App\Models\UserLocationProgress::where('user_id', $user->id)
                    ->whereHas('location', fn ($q) => $q->where('city_id', $city->id))
                    ->where('is_discovered', true)
                    ->count();

                if ($city->locations_count === 0) {
                    $city->mission_status = 'lock';
                } elseif ($discoveredInCity === 0 && $city->created_at?->gt(now()->subDays(14))) {
                    $city->mission_status = 'new';
                } else {
                    $city->mission_status = 'good';
                }

                return $city;
            });

        $citiesUnlocked = \App\Models\UserLocationProgress::where('user_id', $user->id)
            ->where('is_discovered', true)
            ->with('location')
            ->get()
            ->pluck('location.city_id')
            ->filter()
            ->unique()
            ->count();

        $missionsCompleted = \App\Models\UserLocationProgress::where('user_id', $user->id)
            ->where('is_discovered', true)
            ->count()
            + \App\Models\QuizResult::where('user_id', $user->id)->count();

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

        $teamPositions = [];
        if ($teamId) {
            $team = \App\Models\Team::find($teamId);
            if ($team) {
                $teamPositions = $team->members()
                    ->where('users.id', '!=', $user->id)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get(['users.id', 'users.name', 'latitude as lat', 'longitude as lng']);
            }
        }

        return back()->with('teamPositions', $teamPositions);
    }

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
     * Recommencer un quiz en payant 1 cœur
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

    public function selectMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:quiz,aventure'
        ]);

        session(['game_mode' => $request->mode]);

        return redirect()->route('player.cities');
    }

    public function cities()
    {
        $user = auth()->user();
        $mode = session('game_mode', 'aventure');

        $query = City::where('is_active', true);

        if ($mode === 'quiz') {
            // Uniquement les villes avec au moins un quiz
            $query->whereHas('quizzes');
        } else {
            // Mode Aventure : Uniquement les villes avec des lieux et énigmes
            $query->whereHas('locations.enigmas');
        }

        $cities = $query->with(['events', 'locations' => function ($query) use ($user) {
                $query->with(['userProgress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            }])
            ->get()
            ->map(function ($city) use ($user) {
                $totalLocations = $city->locations->count();
                $discoveredLocations = $city->locations->filter(function ($location) {
                    return $location->userProgress->where('is_discovered', true)->isNotEmpty();
                })->count();

                $city->progress_percentage = $totalLocations > 0
                    ? round(($discoveredLocations / $totalLocations) * 100)
                    : 0;
                $city->discovered_count = $discoveredLocations;
                $city->total_count = $totalLocations;

                // Vérifier si une session terminée existe pour cette ville
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

    public function modes()
    {
        return Inertia::render('Player/Modes', [
            'quizzes' => \App\Models\Quiz::withCount('questions')->get()
        ]);
    }

    public function leaderboard()
    {
        $topPlayers = \App\Models\User::where('role', 'joueur')
            ->orderBy('xp', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Player/Leaderboard', [
            'topPlayers' => $topPlayers
        ]);
    }

    public function rewards()
    {
        return Inertia::render('Player/Rewards');
    }

    public function quiz(\App\Models\Quiz $quiz = null)
    {
        if (!$quiz) {
            $quiz = \App\Models\Quiz::first();
        }

        return Inertia::render('Player/Quiz', [
            'quiz' => $quiz->load('questions'),
        ]);
    }

    public function submitQuiz(\Illuminate\Http\Request $request, \App\Models\Quiz $quiz)
    {
        $answers = $request->input('answers');
        $hintsUsed = $request->input('hints_used', 0);
        $heartsLeft = $request->input('hearts_left', 3);

        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            if ($userAnswer && (string)$userAnswer === (string)$question->correct_option) {
                $score++;
            }
        }

        // 1. Règle des étoiles mise à jour :
        // 5/5 -> 3 étoiles
        // 4/5 -> 2 étoiles
        // 3/5 -> 1 étoile
        // < 3 -> 0 étoile
        $stars = 0;
        if ($score === 5) {
            $stars = 3;
        } elseif ($score === 4) {
            $stars = 2;
        } elseif ($score === 3) {
            $stars = 1;
        }

        // 2. Calcul des XP : 100 XP par bonne réponse
        $xpEarned = $score * 100;

        // Si c'est un quiz lié à un lieu, on le débloque si le score est suffisant (min 3/5)
        if ($quiz->location_id && $score >= 3) {
            \App\Models\UserLocationProgress::updateOrCreate(
                ['user_id' => auth()->id(), 'location_id' => $quiz->location_id],
                ['is_discovered' => true, 'stars' => max($stars, 1), 'discovered_at' => now()]
            );
        }

        \App\Models\QuizResult::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $total,
            'xp_earned' => $xpEarned,
        ]);

        // 3. Mise à jour de l'utilisateur : on AJOUTE les XP au total existant
        $user = auth()->user();
        $user->increment('xp', $xpEarned);

        // Note : On ne touche PAS aux cœurs ici.
        // Les vies du quiz sont temporaires, les cœurs du compte sont permanents et s'achètent.

        return redirect()
            ->route('player.quiz.result', $quiz)
            ->with('success', "Quiz terminé ! Vous avez gagné $xpEarned XP.")
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

    public function quizResult(\App\Models\Quiz $quiz)
    {
        $result = session('quiz_result');

        if (!$result) {
            return redirect()->route('player.quiz', $quiz);
        }

        $quiz->load('city');

        return Inertia::render('Player/QuizResult', [
            'quiz' => $quiz->only(['id', 'title', 'category']),
            'city' => $quiz->city,
            'result' => $result,
        ]);
    }

    public function game(City $city)
    {
        $user = auth()->user();
        $mode = session('game_mode', 'aventure');

        // On n'empêche plus l'accès si la mission est terminée pour permettre de rejouer
        // $hasCompleted = \App\Models\GameSession::where('city_id', $city->id)
        //     ->where('user_id', $user->id)
        //     ->where('status', 'completed')
        //     ->exists();

        // if ($hasCompleted && $mode === 'aventure') {
        //     return redirect()->route('player.adventure.solo', $city->id)
        //         ->with('error', 'Cette mission est déjà terminée. Vous pouvez consulter vos statistiques dans le lobby.');
        // }

        if ($mode === 'quiz') {
            // On récupère tous les quiz de la ville
            $quizzes = \App\Models\Quiz::where('city_id', $city->id)
                ->withCount('questions')
                ->get();

            // On récupère les IDs des quiz déjà réussis par le joueur (score >= 60%)
            $completedQuizIds = \App\Models\QuizResult::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizzes->pluck('id'))
                ->whereRaw('score >= (total_questions * 0.6)')
                ->pluck('quiz_id')
                ->toArray();

            // On catégorise les quiz par difficulté
            $categorizedQuizzes = [
                'easy' => $quizzes->where('difficulty', 'easy')->values(),
                'medium' => $quizzes->where('difficulty', 'medium')->values(),
                'hard' => $quizzes->where('difficulty', 'hard')->values(),
            ];

            // Logique de déblocage :
            // Facile est toujours ouvert
            // Moyen s'ouvre si TOUS les quiz Faciles sont réussis
            // Difficile s'ouvre si TOUS les quiz Moyens sont réussis

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

        // Get current session (Check Solo first, then Teams)
        $session = \App\Models\GameSession::where('city_id', $city->id)
            ->where('status', 'in_progress')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('team_id', $user->teams->pluck('id'));
            })
            ->first();

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

        $locations = $city->locations->map(function ($location) use ($user, $session) {
            $progress = $location->userProgress->where('user_id', $user->id)->first();

            $location->is_discovered = $progress ? $progress->is_discovered : false;
            $location->stars = $progress ? $progress->stars : 0;

            // Determine if this is the current target in the sequence
            $location->is_current_target = $session && $session->current_location_id == $location->id;

            if (!$location->is_discovered && !$location->is_current_target) {
                $location->display_name = "???";
                $location->display_description = "Zone inconnue";
                $location->status = 'locked'; // Padlock
            } elseif (!$location->is_discovered && $location->is_current_target) {
                $location->display_name = "Prochaine destination";
                $location->display_description = "Résolvez l'énigme pour localiser";
                $location->status = 'target'; // Question mark
            } else {
                $location->display_name = $location->name;
                $location->display_description = $location->description;
                $location->status = 'discovered';
            }

            return $location;
        });

        return Inertia::render('Player/Game', [
            'city' => $city,
            'locations' => $locations,
            'gameMode' => session('game_mode', 'aventure'),
            'currentSession' => $session,
            'initialTeamPositions' => $teamPositions,
        ]);
    }

    public function adventureSetup(City $city)
    {
        return Inertia::render('Player/AdventureSetup', [
            'city' => $city,
            'teams' => auth()->user()->teams()->withCount('members')->get()
        ]);
    }

    public function startSoloQuest(Request $request, City $city)
    {
        $user = auth()->user();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $transport = $request->input('transport', 'bike');
        $difficulty = $request->input('difficulty', 'medium');

        // Vérifier si une session terminée existe déjà
        $completedSession = \App\Models\GameSession::where('city_id', $city->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->first();

        $availableLocations = $city->locations()->whereHas('enigmas', function($q) use ($difficulty) {
            $q->where('difficulty', $difficulty);
        })->with(['enigmas' => function($q) use ($difficulty) {
            $q->where('difficulty', $difficulty);
        }])->get();

        if ($lat && $lng) {
            // Filtrage par distance selon le mode de transport (Règles strictes)
            $maxDistance = 8000; // Pied/Vélo : 8 km
            if ($transport === 'moto') $maxDistance = 20000; // Moto : 20 km
            if ($transport === 'car') $maxDistance = 100000; // Voiture : 100 km (Plafonné)

            $availableLocations = $availableLocations->filter(function ($location) use ($lat, $lng, $maxDistance) {
                $distance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                return $distance <= $maxDistance;
            });
        }

        return Inertia::render('Player/ExplorerLobby', [
            'city' => $city,
            'locations' => $availableLocations->values(),
            'completedSession' => $completedSession,
            'config' => [
                'transport' => $transport,
                'difficulty' => $difficulty,
                'lat' => $lat,
                'lng' => $lng
            ]
        ]);
    }

    public function launchAdventure(Request $request, City $city)
    {
        $user = auth()->user();
        $locationId = $request->input('location_id');
        $enigmaId = $request->input('enigma_id');
        $difficulty = $request->input('difficulty', 'medium');

        // On autorise le lancement même si déjà complété pour permettre de rejouer
        // On cherche une session en cours pour la réinitialiser ou on en crée une nouvelle
        
        $location = \App\Models\Location::findOrFail($locationId);

        // Initialiser la session pour cette énigme spécifique choisie par le joueur
        $session = \App\Models\GameSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'city_id' => $city->id,
                'team_id' => null,
            ],
            [
                'start_time' => now(),
                'status' => 'in_progress', // On force le statut à 'in_progress' pour rejouer
                'discovery_sequence' => [$locationId],
                'current_location_id' => $locationId,
                'current_enigma_id' => $enigmaId,
            ]
        );

        session(['adventure_start_time' => now()]);

        return redirect()->route('player.game', $city->id);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

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
            // Move to site-specific enigma
            $siteEnigma = $location->enigmas()->where('is_site_specific', true)->first();
            $session->update([
                'current_enigma_id' => $siteEnigma->id ?? $session->current_enigma_id
            ]);

            // Award XP for unlocking
            $difficulty = $request->input('difficulty', 'easy');
            $xp = 100;
            if ($difficulty === 'medium') $xp = 250;
            if ($difficulty === 'hard') $xp = 500;

            $user->xp += $xp;
            $user->save();
        }

        return back()->with('success', 'Lieu localisé ! Allez-y maintenant.');
    }

    public function completeLocation(Request $request, \App\Models\Location $location)
    {
        $user = auth()->user();
        $stars = $request->input('stars', 1);
        $xp = $request->input('xp', 250);
        $teamId = $request->input('team_id');
        $duration = $request->input('duration', 0); // Durée en secondes envoyée par le front

        // Mark as discovered for the user
        \App\Models\UserLocationProgress::updateOrCreate(
            ['user_id' => $user->id, 'location_id' => $location->id],
            ['is_discovered' => true, 'stars' => max(1, $stars), 'discovered_at' => now()]
        );

        // Advance session to next location in sequence
        $session = \App\Models\GameSession::where('city_id', $location->city_id)
            ->where('status', 'in_progress');

        if ($teamId) {
            $session = $session->where('team_id', $teamId);
        } else {
            $session = $session->where('user_id', $user->id)->whereNull('team_id');
        }

        $session = $session->first();

        if ($session && $session->discovery_sequence) {
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
            } else {
                // End of city!
                $session->update([
                    'status' => 'completed',
                    'end_time' => now(),
                    'date_completion' => now(),
                    'total_time' => $duration,
                    'final_score' => $user->xp + $xp, // Score cumulé ou juste XP de la session? On va mettre XP gagné ici
                    'items_found' => count($sequence)
                ]);
            }
        }

        $user->xp += $xp;
        $user->save();

        return back()->with('success', 'Félicitations ! Lieu découvert.');
    }

    public function markNotificationRead(\App\Models\Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back();
    }
}
