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

        // Récupération d'un admin par défaut pour le creator_id
        $admin = User::where('email', 'admin@cityplay.bj')->first() ?? User::first();

        // 1. Cotonou
        City::updateOrCreate(
            ['name' => 'Cotonou'],
            [
                'description' => 'La capitale économique du Bénin. Signifiant "L\'embouchure du fleuve de la mort" en Fon, c\'est une ville vibrante entre lagune et océan.',
                'latitude' => 6.3654,
                'longitude' => 2.4183,
                'is_active' => true,
                'creator_id' => $admin->id,
                'mairie_id' => $mairieCotonou?->id,
                'opening_hours' => ['start' => '07:00', 'end' => '23:00'],
                'image_path' => 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=1200',
            ]
        );

        // 2. Ouidah
        City::updateOrCreate(
            ['name' => 'Ouidah'],
            [
                'description' => 'Cité historique et capitale mondiale du Vodoun. Anciennement appelée Gléhué, elle fut un point central de la traite négrière et de la culture béninoise.',
                'latitude' => 6.3623,
                'longitude' => 2.0850,
                'is_active' => true,
                'creator_id' => $admin->id,
                'mairie_id' => $mairieOuidah?->id,
                'opening_hours' => ['start' => '08:00', 'end' => '19:00'],
                'image_path' => 'https://images.unsplash.com/photo-1590603783180-8736a6552912?auto=format&fit=crop&q=80&w=1200',
            ]
        );

        // 3. Porto-Novo
        City::updateOrCreate(
            ['name' => 'Porto-Novo'],
            [
                'description' => 'La capitale administrative aux trois noms (Hogbonou, Adjatchè, Porto-Novo). Célèbre pour son architecture afro-brésilienne.',
                'latitude' => 6.4969,
                'longitude' => 2.6289,
                'is_active' => true,
                'creator_id' => $admin->id,
                'mairie_id' => $mairiePorto?->id,
                'opening_hours' => ['start' => '08:00', 'end' => '20:00'],
                'image_path' => 'https://images.unsplash.com/photo-1572522085350-997f80598715?auto=format&fit=crop&q=80&w=1200',
            ]
        );
    }
}
