<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MairieController extends Controller
{
    public function dashboard()
    {
        // Mairie only sees their own cities
        $cities = City::where('creator_id', auth()->id())->withCount('locations')->get();
        
        return Inertia::render('Mairie/Dashboard', [
            'cities' => $cities,
            'stats' => [
                'total_sessions' => GameSession::whereIn('city_id', $cities->pluck('id'))->count(),
                'active_players' => GameSession::whereIn('city_id', $cities->pluck('id'))
                    ->where('status', 'in_progress')->count(),
            ]
        ]);
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:100',
        ]);

        $city = City::create([
            ...$validated,
            'creator_id' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Ville créée avec succès.');
    }

    public function toggleStatus(City $city)
    {
        $city->is_active = !$city->is_active;
        $city->save();

        return redirect()->back()->with('success', 'Statut de la ville mis à jour.');
    }

    public function showCity(City $city)
    {
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Mairie/CityShow', [
            'city' => $city->load(['locations.enigmas']),
        ]);
    }

    public function storeLocation(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $city->locations()->create($validated);

        return redirect()->back()->with('success', 'Lieu ajouté avec succès.');
    }

    public function storeEnigma(Request $request, \App\Models\Location $location)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:enigmas,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'answer' => 'required|string',
            'indices' => 'nullable|array',
            'reward_coins' => 'required|integer|min:0',
            'reward_hearts' => 'required|integer|min:0',
            'type' => 'required|string', // text, qr, image
            'image' => 'nullable|image|max:2048', // 2MB max
            'is_site_specific' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('enigmas', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $enigmaId = $validated['id'] ?? null;
        unset($validated['id'], $validated['image']);

        $location->enigmas()->updateOrCreate(
            ['id' => $enigmaId],
            $validated
        );

        return redirect()->back()->with('success', 'Énigme enregistrée avec succès.');
    }

    public function storeLocationImage(Request $request, \App\Models\Location $location)
    {
        $validated = $request->validate([
            'image_url' => 'required|url',
        ]);

        $location->images = [$validated['image_url']];
        $location->save();

        return redirect()->back()->with('success', 'Image du lieu mise à jour.');
    }
}
