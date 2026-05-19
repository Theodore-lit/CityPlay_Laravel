<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MairieController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AdminController;
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
    Route::post('/player/select-mode', [PlayerController::class, 'selectMode'])->name('player.select-mode');
    Route::get('/cities', [PlayerController::class, 'cities'])->name('player.cities');
    Route::get('/modes', [PlayerController::class, 'modes'])->name('player.modes');
    Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->name('player.leaderboard');
    Route::get('/rewards', [PlayerController::class, 'rewards'])->name('player.rewards');
    Route::get('/quiz/{quiz?}', [PlayerController::class, 'quiz'])->name('player.quiz');
    Route::post('/quiz/{quiz}/submit', [PlayerController::class, 'submitQuiz'])->name('player.quiz.submit');
    Route::get('/quiz/{quiz}/result', [PlayerController::class, 'quizResult'])->name('player.quiz.result');
    Route::get('/player/game/{city}', [PlayerController::class, 'game'])->name('player.game');
    Route::get('/player/adventure/setup/{city}', [PlayerController::class, 'adventureSetup'])->name('player.adventure.setup');
    Route::post('/player/adventure/solo/{city}', [PlayerController::class, 'startSoloQuest'])->name('player.adventure.solo');
    Route::post('/player/location/{location}/unlock', [PlayerController::class, 'unlockLocation'])->name('player.unlock-location');
    Route::post('/player/location/{location}/complete', [PlayerController::class, 'completeLocation'])->name('player.complete-location');
    Route::post('/player/update-position', [PlayerController::class, 'updatePosition'])->name('player.update-position');

    // Team Routes
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams/{team}/start-quest/{city}', [TeamController::class, 'startQuest'])->name('teams.start-quest');

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/mairie-city', [AdminController::class, 'storeMairieWithCity'])->name('admin.mairie-city.store');
    Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
    Route::get('/admin/cities', [MairieController::class, 'dashboard'])->name('admin.cities');

    Route::get('/admin/cities/{city}/quizzes', [AdminController::class, 'cityQuizzes'])->name('admin.cities.quizzes');
    Route::post('/admin/cities/{city}/quizzes', [AdminController::class, 'storeQuiz'])->name('admin.quizzes.store');
    Route::delete('/admin/quizzes/{quiz}', [AdminController::class, 'deleteQuiz'])->name('admin.quizzes.delete');

    Route::post('/admin/quizzes/{quiz}/questions', [AdminController::class, 'storeQuestion'])->name('admin.questions.store');
    Route::delete('/admin/questions/{question}', [AdminController::class, 'deleteQuestion'])->name('admin.questions.delete');

    // Mairie Routes
    Route::get('/mairie', [MairieController::class, 'dashboard'])->name('mairie.dashboard');
    Route::post('/mairie/cities', [MairieController::class, 'storeCity'])->name('mairie.cities.store');
    Route::get('/mairie/city/{city}', [MairieController::class, 'showCity'])->name('mairie.cities.show');
    Route::post('/mairie/city/{city}/location', [MairieController::class, 'storeLocation'])->name('mairie.locations.store');
    Route::post('/mairie/location/{location}/update', [MairieController::class, 'updateLocation'])->name('mairie.locations.update');
    Route::post('/mairie/location/{location}/enigma', [MairieController::class, 'storeEnigma'])->name('mairie.enigmas.store');
    Route::post('/mairie/location/{location}/image', [MairieController::class, 'storeLocationImage'])->name('mairie.locations.image');
    Route::patch('/mairie/city/{city}/toggle', [MairieController::class, 'toggleStatus'])->name('mairie.city.toggle');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/player/buy-heart', [PlayerController::class, 'buyHeart'])->name('player.buy.heart');
    Route::post('/player/quiz/{quiz}/retry', [PlayerController::class, 'retryQuiz'])->name('player.quiz.retry');
});

require __DIR__.'/auth.php';
