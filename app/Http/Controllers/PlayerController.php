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
        $hintsUsed = $request->input('hints_used', 0);
        $heartsLeft = $request->input('hearts_left', 3);
        
        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $index => $question) {
            if (isset($answers[$index]) && $answers[$index] === $question->correct_option) {
                $score++;
            }
        }

        // Calcul des étoiles
        $stars = 1;
        if ($score === $total) {
            $stars = ($hintsUsed === 0) ? 3 : 2;
        }

        $xpEarned = floor(($score / $total) * $quiz->xp_reward);

        // Si c'est un quiz lié à un lieu, on le débloque
        if ($quiz->location_id && $score >= floor($total * 0.6)) {
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

        $user = auth()->user();
        $user->xp += $xpEarned;
        $user->hearts = $heartsLeft; // Update user hearts with remaining ones
        $user->save();

        return back()->with('success', "Quiz terminé ! Vous avez gagné $xpEarned XP.");
    }

    public function game(City $city)
    {
        $user = auth()->user();
        $mode = session('game_mode', 'aventure');

        if ($mode === 'quiz') {
            return Inertia::render('Player/QuizLobby', [
                'city' => $city,
                'quizzes' => \App\Models\Quiz::where('city_id', $city->id)
                    ->withCount('questions')
                    ->get()
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
