<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use App\Models\User;
use App\Support\StorageUrl;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MairieController extends Controller
{
    /**
     * Affiche le tableau de bord de la mairie.
     * Redirige vers le hub de la ville gérée par l'utilisateur connecté.
     */
    public function dashboard()
    {
        // Les super_admins ont leur propre tableau de bord
        if (auth()->user()->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        // Récupère la ville associée au compte mairie kamal
        $city = City::where('mairie_id', auth()->id())->first();

        if ($city) {
            return redirect()->route('mairie.city.hub', $city->id);
        }

        // Cas de secours si aucune ville n'est encore créée
        return Inertia::render('Admin/City', [
            'city' => [],
            ]);
    }

    /**
     * Crée une nouvelle ville et son compte mairie associé.
     * Utilisé par les administrateurs.
     */
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

        $user = auth()->user();

        // Création automatique du compte mairie kamal
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
     * Met à jour les informations globales d'une ville.
     */
    public function updateCity(Request $request, City $city)
    {
        // Vérification des droits kamal
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
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
            // Nettoyage de l'ancienne image kamal
            if ($diskPath = StorageUrl::diskPath($city->image_path)) {
                Storage::disk('public')->delete($diskPath);
            }
            $path = $request->file('image')->store('cities', 'public');
            $validated['image_path'] = $path;
        }

        $city->update($validated);

        return redirect()->back()->with('success', 'Ville mise à jour avec succès.');
    }

    /**
     * Active ou désactive une ville.
     */
    public function toggleStatus(City $city)
    {
        $city->is_active = !$city->is_active;
        $city->save();

        return redirect()->back()->with('success', 'Statut de la ville mis à jour.');
    }

    /**
     * Affiche les détails d'une ville (Lieux, Énigmes, etc.).
     */
    public function showCity(City $city)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        return Inertia::render('Mairie/CityShow', [
            'city' => $city->load(['locations.enigmas.questions.options']),
        ]);
    }

    /**
     * Affiche le hub de gestion de la ville avec les compteurs.
     */
    public function cityHub(City $city)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'mairie') {
            abort(403);
        }

        return Inertia::render('Mairie/CityHub', [
            'city' => $city->loadCount(['locations', 'events', 'quizzes']),
        ]);
    }

    /**
     * Ajoute un nouveau lieu à la ville avec ses récompenses XP.
     */
    public function storeLocation(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'reward_xp_arrival' => 'nullable|integer|min:0', // XP gagnés à l'arrivée kamal
            'reward_xp_enigma' => 'nullable|integer|min:0',  // XP gagnés par énigme kamal
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

    /**
     * Met à jour un lieu existant et ses récompenses.
     */
    public function updateLocation(Request $request, \App\Models\Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'reward_xp_arrival' => 'nullable|integer|min:0', // XP gagnés à l'arrivée kamal
            'reward_xp_enigma' => 'nullable|integer|min:0',  // XP gagnés par énigme kamal
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Nettoyage des anciennes images kamal
            if (!empty($location->images) && is_array($location->images)) {
                foreach (StorageUrl::diskPaths($location->images) as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->store('locations', 'public');
            $validated['images'] = [$path];
        }

        $location->update($validated);

        return redirect()->back()->with('success', 'Lieu mis à jour avec succès.');
    }

    /**
     * Crée ou met à jour une énigme liée à un lieu.
     * Gère également les questions à choix multiples.
     */
    public function storeEnigma(Request $request, \App\Models\Location $location)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:enigmas,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'answer' => 'required|string',
            'indices' => 'nullable|array',
            'reward_coins' => 'required|integer|min:0', // Utilisé comme XP kamal
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
            // Nettoyage de l'image kamal
            if ($enigma && ($diskPath = StorageUrl::diskPath($enigma->image_path))) {
                Storage::disk('public')->delete($diskPath);
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

        // Synchronisation des questions : suppression et recréation kamal
        if (!empty($questions)) {
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

    /**
     * Met à jour uniquement l'image d'un lieu.
     */
    public function storeLocationImage(Request $request, \App\Models\Location $location)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('locations', 'public');
            $location->images = [$path];
            $location->save();
        }

        return redirect()->back()->with('success', 'Image du lieu mise à jour.');
    }


    public function deleteLocation(\App\Models\Location $location)
    {
        // Security check for Mairie
        if (auth()->user()->role !== 'mairie' && auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        // Delete images if exist
        if (!empty($location->images) && is_array($location->images)) {
            foreach (StorageUrl::diskPaths($location->images) as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $location->delete();

        return redirect()->back()->with('success', 'Secteur supprimé avec succès.');
    }

}
