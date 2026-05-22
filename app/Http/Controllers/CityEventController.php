<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityEvent;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityEventController extends Controller
{
    // kamal
    /**
     * Affiche la liste des événements pour une mairie donnée.
     * Logique liée à l'administration des événements par la mairie.
     */
    public function index(City $city)
    {
        //kamal Vérification des droits d'accès : Seuls les admins et mairies peuvent gérer les événements
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        return Inertia::render('Mairie/CityEvents', [
            'city' => $city->load(['events' => function($query) {
                $query->latest(); // Charge les derniers événements en premier pour une meilleure visibilité
            }]),
        ]);
    }

    /**
     * Crée ou met à jour un événement.
     * Gère la persistance des données et le stockage des fichiers médias.
     */
public function store(Request $request, $id) // On prend l'ID brut pour rester flexible
{
    // 1. Validation des données entrantes (titre, date, prix en diamants, etc.) kamal
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

    // 2. Logique de récupération ou d'instanciation du modèle CityEvent kamal
    if ($request->has('id') && $request->id) {
        // Mode ÉDITION : Récupération de l'existant
        $event = CityEvent::findOrFail($request->id);
    } else {
        // Mode CRÉATION : Nouvelle instance rattachée à la ville spécifiée
        $city = City::findOrFail($id);
        $event = new CityEvent(['city_id' => $city->id]);
    }

    // 3. Gestion des images : mix entre les images existantes conservées et les nouvelles téléchargées kamal
    $images = StorageUrl::diskPaths($validated['existing_images'] ?? []);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Stockage physique des images dans le dossier public/events
            $images[] = $image->store('events', 'public');
        }
    }

    // 4. Remplissage des attributs et sauvegarde en base de données
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
     * Supprime un événement du système.
     */
    public function delete(CityEvent $event)
    {
        // Sécurité renforcée pour la suppression
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        $event->delete();
        return redirect()->back()->with('success', 'Protocole supprimé : Événement effacé.');
    }

    /**
     * Affiche les détails d'un événement spécifique.
     * Utilisé pour la vue publique ou détaillée côté mairie.
     */
    public function show(City $city, CityEvent $event)
    {
        return Inertia::render('Mairie/EventShow', [
            'event' => $event,
            'city' => $city
        ]);
    }
}
