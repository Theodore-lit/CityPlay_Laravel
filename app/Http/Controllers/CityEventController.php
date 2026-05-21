<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityEvent;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityEventController extends Controller
{
    /**
     * Affiche la liste des événements pour une mairie donnée.
     */
    public function index(City $city)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        return Inertia::render('Mairie/CityEvents', [
            'city' => $city->load(['events' => function($query) {
                $query->latest(); // Charge les derniers événements en premier
            }]),
        ]);
    }

    /**
     * Crée ou met à jour un événement.
     */
public function store(Request $request, $id) // On prend l'ID brut pour rester flexible
{
    // 1. Validation
    $validated = $request->validate([
        'id' => 'nullable|exists:city_events,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'event_date' => 'required|date',
        'location_name' => 'nullable|string|max:255',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|max:2048',
        'existing_images' => 'nullable|array',
        'diamond_price' => 'nullable|integer|min:0',
        'has_vip_pass' => 'boolean',
        'reward_type' => 'nullable|string',
        'is_active' => 'boolean',
    ]);

    // 2. Logique de récupération de l'instance
    if ($request->has('id') && $request->id) {
        // Mode ÉDITION : On récupère l'événement existant
        $event = CityEvent::findOrFail($request->id);
    } else {
        // Mode CRÉATION : On crée une nouvelle instance liée à la ville
        $city = City::findOrFail($id);
        $event = new CityEvent(['city_id' => $city->id]);
    }

    // 3. Gestion des images (Ton StorageUrl est une bonne idée)
    $images = StorageUrl::diskPaths($validated['existing_images'] ?? []);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $images[] = $image->store('events', 'public');
        }
    }

    // 4. Remplissage et Sauvegarde
    $event->fill([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'event_date' => $validated['event_date'],
        'location_name' => $validated['location_name'],
        'images' => $images,
        'diamond_price' => $validated['diamond_price'] ?? 0,
        'has_vip_pass' => $validated['has_vip_pass'] ?? false,
        'reward_type' => $validated['reward_type'],
        'is_active' => $validated['is_active'] ?? true,
    ]);

    $event->save();

    return redirect()->back()->with('success', 'Système mis à jour avec succès.');
}

    /**
     * Supprime un événement.
     */
    public function delete(CityEvent $event)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        $event->delete();
        return redirect()->back()->with('success', 'Protocole supprimé : Événement effacé.');
    }

    /**
     * Affiche un événement spécifique (Optionnel).
     */
    public function show(City $city, CityEvent $event)
    {
        // Pas besoin de transformer manuellement ici, l'attribut image_urls du model s'en charge via $appends
        return Inertia::render('Mairie/EventShow', [
            'event' => $event,
            'city' => $city
        ]);
    }
}
