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
