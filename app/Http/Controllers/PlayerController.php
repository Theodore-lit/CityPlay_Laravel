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

        // Get current session
        $session = \App\Models\GameSession::where('user_id', $user->id)
            ->where('city_id', $city->id)
            ->where('status', 'in_progress')
            ->first();

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

        $availableLocations = $city->locations()->get()->collect();
        $sequence = [];

        if ($lat && $lng && $availableLocations->isNotEmpty()) {
            $currentLat = $lat;
            $currentLng = $lng;

            while ($availableLocations->isNotEmpty()) {
                // Trouver le lieu le plus proche de la position actuelle
                $closest = $availableLocations->sortBy(function ($location) use ($currentLat, $currentLng) {
                    return $this->calculateDistance($currentLat, $currentLng, $location->latitude, $location->longitude);
                })->first();

                $sequence[] = $closest->id;

                // Mettre à jour la position actuelle pour le prochain calcul
                $currentLat = $closest->latitude;
                $currentLng = $closest->longitude;

                // Retirer le lieu trouvé de la liste des lieux disponibles
                $availableLocations = $availableLocations->reject(fn($l) => $l->id === $closest->id);
            }
        } else {
            $sequence = $availableLocations->pluck('id')->toArray();
        }

        // Create or update game session for solo
        $session = \App\Models\GameSession::updateOrCreate(
            ['user_id' => $user->id, 'city_id' => $city->id, 'team_id' => null, 'status' => 'in_progress'],
            [
                'start_time' => now(),
                'discovery_sequence' => $sequence,
                'current_location_id' => $sequence[0] ?? null,
                'current_enigma_id' => \App\Models\Enigma::where('location_id', $sequence[0] ?? null)
                    ->where('is_site_specific', false)
                    ->first()->id ?? null,
            ]
        );

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
                $session->update(['status' => 'completed', 'end_time' => now()]);
            }
        }

        $user->xp += $xp;
        $user->save();

        return back()->with('success', 'Félicitations ! Lieu découvert.');
    }
}
