<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\User;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des mairies
        $mairieCotonou = User::where('email', 'cotonou@mairie.bj')->first();
        $mairieOuidah = User::where('email', 'ouidah@mairie.bj')->first();

        // Récupération d'un admin par défaut pour le creator_id
        $admin = User::where('email', 'admin@cityplay.bj')->first() ?? User::first();

        // 1. Cotonou
        if ($mairieCotonou) {
            City::updateOrCreate(
                ['name' => 'Cotonou'],
                [
                    'description' => 'La métropole bouillonnante du Bénin. Entre le marché Dantokpa et la Place de l\'Amazone, vivez l\'effervescence d\'une ville qui ne dort jamais.',
                    'latitude' => 6.3654,
                    'longitude' => 2.4183,
                    'is_active' => true,
                    'creator_id' => $admin->id,
                    'mairie_id' => $mairieCotonou->id,
                    'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
                    'image_path' => 'images/cities/city-cotonou.jpg',
                ]
            );
        }

        // 2. Ouidah
        if ($mairieOuidah) {
            City::updateOrCreate(
                ['name' => 'Ouidah'],
                [
                    'description' => 'Capitale mondiale du Vodoun et cité historique. Parcourez la Route des Esclaves et découvrez les mystères du Temple des Pythons.',
                    'latitude' => 6.3623,
                    'longitude' => 2.0850,
                    'is_active' => true,
                    'creator_id' => $admin->id,
                    'mairie_id' => $mairieOuidah->id,
                    'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
                    'image_path' => 'images/cities/city-ouidah.jpg',
                ]
            );
        }
    }
}
