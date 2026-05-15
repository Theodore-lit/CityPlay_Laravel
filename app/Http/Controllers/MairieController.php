<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use App\Models\Location;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MairieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
       return Inertia::render('Mairie/Dashboard', [
            'cities' => Location::where('creator_id', auth()->id())->get(),
            'stats' => [
                'total_sessions' => GameSession::count(),
                'active_players' => GameSession::where('status', 'in_progress')->count(),
            ]
        ]);
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        dd($user);
       $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'description' => 'required|string|max:255',
            'latitude' => 'required|decimal',
            'longitude' => 'required|decimal',
            'image_path'=> 'nullable|string',
            'radius_meters' => 5000,
            'is_active' => true,
            'creator_id' => Auth::user()->id,
            'opening_hours' => 'nullable|Array',
        ]);

        $marie = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mairie',
        ]);
        City::create([
            'name' => $request->name,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
            'is_active' => $request->is_active,
            'creator_id' => 1,
            'mairie_id' => $marie->id,
            'opening_hours' => $request->opening_hours,
        ]);
        return redirect(route('admin.dashboard', absolute: false));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
