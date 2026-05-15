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
            'cities' => City::where('is_active', true)->get()
        ]);
    }

    public function game(City $city)
    {
        return Inertia::render('Player/Game', [
            'city' => $city->load('locations.enigmas')
        ]);
    }
}
