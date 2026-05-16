<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\User;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $mairieCotonou = User::where('email', 'mairie.cotonou@cityplay.bj')->first();
        $mairiePorto = User::where('email', 'mairie.portonovo@cityplay.bj')->first();
        $mairieCalavi = User::where('email', 'mairie.calavi@cityplay.bj')->first();
        $mairieParakou = User::where('email', 'mairie.parakou@cityplay.bj')->first();
        $mairieOuidah = User::where('email', 'mairie.ouidah@cityplay.bj')->first();

        City::create([
            'name' => 'Cotonou',
            'description' => 'La capitale économique vibrante, entre terre et mer.',
            'latitude' => 6.3667,
            'longitude' => 2.4333,
            'radius_meters' => 5000,
            'is_active' => true,
            'creator_id' => 1,
            'mairie_id' => $mairieCotonou->id,
            'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
        ]);

        City::create([
            'name' => 'Porto-Novo',
            'description' => 'La capitale administrative, riche en patrimoine colonial.',
            'latitude' => 6.4969,
            'longitude' => 2.6289,
            'radius_meters' => 4000,
            'is_active' => true,
            'creator_id' => 1,

            'mairie_id' =>    $mairiePorto->id,
            'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
        ]);

        City::create([
            'name' => 'Abomey-Calavi',
            'description' => 'La cité universitaire et carrefour stratégique.',
            'latitude' => 6.4481,
            'longitude' => 2.3557,
            'radius_meters' => 6000,
            'is_active' => true,
            'creator_id' => 1,

            'mairie_id' =>  $mairieCalavi->id,
            'opening_hours' => ['start' => '06:00', 'end' => '22:00'],
        ]);

        City::create([
            'name' => 'Parakou',
            'description' => 'La métropole du Nord, carrefour commercial majeur.',
            'latitude' => 9.3372,
            'longitude' => 2.6303,
            'radius_meters' => 7000,
            'is_active' => true,
            'creator_id' => 1,
            'mairie_id' => $mairieParakou->id,
            'opening_hours' => ['start' => '07:00', 'end' => '21:00'],
        ]);

        City::create([
            'name' => 'Ouidah',
            'description' => 'Cité historique et spirituelle, berceau du Vodoun.',
            'latitude' => 6.3622,
            'longitude' => 2.0850,
            'radius_meters' => 3000,
            'is_active' => true,
            'creator_id' => 1,

            'mairie_id' => $mairieOuidah->id,
            'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
        ]);
    }
}
