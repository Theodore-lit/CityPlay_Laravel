<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\User;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $mairieCotonou = User::where('email', 'cotonou@mairie.bj')->first();
        $mairieOuidah = User::where('email', 'ouidah@mairie.bj')->first();
        $mairiePorto = User::where('email', 'porto-novo@mairie.bj')->first();
        $mairieParakou = User::where('email', 'parakou@mairie.bj')->first();
        $mairieAbomey = User::where('email', 'abomey@mairie.bj')->first();

        City::updateOrCreate(
            ['name' => 'Cotonou Vibrante'],
            [
                'description' => 'Explorez la capitale économique du Bénin, entre marchés animés et plages de sable fin.',
                'latitude' => 6.3654,
                'longitude' => 2.4183,
                'is_active' => true,
                'creator_id' => $mairieCotonou->id,
                'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
                'image_path' => 'cities/cotonou.webp',
            ]
        );

        City::updateOrCreate(
            ['name' => 'Ouidah Historique'],
            [
                'description' => 'Plongez au cœur de l\'histoire et de la culture vodoun dans la cité des Kpassè.',
                'latitude' => 6.3623,
                'longitude' => 2.0850,
                'is_active' => true,
                'creator_id' => $mairieOuidah->id,
                'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
                'image_path' => 'cities/ouidah.webp',
            ]
        );

        City::updateOrCreate(
            ['name' => 'Porto-Novo Impériale'],
            [
                'description' => 'Découvrez les charmes de la capitale administrative et ses trésors coloniaux.',
                'latitude' => 6.4969,
                'longitude' => 2.6289,
                'is_active' => true,
                'creator_id' => $mairiePorto->id,
                'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
                'image_path' => 'cities/porto.webp',
            ]
        );

        City::updateOrCreate(
            ['name' => 'Parakou la Cité des Princes'],
            [
                'description' => 'Porte d\'entrée du Nord Bénin, Parakou vous accueille avec son marché international et son riche patrimoine culturel.',
                'latitude' => 9.3372,
                'longitude' => 2.6303,
                'is_active' => true,
                'creator_id' => $mairieParakou->id,
                'opening_hours' => ['start' => '08:00', 'end' => '21:00'],
                'image_path' => 'cities/parakou.webp',
            ]
        );

        City::updateOrCreate(
            ['name' => 'Abomey la Royale'],
            [
                'description' => 'Ancienne capitale du royaume de Dahomey, classée au patrimoine mondial de l\'UNESCO.',
                'latitude' => 7.1855,
                'longitude' => 1.9912,
                'is_active' => true,
                'creator_id' => $mairieAbomey->id,
                'opening_hours' => ['start' => '08:30', 'end' => '18:30'],
                'image_path' => 'cities/abomey.webp',
            ]
        );
    }
}
