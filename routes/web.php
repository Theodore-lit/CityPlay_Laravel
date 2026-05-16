<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MairieController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->role === 'mairie' || $user->role === 'super_admin') {
            return redirect()->route('mairie.dashboard');
        }
        return redirect()->route('player.dashboard');
    })->name('dashboard');

    // Player Routes
    Route::get('/player', [PlayerController::class, 'dashboard'])->name('player.dashboard');
    Route::get('/cities', [PlayerController::class, 'cities'])->name('player.cities');
    Route::get('/modes', [PlayerController::class, 'modes'])->name('player.modes');
    Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->name('player.leaderboard');
    Route::get('/rewards', [PlayerController::class, 'rewards'])->name('player.rewards');
    Route::get('/quiz/{quiz?}', [PlayerController::class, 'quiz'])->name('player.quiz');
    Route::post('/quiz/{quiz}/submit', [PlayerController::class, 'submitQuiz'])->name('player.quiz.submit');
    Route::get('/player/game/{city}', [PlayerController::class, 'game'])->name('player.game');

    // Mairie Routes
    Route::get('/mairie', [MairieController::class, 'dashboard'])->name('mairie.dashboard');
    Route::patch('/mairie/city/{city}/toggle', [MairieController::class, 'toggleStatus'])->name('mairie.city.toggle');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
