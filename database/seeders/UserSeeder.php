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
            'accepts_terms'=> true,
            'is_active' => true,
        ]);

        // Mairies
        $mairies = [
            'Cotonou' => 'mairie.cotonou@cityplay.bj',
            'Porto-Novo' => 'mairie.portonovo@cityplay.bj',
            'Calavi' => 'mairie.calavi@cityplay.bj',
            'Parakou' => 'mairie.parakou@cityplay.bj',
            'Ouidah' => 'mairie.ouidah@cityplay.bj',
            'accepts_terms'=> true,
            'is_active' => true,
        ];

        foreach ($mairies as $name => $email) {
            User::create([
                'name' => "Mairie de $name",
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'mairie',
                'accepts_terms'=> true,
                'is_active' => true,
            ]);
        }

        // Joueurs (au moins 20)
        for ($i = 1; $i <= 25; $i++) {
            User::create([
                'name' => "Joueur $i",
                'email' => "player$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'joueur',
                'coins' => rand(0, 100),
                'hearts' => rand(1, 5),
                'accepts_terms'=> true,
                'is_active' => true,
            ]);
        }
    }
}
