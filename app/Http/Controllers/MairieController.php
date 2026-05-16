<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use App\Models\Location;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MairieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $user = auth()->user();
        // On cherche la ville où l'utilisateur est assigné comme maire (mairie_id)
        $city = City::where('mairie_id', $user->id)->first();

        if (!$city) {
            return Inertia::render('Admin/CityShow', [
                'city' => null,
                'locations' => [],
                'stats' => [
                    'total_sessions' => 0,
                    'active_players' => 0,
                ]
            ]);
        }

        return Inertia::render('Admin/CityShow', [
            'city' => $city->load('locations.locationImages', 'locations.enigmas.responses', 'creator', 'mairie'),
            'isMairie' => true
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
        $user = auth()->user();
        // dd($request);

        $validated = $request->validate([
            'city_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'opening_hours' => 'nullable|array',
        ]);


        $marie = User::create([
            'name' => $validated['city_name'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'role' => 'mairie',
        ]);

        $cityDataImage = null;
        if ($request->hasFile('image')) {
            $cityDataImage = $request->file('image')->store('cities', 'public');
        }

        City::create([
            'name' => $validated['city_name'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $request->input('radius_meters', 5000),
            'is_active' => $request->input('is_active', true),
            'creator_id' => $user->id,
            'mairie_id' => $marie->id,
            'image_path' => $cityDataImage,
            'opening_hours' => $validated['opening_hours'],
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
