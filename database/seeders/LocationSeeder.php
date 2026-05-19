<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\City;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $cotonou = City::where('name', 'Cotonou Vibrante')->first();
        $ouidah = City::where('name', 'Ouidah Historique')->first();
        $portoNovo = City::where('name', 'Porto-Novo Impériale')->first();
        $parakou = City::where('name', 'Parakou la Cité des Princes')->first();
        $abomey = City::where('name', 'Abomey la Royale')->first();

        // Cotonou
        Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Marché Dantokpa',
            'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest.',
            'category' => 'culture',
            'latitude' => 6.3712,
            'longitude' => 2.4328,
            'radius_meters' => 150,
            'status' => 'active',
        ]);

        Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Place de l\'Amazone',
            'description' => 'Monument majestueux dédié aux femmes guerrières du Dahomey.',
            'category' => 'historique',
            'latitude' => 6.3533,
            'longitude' => 2.4331,
            'radius_meters' => 100,
            'status' => 'active',
        ]);

        // Ouidah
        Location::create([
            'city_id' => $ouidah->id,
            'name' => 'Porte du Non-Retour',
            'description' => 'Mémorial érigé sur la plage en mémoire de la traite négrière.',
            'category' => 'historique',
            'latitude' => 6.3245,
            'longitude' => 2.0854,
            'radius_meters' => 100,
            'status' => 'active',
        ]);

        Location::create([
            'city_id' => $ouidah->id,
            'name' => 'Temple des Pythons',
            'description' => 'Lieu sacré du culte vodoun dédié au python royal.',
            'category' => 'mystere',
            'latitude' => 6.3622,
            'longitude' => 2.0850,
            'radius_meters' => 50,
            'status' => 'active',
        ]);

        // Porto-Novo
        Location::create([
            'city_id' => $portoNovo->id,
            'name' => 'Grande Mosquée de Porto-Novo',
            'description' => 'Architecture unique inspirée des églises baroques du Brésil.',
            'category' => 'culture',
            'latitude' => 6.4764,
            'longitude' => 2.6288,
            'radius_meters' => 80,
            'status' => 'active',
        ]);

        // Parakou
        Location::create([
            'city_id' => $parakou->id,
            'name' => 'Musée de plein air de Parakou',
            'description' => 'Découvrez les habitations traditionnelles des peuples du Nord.',
            'category' => 'culture',
            'latitude' => 9.3361,
            'longitude' => 2.6289,
            'radius_meters' => 120,
            'status' => 'active',
        ]);

        // Abomey
        Location::create([
            'city_id' => $abomey->id,
            'name' => 'Palais Royaux d\'Abomey',
            'description' => 'Le cœur battant de l\'ancien royaume de Dahomey.',
            'category' => 'historique',
            'latitude' => 7.1858,
            'longitude' => 1.9915,
            'radius_meters' => 200,
            'status' => 'active',
        ]);

        // Secret Location in Ouidah
        Location::create([
            'city_id' => $ouidah->id,
            'name' => 'Forêt Sacrée de Kpassè',
            'description' => 'Une forêt mystique où l\'histoire se mêle à la légende du roi Kpassè.',
            'category' => 'mystere',
            'latitude' => 6.3662,
            'longitude' => 2.0833,
            'radius_meters' => 150,
            'status' => 'active',
            'is_secret' => true,
        ]);
    }
}
