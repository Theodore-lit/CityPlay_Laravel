<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CityEventController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MairieController;
use App\Http\Controllers\TeamController;
use App\Models\City;
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

        // Si c'est une mairie ou un super_admin, ils vont sur le même dashboard d'administration
        if ($user->role === 'mairie' || $user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('player.dashboard');
        }
    })->name('dashboard');

    // --- ROUTES JOUEUR ---
    Route::middleware('role:joueur')->group(function () {
        Route::get('/player', [PlayerController::class, 'dashboard'])->name('player.dashboard');
        Route::post('/player/select-mode', [PlayerController::class, 'selectMode'])->name('player.select-mode');
        Route::get('/cities', [PlayerController::class, 'cities'])->name('player.cities');
        Route::get('/modes', [PlayerController::class, 'modes'])->name('player.modes');
        Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->name('player.leaderboard');
        Route::get('/rewards', [RewardsController::class, 'index'])->name('player.rewards.index');
        Route::get('/quiz/{quiz?}', [PlayerController::class, 'quiz'])->name('player.quiz');
        Route::post('/quiz/{quiz}/submit', [PlayerController::class, 'submitQuiz'])->name('player.quiz.submit');
        Route::get('/quiz/{quiz}/result', [PlayerController::class, 'quizResult'])->name('player.quiz.result');
        Route::get('/player/game/{city}', [PlayerController::class, 'game'])->name('player.game');
        Route::get('/player/adventure/setup/{city}', [PlayerController::class, 'adventureSetup'])->name('player.adventure.setup');
        Route::get('/player/adventure/solo/{city}', [PlayerController::class, 'startSoloQuest'])->name('player.adventure.solo');
        Route::post('/player/adventure/launch/{city}', [PlayerController::class, 'launchAdventure'])->name('player.adventure.launch');
        Route::get('/player/mission-lobby/{lobbySessionId}', [PlayerController::class, 'showMissionLobby'])->name('player.mission-lobby');
        Route::post('/player/mission-lobby/{lobbySessionId}/invite', [PlayerController::class, 'invitePlayer'])->name('player.mission-lobby.invite');
        Route::post('/player/mission-lobby/{lobbySessionId}/join', [PlayerController::class, 'joinMissionLobby'])->name('player.mission-lobby.join');
        Route::post('/player/mission-lobby/{lobbySessionId}/start', [PlayerController::class, 'startMissionWithPlayers'])->name('player.mission-lobby.start');
        Route::post('/player/location/{location}/unlock', [PlayerController::class, 'unlockLocation'])->name('player.unlock-location');
        Route::post('/player/location/{location}/complete', [PlayerController::class, 'completeLocation'])->name('player.complete-location');
        Route::post('/player/update-position', [PlayerController::class, 'updatePosition'])->name('player.update-position');
        Route::get('/api/missions/{city}', [PlayerController::class, 'getMissionDetails'])->name('api.missions.show');
        Route::get('/api/available-players', [PlayerController::class, 'getAvailablePlayers'])->name('api.available-players');
        Route::get('/mission/join/{lobbySessionId}', [PlayerController::class, 'joinMissionLobby'])
    ->name('mission.join-link')->middleware('signed');

        // Team Routes
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::post('/teams/{team}/start-quest/{city}', [TeamController::class, 'startQuest'])->name('teams.start-quest');
        Route::get('/teams/{team}/join-game/{city}', [TeamController::class, 'joinGame'])->name('teams.join-game');

        // Player Hub & Shop
        Route::get('/player/competitions', [CompetitionController::class, 'playerIndex'])->name('player.competitions');
        Route::get('/player/competitions/{competition}', [CompetitionController::class, 'show'])->name('player.competitions.show');
        Route::post('/player/competitions/{competition}/join', [CompetitionController::class, 'join'])->name('player.competitions.join');
        Route::post('/player/competitions/{competition}/charge', [CompetitionController::class, 'charge'])->name('player.competitions.charge');
        
        Route::get('/player/hub', [PlayerController::class, 'hub'])->name('player.hub');
        Route::get('/player/shop', [PlayerController::class, 'shop'])->name('player.shop');
        Route::post('/player/buy-heart', [PlayerController::class, 'buyHeart'])->name('player.buy.heart');
        Route::post('/player/buy-diamonds', [PlayerController::class, 'buyDiamonds'])->name('player.buy.diamonds');
        Route::post('/player/buy-explorer-pass', [PlayerController::class, 'buyExplorerPass'])->name('player.buy.explorer-pass');
        Route::post('/player/buy-xp-boost', [PlayerController::class, 'buyXpBoost'])->name('player.buy.xp-boost');
        Route::get('/city/{city}/events/{event}', [CityEventController::class, 'show'])->name('mairie.events.show');
    });

    // --- ROUTES ADMIN & MAIRIE (COMMUNES) ---
    Route::middleware('role:mairie,super_admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::post('/admin/mairie-city', [AdminController::class, 'storeMairieWithCity'])->name('admin.mairie-city.store');
        Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');

        Route::get('/admin/cities/{city}/quizzes', [AdminController::class, 'cityQuizzes'])->name('admin.cities.quizzes');
        Route::post('/admin/cities/{city}/quizzes', [AdminController::class, 'storeQuiz'])->name('admin.quizzes.store');
        Route::delete('/admin/quizzes/{quiz}', [AdminController::class, 'deleteQuiz'])->name('admin.quizzes.delete');

        Route::post('/admin/quizzes/{quiz}/questions', [AdminController::class, 'storeQuestion'])->name('admin.questions.store');
        Route::delete('/admin/questions/{question}', [AdminController::class, 'deleteQuestion'])->name('admin.questions.delete');

        Route::get('/admin/city/{city}', [AdminController::class, 'showCity'])->name('admin.city.show');
        Route::post('/admin/mairie', [MairieController::class, 'store'])->name('admin.mairie.store');
        Route::post('/admin/location', [AdminController::class, 'storeLocation'])->name('admin.location.store');
        Route::post('/admin/enigma', [AdminController::class, 'storeEnigma'])->name('admin.enigma.store');
        Route::post('/admin/user/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.user.toggle-status');

        // --- Spécifiques Mairie ---
        Route::get('/mairie/city/{city}/hub', [MairieController::class, 'cityHub'])->name('mairie.city.hub');
        Route::post('/mairie/cities', [MairieController::class, 'storeCity'])->name('mairie.cities.store');
        Route::post('/mairie/cities/{city}/update', [MairieController::class, 'updateCity'])->name('mairie.cities.update');
        Route::get('/mairie/city/{city}', [MairieController::class, 'showCity'])->name('mairie.cities.show');
        Route::post('/mairie/city/{city}/location', [MairieController::class, 'storeLocation'])->name('mairie.locations.store');
        Route::post('/mairie/location/{location}/update', [MairieController::class, 'updateLocation'])->name('mairie.locations.update');
        Route::post('/mairie/location/{location}/enigma', [MairieController::class, 'storeEnigma'])->name('mairie.enigmas.store');
        Route::post('/mairie/location/{location}/image', [MairieController::class, 'storeLocationImage'])->name('mairie.locations.image');

        Route::patch('/mairie/city/{city}/toggle', [MairieController::class, 'toggleStatus'])->name('mairie.city.toggle');

        // Mairie Event Routes - kamal
        Route::get('/mairie/city/{city}/events', [CityEventController::class, 'index'])->name('mairie.cities.events');
        Route::post('/mairie/city/{city}/events', [CityEventController::class, 'store'])->name('mairie.events.store');
        Route::put('/mairie/events/{event}', [CityEventController::class, 'store'])->name('mairie.events.edit');
        Route::delete('/mairie/events/{event}', [CityEventController::class, 'delete'])->name('mairie.events.delete');

        // Mairie Competition Routes - kamal
        Route::get('/mairie/events/{event}/competitions', [CompetitionController::class, 'index'])->name('mairie.events.competitions');
        Route::post('/mairie/competitions', [CompetitionController::class, 'store'])->name('mairie.competitions.store');
        Route::delete('/mairie/competitions/{competition}', [CompetitionController::class, 'destroy'])->name('mairie.competitions.destroy');
    });
});

Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/player/buy-heart', [PlayerController::class, 'buyHeart'])->name('player.buy.heart');
        Route::post('/player/use-hint', [PlayerController::class, 'useHint'])->name('player.use-hint');
        // Achat d'une boussole (coûte 1000 XP)
        Route::post('/player/purchase-compass', [PlayerController::class, 'purchaseCompass'])->name('player.purchase-compass');
        Route::post('/player/quiz/{quiz}/retry', [PlayerController::class, 'retryQuiz'])->name('player.quiz.retry');
        Route::post('/notifications/{notification}/read', [PlayerController::class, 'markNotificationRead'])->name('notifications.read');
        
        // Rewards & Prizes
        Route::post('/player/prizes/{prize}/open', [RewardsController::class, 'openPrize'])->name('player.prizes.open');
    });

require __DIR__ . '/auth.php';