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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cities', 'public');
            $validated['image_path'] = '/storage/' . $path;
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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $validated['images'] = ['/storage/' . $path];
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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $validated['images'] = ['/storage/' . $path];
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
            'image' => 'nullable|image|max:2048', // 2MB max
            'is_site_specific' => 'boolean',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('enigmas', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $enigmaId = $validated['id'] ?? null;
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
