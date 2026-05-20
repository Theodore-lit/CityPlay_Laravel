<?php

use App\Http\Controllers\AdminController;
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
        if ($user->role === 'mairie') {
            return redirect()->route('mairie.dashboard');
            }
            elseif ($user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');

        }
        else {
            
            return redirect()->route('player.dashboard');
        }
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
    Route::get('/player/adventure/solo/{city}', [PlayerController::class, 'startSoloQuest'])->name('player.adventure.solo');
    Route::post('/player/adventure/launch/{city}', [PlayerController::class, 'launchAdventure'])->name('player.adventure.launch');
    Route::post('/player/location/{location}/unlock', [PlayerController::class, 'unlockLocation'])->name('player.unlock-location');
    Route::post('/player/location/{location}/complete', [PlayerController::class, 'completeLocation'])->name('player.complete-location');
    Route::post('/player/update-position', [PlayerController::class, 'updatePosition'])->name('player.update-position');
    Route::get('/api/missions/{city}', [PlayerController::class, 'getMissionDetails'])->name('api.missions.show');

    // Team Routes
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams/{team}/start-quest/{city}', [TeamController::class, 'startQuest'])->name('teams.start-quest');
    Route::get('/teams/{team}/join-game/{city}', [TeamController::class, 'joinGame'])->name('teams.join-game');

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

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/city/{city}', [AdminController::class, 'showCity'])->name('admin.city.show');
    Route::post('/admin/mairie', [MairieController::class, 'store'])->name('admin.mairie.store');
    Route::post('/admin/location', [AdminController::class, 'storeLocation'])->name('admin.location.store');
    Route::post('/admin/enigma', [AdminController::class, 'storeEnigma'])->name('admin.enigma.store');
    Route::post('/admin/user/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.user.toggle-status');
    // Mairie Routes
    Route::get('/mairie', [MairieController::class, 'dashboard'])->name('mairie.dashboard');
    Route::get('/mairie/city/{city}/hub', [MairieController::class, 'cityHub'])->name('mairie.city.hub');
    Route::post('/mairie/cities', [MairieController::class, 'storeCity'])->name('mairie.cities.store');
    Route::post('/mairie/cities/{city}/update', [MairieController::class, 'updateCity'])->name('mairie.cities.update');
    Route::get('/mairie/city/{city}', [MairieController::class, 'showCity'])->name('mairie.cities.show');
    Route::post('/mairie/city/{city}/location', [MairieController::class, 'storeLocation'])->name('mairie.locations.store');
    Route::post('/mairie/location/{location}/update', [MairieController::class, 'updateLocation'])->name('mairie.locations.update');
    Route::post('/mairie/location/{location}/enigma', [MairieController::class, 'storeEnigma'])->name('mairie.enigmas.store');
    Route::post('/mairie/location/{location}/image', [MairieController::class, 'storeLocationImage'])->name('mairie.locations.image');
    Route::patch('/mairie/city/{city}/toggle', [MairieController::class, 'toggleStatus'])->name('mairie.city.toggle');

    // Mairie Event Routes
    Route::get('/mairie/city/{city}/events', [MairieController::class, 'cityEvents'])->name('mairie.cities.events');
    Route::post('/mairie/city/{city}/events', [MairieController::class, 'storeEvent'])->name('mairie.events.store');
    Route::delete('/mairie/events/{event}', [MairieController::class, 'deleteEvent'])->name('mairie.events.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/player/buy-heart', [PlayerController::class, 'buyHeart'])->name('player.buy.heart');
    Route::post('/player/use-hint', [PlayerController::class, 'useHint'])->name('player.use-hint');
    Route::post('/player/quiz/{quiz}/retry', [PlayerController::class, 'retryQuiz'])->name('player.quiz.retry');
    Route::post('/notifications/{notification}/read', [PlayerController::class, 'markNotificationRead'])->name('notifications.read');
});

require __DIR__.'/auth.php';
