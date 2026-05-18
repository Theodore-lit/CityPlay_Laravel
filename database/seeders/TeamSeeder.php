<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'joueur')->get();

        if ($players->count() < 10) {
            return;
        }

        // Créer quelques équipes
        $teamNames = ['Les Explorateurs du Sud', 'Dahomey Warriors', 'Zemidjan Express', 'Vaudou Hunters'];

        foreach ($teamNames as $index => $name) {
            $creator = $players[$index];
            $team = Team::create([
                'name' => $name,
                'creator_id' => $creator->id,
                'invite_code' => strtoupper(Str::random(6)),
                'member_limit' => 5,
            ]);

            // Ajouter le créateur comme leader
            $team->members()->attach($creator->id, ['role' => 'leader']);

            // Ajouter quelques membres aléatoires
            $members = $players->where('id', '!=', $creator->id)->random(rand(1, 4));
            foreach ($members as $member) {
                $team->members()->attach($member->id, ['role' => 'member']);
            }
        }
    }
}
