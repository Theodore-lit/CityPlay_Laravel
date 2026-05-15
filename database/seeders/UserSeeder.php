<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cityplay.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'name' => 'Mairie de Paris',
            'email' => 'paris@mairie.fr',
            'password' => Hash::make('password123'),
            'role' => 'mairie',
        ]);

        User::create([
            'name' => 'Jean Joueur',
            'email' => 'jean@player.com',
            'password' => Hash::make('password123'),
            'role' => 'joueur',
            'coins' => 10,
            'hearts' => 3,
        ]);
    }
}
