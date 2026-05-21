<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\City;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Recherche avec LIKE pour matcher "Ouidah Historique", "Cotonou Vibrante", etc.
        $cotonou = City::where('name', 'LIKE', 'Cotonou%')->first();
        $portoNovo = City::where('name', 'LIKE', 'Porto-Novo%')->first();
        $parakou = City::where('name', 'LIKE', 'Parakou%')->first();
        $ouidah = City::where('name', 'LIKE', 'Ouidah%')->first();
        // Ajout d'Abomey au cas où (utilisé dans ton CitySeeder)
        $abomey = City::where('name', 'LIKE', 'Abomey%')->first();

        // --- OUIDAH ---
        if ($ouidah) {
            Location::updateOrCreate(
                ['city_id' => $ouidah->id, 'name' => 'Porte du Non-Retour'],
                [
                    'description' => 'Mémorial érigé sur la plage en mémoire de la traite négrière.',
                    'category' => 'historique',
                    'latitude' => 6.3245,
                    'longitude' => 2.0854,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => ['locations/porte.webp'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $ouidah->id, 'name' => 'Temple des Pythons'],
                [
                    'description' => 'Lieu sacré du culte vodoun dédié au python royal.',
                    'category' => 'mystere',
                    'latitude' => 6.3622,
                    'longitude' => 2.0850,
                    'radius_meters' => 50,
                    'status' => 'active',
                    'images' => ['locations/pythons.webp'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $ouidah->id, 'name' => 'Forêt Sacrée de Kpassè'],
                [
                    'description' => 'Une forêt mystique où l\'histoire se mêle à la légende du roi Kpassè.',
                    'category' => 'mystere',
                    'latitude' => 6.3662,
                    'longitude' => 2.0833,
                    'radius_meters' => 150,
                    'status' => 'active',
                    'is_secret' => true,
                    'images' => ['locations/foret.webp'],
                ]
            );
        }

        // --- PORTO-NOVO ---
        if ($portoNovo) {
            Location::updateOrCreate(
                ['city_id' => $portoNovo->id, 'name' => 'Grande Mosquée de Porto-Novo'],
                [
                    'description' => 'Architecture unique inspirée des églises baroques du Brésil.',
                    'category' => 'culture',
                    'latitude' => 6.4764,
                    'longitude' => 2.6288,
                    'radius_meters' => 80,
                    'status' => 'active',
                    'images' => ['locations/mosquee.webp'],
                ]
            );
        }

        // --- COTONOU ---
        if ($cotonou) {
            Location::updateOrCreate(
                ['city_id' => $cotonou->id, 'name' => 'Place de l\'Amazone'],
                [
                    'description' => 'Monument majestueux rendant hommage aux courageuses guerrières du Dahomey.',
                    'category' => 'historique',
                    'latitude' => 6.3486,
                    'longitude' => 2.4331,
                    'radius_meters' => 120,
                    'status' => 'active',
                    'images' => ['locations/amazone.webp'],
                ]
            );
        }
    }
}
