<?php

namespace App\Http\Controllers;

use App\Models\City;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Player/Dashboard', [
            'cities' => City::where('is_active', true)->withCount('locations')->get()
        ]);
    }

    /**
     * Achat d'un cœur avec des XP (1 cœur = 500 XP)
     */
    public function buyHeart()
    {
        $user = auth()->user();
        
        if ($user->xp >= 500) {
            $user->decrement('xp', 500);
            $user->increment('hearts', 1);
            
            return back()->with('success', 'Cœur acheté avec succès ! ❤️');
        }
        
        return back()->with('error', 'Pas assez d\'XP pour acheter un cœur.');
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
        $cities = City::where('is_active', true)
            ->with(['locations' => function ($query) use ($user) {
                $query->with(['userProgress' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            }])
            ->get()
            ->map(function ($city) {
                $totalLocations = $city->locations->count();
                $discoveredLocations = $city->locations->filter(function ($location) {
                    return $location->userProgress->where('is_discovered', true)->isNotEmpty();
                })->count();

                $city->progress_percentage = $totalLocations > 0 
                    ? round(($discoveredLocations / $totalLocations) * 100) 
                    : 0;
                $city->discovered_count = $discoveredLocations;
                $city->total_count = $totalLocations;

                return $city;
            });

        return Inertia::render('Player/Cities', [
            'cities' => $cities,
            'gameMode' => session('game_mode', 'aventure'),
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
        
        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            if ($userAnswer && (string)$userAnswer === (string)$question->correct_option) {
                $score++;
            }
        }

        // 1. Règle des étoiles corrigée :
        // 5/5 -> 3 étoiles
        // 4/5 -> 2 étoiles
        // 1, 2 ou 3 -> 1 étoile
        // 0 -> 0 étoile
        $stars = 0;
        if ($score === 5) {
            $stars = 3;
        } elseif ($score === 4) {
            $stars = 2;
        } elseif ($score >= 1) {
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

        $city->load(['locations.enigmas']);

        $locations = $city->locations->map(function ($location) use ($user) {
            $progress = $location->userProgress->where('user_id', $user->id)->first();
            
            $location->is_discovered = $progress ? $progress->is_discovered : false;
            $location->stars = $progress ? $progress->stars : 0;

            if (!$location->is_discovered) {
                $location->display_name = "???";
                $location->display_description = "Zone inconnue";
            } else {
                $location->display_name = $location->name;
                $location->display_description = $location->description;
            }

            return $location;
        });

        return Inertia::render('Player/Game', [
            'city' => $city,
            'locations' => $locations,
            'gameMode' => session('game_mode', 'aventure'),
        ]);
    }

    public function unlockLocation(Request $request, \App\Models\Location $location)
    {
        $user = auth()->user();
        
        \App\Models\UserLocationProgress::updateOrCreate(
            ['user_id' => $user->id, 'location_id' => $location->id],
            ['is_discovered' => true, 'discovered_at' => now()]
        );

        return back()->with('success', 'Lieu débloqué !');
    }

    public function completeLocation(Request $request, \App\Models\Location $location)
    {
        $user = auth()->user();
        $stars = $request->input('stars', 1);
        $xp = $request->input('xp', 250);

        $progress = \App\Models\UserLocationProgress::where('user_id', $user->id)
            ->where('location_id', $location->id)
            ->first();

        if ($progress) {
            $progress->stars = max($progress->stars, $stars);
            $progress->save();
        }

        $user->xp += $xp;
        $user->save();

        return back()->with('success', 'Mission terminée !');
    }
}
