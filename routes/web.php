<?php

use App\Http\Controllers\AdminController;
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
});

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
    Route::get('/player/game/{city}', [PlayerController::class, 'game'])->name('player.game');

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/city/{city}', [AdminController::class, 'showCity'])->name('admin.city.show');
    Route::post('/admin/mairie', [MairieController::class, 'store'])->name('admin.mairie.store');
    Route::post('/admin/location', [AdminController::class, 'storeLocation'])->name('admin.location.store');
    Route::post('/admin/enigma', [AdminController::class, 'storeEnigma'])->name('admin.enigma.store');
    Route::post('/admin/user/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.user.toggle-status');
    // Mairie Routes
    Route::get('/mairie', [MairieController::class, 'dashboard'])->name('mairie.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
