<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\User;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $mairie = User::where('role', 'mairie')->first();

        City::create([
            'name' => 'Cotonou Vibrante',
            'description' => 'Explorez la capitale économique du Bénin, entre marchés animés et plages de sable fin.',
            'is_active' => true,
            'creator_id' => $mairie->id,
            'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
        ]);

        City::create([
            'name' => 'Ouidah Historique',
            'description' => 'Plongez au cœur de l\'histoire et de la culture vodoun dans la cité des Kpassè.',
            'is_active' => true,
            'creator_id' => $mairie->id,
            'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
        ]);

        City::create([
            'name' => 'Porto-Novo Impériale',
            'description' => 'Découvrez les charmes de la capitale administrative et ses trésors coloniaux.',
            'is_active' => true,
            'creator_id' => $mairie->id,
            'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
        ]);
    }
}
