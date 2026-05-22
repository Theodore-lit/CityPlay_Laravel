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
        $mairiePorto = User::where('email', 'porto-novo@mairie.bj')->first();
        $mairieParakou = User::where('email', 'parakou@mairie.bj')->first();
        $mairieAbomey = User::where('email', 'abomey@mairie.bj')->first();

        // Récupération d'un admin par défaut pour le creator_id (au cas où)
        $admin = User::where('email', 'admin@cityplay.bj')->first() ?? User::first();

        // 1. Cotonou
        if ($mairieCotonou) {
            City::updateOrCreate(
                ['name' => 'Cotonou Vibrante'],
                [
                    'description' => 'La métropole bouillonnante du Bénin. Entre le marché Dantokpa et les plages de Fidjrossè, vivez l\'effervescence d\'une ville qui ne dort jamais.',
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
                ['name' => 'Ouidah Historique'],
                [
                    'description' => 'Capitale mondiale du Vodoun. Parcourez la Route des Esclaves et découvrez les mystères du Temple des Pythons.',
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

        // 3. Porto-Novo
        if ($mairiePorto) {
            City::updateOrCreate(
                ['name' => 'Porto-Novo Impériale'],
                [
                    'description' => 'La capitale aux trois noms. Admirez l\'architecture afro-brésilienne et les musées royaux de cette cité paisible.',
                    'latitude' => 6.4969,
                    'longitude' => 2.6289,
                    'is_active' => true,
                    'creator_id' => $admin->id,
                    'mairie_id' => $mairiePorto->id,
                    'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
                    'image_path' => 'images/cities/city-porto-novo.jpg',
                ]
            );
        }

        // 4. Parakou
        if ($mairieParakou) {
            City::updateOrCreate(
                ['name' => 'Parakou la Cité des Princes'],
                [
                    'description' => 'Le carrefour du Nord. Découvrez le palais des Kobourou et l\'hospitalité légendaire du peuple Baatonu.',
                    'latitude' => 9.3372,
                    'longitude' => 2.6303,
                    'is_active' => true,
                    'creator_id' => $admin->id,
                    'mairie_id' => $mairieParakou->id,
                    'opening_hours' => ['start' => '08:00', 'end' => '21:00'],
                    'image_path' => 'images/cities/city-parakou.jpg',
                ]
            );
        }

        // 5. Abomey
        if ($mairieAbomey) {
            City::updateOrCreate(
                ['name' => 'Abomey la Royale'],
                [
                    'description' => 'Le berceau des rois. Visitez les palais royaux et revivez l\'épopée des Amazones du Dahomey.',
                    'latitude' => 7.1855,
                    'longitude' => 1.9912,
                    'is_active' => true,
                    'creator_id' => $admin->id,
                    'mairie_id' => $mairieAbomey->id,
                    'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
                    'image_path' => 'images/cities/city-abomey.jpg',
                ]
            );
        }
    }
}
