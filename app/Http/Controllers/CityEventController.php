<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityEvent;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityEventController extends Controller
{


    public function index(City $city)
    {
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Mairie/CityEvents', [
            'city' => $city->load('events'),
        ]);
    }

    public function store(Request $request, City $city)
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
        $images = StorageUrl::diskPaths($validated['existing_images'] ?? []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('events', 'public');
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

    public function delete(CityEvent $event)
    {
        $city = $event->city;
        // Check if user is creator or super_admin
        if (auth()->user()->role !== 'super_admin' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        $event->delete();
        return redirect()->back()->with('success', 'Événement supprimé.');
    }

    // Exemple dans ton controller
    public function show(City $city, CityEvent $event)
    {
        // Assure-toi que les images sont transformées en URLs complètes
        $event->image_urls = collect($event->images)->map(fn($img) => asset('storage/' . $img));

        return Inertia::render('Mairie/EventShow', [
            'event' => $event,
            'city' => $city
        ]);
    }
}
