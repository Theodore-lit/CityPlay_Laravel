<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\City;
use App\Models\LocationImage;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $cotonou = City::where('name', 'Cotonou')->first();
        $portoNovo = City::where('name', 'Porto-Novo')->first();
        $calavi = City::where('name', 'Abomey-Calavi')->first();
        $parakou = City::where('name', 'Parakou')->first();
        $ouidah = City::where('name', 'Ouidah')->first();

        // Ouidah
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

        // Porto-Novo
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

        // Secret Location in Ouidah
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
}
