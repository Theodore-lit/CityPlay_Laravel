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

        $cities = [
            [
                'name' => 'Cotonou Vibrante',
                'description' => 'Explorez la capitale économique du Bénin, entre marchés animés et plages de sable fin.',
                'image_path' => 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800',
                'latitude' => 6.3654,
                'longitude' => 2.4183,
                'is_active' => true,
                'creator_id' => $mairieCotonou->id ?? null,
                'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
            ],
            [
                'name' => 'Ouidah Historique',
                'description' => 'Plongez au cœur de l\'histoire et de la culture vodoun dans la cité des Kpassè.',
                'image_path' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&q=80&w=800',
                'latitude' => 6.3623,
                'longitude' => 2.0850,
                'is_active' => true,
                'creator_id' => $mairieOuidah->id ?? null,
                'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
            ],
            [
                'name' => 'Porto-Novo Impériale',
                'description' => 'Découvrez les charmes de la capitale administrative et ses trésors coloniaux.',
                'image_path' => 'https://images.unsplash.com/photo-1596422846543-75c6fc18a593?auto=format&fit=crop&q=80&w=800',
                'latitude' => 6.4969,
                'longitude' => 2.6289,
                'is_active' => true,
                'creator_id' => $mairiePorto->id ?? null,
                'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
            ],
            [
                'name' => 'Parakou la Cité des Princes',
                'description' => 'Porte d\'entrée du Nord Bénin, Parakou vous accueille avec son marché international et son riche patrimoine culturel.',
                'image_path' => 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?auto=format&fit=crop&q=80&w=800',
                'latitude' => 9.3372,
                'longitude' => 2.6303,
                'is_active' => true,
                'creator_id' => $mairieParakou->id ?? null,
                'opening_hours' => ['start' => '08:00', 'end' => '21:00'],
            ],
            [
                'name' => 'Abomey la Royale',
                'description' => 'Ancienne capitale du royaume de Dahomey, classée au patrimoine mondial de l\'UNESCO.',
                'image_path' => 'https://images.unsplash.com/photo-1523805081446-99b256619ebf?auto=format&fit=crop&q=80&w=800',
                'latitude' => 7.1855,
                'longitude' => 1.9912,
                'is_active' => true,
                'creator_id' => $mairieAbomey->id ?? null,
                'opening_hours' => ['start' => '08:30', 'end' => '18:30'],
            ],
        ];

        foreach ($cities as $cityData) {
            City::updateOrCreate(
                ['name' => $cityData['name']],
                $cityData
            );
        }
    }
}
