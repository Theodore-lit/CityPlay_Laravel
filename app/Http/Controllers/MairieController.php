<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MairieController extends Controller
{
    public function dashboard()
    {
        // Admin should not access mairie dashboard
        if (auth()->user()->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        // Mairie only sees their own cities
        $city = City::where('creator_id', auth()->id())->first();

        if ($city) {
            return redirect()->route('mairie.cities.show', $city->id);
        }

        // Fallback if no city created yet (though should not happen with current flow)
        return Inertia::render('Mairie/Dashboard', [
            'cities' => [],
            'stats' => [
                'total_sessions' => 0,
                'active_players' => 0,
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cities', 'public');
            $validated['image_path'] = $path;
        }

        $city = City::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'image_path' => $validated['image_path'] ?? null,
            'creator_id' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Ville créée avec succès.');
    }

    public function updateCity(Request $request, City $city)
    {
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($city->image_path) {
                Storage::disk('public')->delete($city->image_path);
            }
            $path = $request->file('image')->store('cities', 'public');
            $validated['image_path'] = $path;
        }

        $city->update($validated);

        return redirect()->back()->with('success', 'Ville mise à jour avec succès.');
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
            'city' => $city->load(['locations.enigmas.questions.options']),
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $validated['images'] = [$path];
        }

        $city->locations()->create($validated);

        return redirect()->back()->with('success', 'Lieu ajouté avec succès.');
    }

    public function updateLocation(Request $request, \App\Models\Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old images if exist (assuming first image for now based on previous code)
            if (!empty($location->images) && is_array($location->images)) {
                foreach ($location->images as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->store('locations', 'public');
            $validated['images'] = [$path];
        }

        $location->update($validated);

        return redirect()->back()->with('success', 'Lieu mis à jour avec succès.');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_site_specific' => 'boolean',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $enigmaId = $validated['id'] ?? null;
        $enigma = $enigmaId ? \App\Models\Enigma::find($enigmaId) : null;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($enigma && $enigma->image_path) {
                Storage::disk('public')->delete($enigma->image_path);
            }
            $path = $request->file('image')->store('enigmas', 'public');
            $validated['image_path'] = $path;
        }

        $questions = $validated['questions'] ?? [];

        unset($validated['id'], $validated['image'], $validated['questions']);

        $enigma = $location->enigmas()->updateOrCreate(
            ['id' => $enigmaId],
            $validated
        );

        // Sync questions
        if (!empty($questions)) {
            // Simple approach: delete existing questions and recreate
            // (In production, you might want a more sophisticated sync)
            $enigma->questions()->delete();

            foreach ($questions as $qData) {
                $question = $enigma->questions()->create([
                    'question_text' => $qData['question_text']
                ]);

                foreach ($qData['options'] as $oData) {
                    $question->options()->create($oData);
                }
            }
        }

        return redirect()->back()->with('success', 'Énigme enregistrée avec succès.');
    }

    public function storeLocationImage(Request $request, \App\Models\Location $location)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $location->images = ['/storage/' . $path];
            $location->save();
        }

        return redirect()->back()->with('success', 'Image du lieu mise à jour.');
    }

    public function cityEvents(City $city)
    {
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Mairie/CityEvents', [
            'city' => $city->load('events'),
        ]);
    }

    public function storeEvent(Request $request, City $city)
    {
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'id' => 'nullable|exists:city_events,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'location_name' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
            'existing_images' => 'nullable|array',
        ]);

        $eventId = $validated['id'] ?? null;
        $images = $validated['existing_images'] ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('events', 'public');
                $images[] = '/storage/' . $path;
            }
        }

        $city->events()->updateOrCreate(
            ['id' => $eventId],
            [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'event_date' => $validated['event_date'],
                'location_name' => $validated['location_name'],
                'images' => $images,
                'is_active' => true,
            ]
        );

        return redirect()->back()->with('success', 'Événement enregistré avec succès.');
    }

    public function deleteEvent(\App\Models\CityEvent $event)
    {
        $city = $event->city;
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        $event->delete();
        return redirect()->back()->with('success', 'Événement supprimé.');
    }
}
