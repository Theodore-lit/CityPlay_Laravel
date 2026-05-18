<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cityplay.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_verified' => true,
        ]);

        // Comptes Mairies pour les villes principales
        $cities = ['Cotonou', 'Ouidah', 'Porto-Novo', 'Parakou', 'Abomey'];
        foreach ($cities as $cityName) {
            User::create([
                'name' => "Mairie de $cityName",
                'email' => strtolower($cityName) . '@mairie.bj',
                'password' => Hash::make('password'),
                'role' => 'mairie',
                'is_verified' => true,
            ]);
        }

        // 20 Joueurs avec des données
        $players = [
            ['name' => 'Jean Dupont', 'email' => 'jean@example.com'],
            ['name' => 'Marie Silva', 'email' => 'marie@example.com'],
            ['name' => 'Koffi Agossou', 'email' => 'koffi@example.com'],
            ['name' => 'Amina Bello', 'email' => 'amina@example.com'],
            ['name' => 'Sessi Zinsou', 'email' => 'sessi@example.com'],
            ['name' => 'Tunde Bio', 'email' => 'tunde@example.com'],
            ['name' => 'Femi Adeyemi', 'email' => 'femi@example.com'],
            ['name' => 'Yemi Alade', 'email' => 'yemi@example.com'],
            ['name' => 'Babatunde Raji', 'email' => 'babatunde@example.com'],
            ['name' => 'Chidi Okafor', 'email' => 'chidi@example.com'],
            ['name' => 'Fatima Toure', 'email' => 'fatima@example.com'],
            ['name' => 'Ibrahim Kone', 'email' => 'ibrahim@example.com'],
            ['name' => 'Kwame Nkrumah', 'email' => 'kwame@example.com'],
            ['name' => 'Lamine Gueye', 'email' => 'lamine@example.com'],
            ['name' => 'Moussa Diop', 'email' => 'moussa@example.com'],
            ['name' => 'Nneka Ebele', 'email' => 'nneka@example.com'],
            ['name' => 'Ousmane Sylla', 'email' => 'ousmane@example.com'],
            ['name' => 'Penda Sow', 'email' => 'penda@example.com'],
            ['name' => 'Quincy Jones', 'email' => 'quincy@example.com'],
            ['name' => 'Rachidatou Diallo', 'email' => 'rachidatou@example.com'],
        ];

        foreach ($players as $index => $playerData) {
            User::create([
                'name' => $playerData['name'],
                'email' => $playerData['email'],
                'password' => Hash::make('password'),
                'role' => 'joueur',
                'is_verified' => true,
                'coins' => rand(50, 500),
                'hearts' => rand(1, 5),
                'xp' => rand(100, 5000),
                'level' => rand(1, 10),
            ]);
        }
    }
}
