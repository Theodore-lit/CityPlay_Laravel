<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CitySeeder::class,
            LocationSeeder::class,
            EnigmaSeeder::class,
            QuizSeeder::class,
            MassiveQuizSeeder::class,
            EnigmeQuestionSeeder::class,
            TeamSeeder::class,
        ]);

        $this->seedTeamsAndSessions();
    }

    private function seedTeamsAndSessions(): void
    {
        $players = User::where('role', 'joueur')->get();
        $cities = \App\Models\City::all();
        $enigmas = \App\Models\Enigma::with('location')->get();

        // Créer quelques équipes
        for ($i = 1; $i <= 5; $i++) {
            $creator = $players->random();
            $team = \App\Models\Team::create([
                'name' => "Team " . ['Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon'][$i-1],
                'creator_id' => $creator->id,
                'invite_code' => strtoupper(bin2hex(random_bytes(3))),
                'member_limit' => 5,
            ]);

            // Ajouter quelques membres (pivot table team_user)
            $members = $players->random(rand(1, 4))->pluck('id')->toArray();
            if (!in_array($creator->id, $members)) {
                $members[] = $creator->id;
            }
            $team->members()->attach($members);
        }

        // Créer quelques sessions de jeu
        foreach ($players->take(10) as $player) {
            $city = $cities->random();
            $cityEnigmas = $enigmas->filter(function ($e) use ($city) {
                return $e->location->city_id === $city->id;
            });

            if ($cityEnigmas->isNotEmpty()) {
                $enigma = $cityEnigmas->random();

                \App\Models\GameSession::create([
                    'user_id' => $player->id,
                    'city_id' => $city->id,
                    'status' => 'in_progress',
                    'total_score' => rand(0, 50),
                    'total_coins' => rand(0, 20),
                    'total_hearts' => 3,
                    'current_enigma_id' => $enigma->id,
                    'start_time' => now()->subMinutes(rand(10, 60)),
                    'winner_id' => null,
                    'lobby_session_id' => null,
                ]);
            }
        }

        // Sessions d'équipes
        $teams = \App\Models\Team::all();
        foreach ($teams as $team) {
            $city = $cities->random();
            $cityEnigmas = $enigmas->filter(function ($e) use ($city) {
                return $e->location->city_id === $city->id;
            });

            if ($cityEnigmas->isNotEmpty()) {
                $enigma = $cityEnigmas->random();

                \App\Models\GameSession::create([
                    'team_id' => $team->id,
                    'city_id' => $city->id,
                    'status' => 'in_progress',
                    'total_score' => rand(0, 100),
                    'total_coins' => rand(0, 50),
                    'total_hearts' => 3,
                    'current_enigma_id' => $enigma->id,
                    'start_time' => now()->subMinutes(rand(30, 120)),
                    'winner_id' => null,
                    'lobby_session_id' => null,
                ]);
            }
        }
    }
}
