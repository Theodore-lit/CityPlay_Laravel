<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\City;
use App\Models\GameSession;
use Inertia\Inertia;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\User;
use App\Models\Location;
use App\Models\Enigma;
use Illuminate\Http\Request;

use App\Models\EnigmaResponse;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'mairies' => City::with('creator', 'locations')->get(),
            'locations' => Location::with('city', 'locationImages', 'enigmas')->latest()->take(6)->get(),
            'stats' => [
                'total_sessions' => GameSession::count(),
                'active_players' => GameSession::where('status', 'in_progress')->count(),
                'total_locations' => Location::count(),
            ]
        ]);
    }

    public function showCity(City $city)
    {
        return Inertia::render('Admin/CityShow', [
            'city' => $city->load('locations.enigmas.responses', 'creator'),
        ]);
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $location = Location::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('locations', 'public');
                $location->locationImages()->create(['image_path' => $path]);
            }
        }

        return back()->with('success', 'Lieu créé avec succès');
    }

    public function storeEnigma(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'difficulty' => 'required|string',
            'answer' => 'nullable|string',
            'reward_coins' => 'required|integer',
            'reward_hearts' => 'required|integer',
            'type' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'responses' => 'required|array|min:1',
            'responses.*.content' => 'required|string',
            'responses.*.is_correct' => 'required|boolean',
        ]);

        $enigmaData = $validated;
        unset($enigmaData['responses']);
        
        if ($request->hasFile('image')) {
            $enigmaData['image_path'] = $request->file('image')->store('enigmas', 'public');
        }

        $enigma = Enigma::create($enigmaData);

        foreach ($validated['responses'] as $response) {
            $enigma->responses()->create($response);
        }

        return back()->with('success', 'Énigme créée avec succès');
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
    public function store(StoreAdminRequest $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
