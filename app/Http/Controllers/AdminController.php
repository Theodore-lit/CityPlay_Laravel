<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (auth()->user()->role !== 'super_admin') {

            $city = City::where('mairie_id', auth()->id())->first();

            if ($city) {
                return redirect()->route('mairie.cities.show', $city->id);
            }

            // return redirect()->route('mairie.dashboard');
        }

        return Inertia::render('Admin/Dashboard', [
            'mairies' => User::where('role', 'mairie')->get(),
            'players' => User::where('role', 'joueur')->orderBy('created_at', 'desc')->paginate(5, ['*'], 'players'),
            'cities' => City::with(['creator'])->withCount(['locations', 'quizzes'])->paginate(5, ['*'], 'cities'), // Super Admin voit toutes les villes
            'stats' => [
                'total_sessions' => GameSession::count(),
                'active_players' => GameSession::where('status', 'in_progress')->count(),
                'total_users' => User::count(),
            ]
        ]);
    }

    public function cityQuizzes(City $city)
    {
        // Security check for Mairie
        if (auth()->user()->role === 'mairie' && $city->creator_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à gérer les quiz de cette ville.');
        }

        return Inertia::render('Admin/Quizzes', [
            'city' => $city->load('quizzes.questions'),
        ]);
    }

    public function storeQuiz(Request $request, City $city)
    {
        // Security check for Mairie
        if (auth()->user()->role === 'mairie' && $city->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'id' => 'nullable|exists:quizzes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'xp_reward' => 'required|integer|min:0',
            'time_limit' => 'required|integer|min:10',
        ]);

        $quizId = $validated['id'] ?? null;
        unset($validated['id']);

        $quiz = $city->quizzes()->updateOrCreate(
            ['id' => $quizId],
            $validated
        );

        return redirect()->back()->with('success', 'Quiz enregistré avec succès.');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        // Security check for Mairie
        if (auth()->user()->role === 'mairie' && $quiz->city->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'id' => 'nullable|exists:questions,id',
            'question_text' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_option' => 'required|string',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
        ]);

        $questionId = $validated['id'] ?? null;
        unset($validated['id']);

        $quiz->questions()->updateOrCreate(
            ['id' => $questionId],
            $validated
        );

        return redirect()->back()->with('success', 'Question enregistrée avec succès.');
    }

    public function deleteQuiz(Quiz $quiz)
    {
        // Security check for Mairie
        if (auth()->user()->role === 'mairie' && $quiz->city->creator_id !== auth()->id()) {
            abort(403);
        }

        $quiz->delete();
        return redirect()->back()->with('success', 'Quiz supprimé.');
    }

    public function deleteQuestion(Question $question)
    {
        // Security check for Mairie
        if (auth()->user()->role === 'mairie' && $question->quiz->city->creator_id !== auth()->id()) {
            abort(403);
        }

        $question->delete();
        return redirect()->back()->with('success', 'Question supprimée.');
    }

    public function toggleUserStatus(User $user)
    {
        // On suppose qu'on a un champ 'is_active' sur User, ou on utilise une autre logique
        // Pour l'instant, simulons avec une désactivation de compte
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', 'Statut de l\'utilisateur mis à jour.');
    }

    public function storeMairieWithCity(Request $request)
    {
        $validated = $request->validate([
            'city_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
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

        // Créer le compte Mairie
        $mairie = User::create([
            'name' => "Mairie de " . $validated['city_name'],
            'email' => $validated['email'],
            'password' => bcrypt('password123'), // Mot de passe par défaut
            'role' => 'mairie',
        ]);

        // Créer la Ville
        City::create([
            'name' => $validated['city_name'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'image_path' => $validated['image_path'] ?? null,
            'creator_id' => $mairie->id,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Mairie et ville créées avec succès.');
    }
}
