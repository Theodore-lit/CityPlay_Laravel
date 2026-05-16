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

    public function cities()
    {
        return Inertia::render('Player/Cities', [
            'cities' => City::where('is_active', true)->withCount('locations')->get()
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
        return Inertia::render('Player/Leaderboard', [
            'topPlayers' => \App\Models\User::orderBy('xp', 'desc')->take(10)->get()
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
            if (isset($answers[$index]) && $answers[$index] === $question->correct_option) {
                $score++;
            }
        }

        $xpEarned = floor(($score / $total) * $quiz->xp_reward);

        \App\Models\QuizResult::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $total,
            'xp_earned' => $xpEarned,
        ]);

        // Update user XP
        $user = auth()->user();
        $user->xp += $xpEarned;
        $user->save();

        return redirect()->route('player.modes')->with('success', "Quiz terminé ! Score: $score/$total. XP gagnés: $xpEarned");
    }

    public function game(City $city)
    {
        return Inertia::render('Player/Game', [
            'city' => $city->load('locations.enigmas')
        ]);
    }
}
