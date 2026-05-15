<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\City;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $cotonou = City::where('name', 'Cotonou')->first();
        $portoNovo = City::where('name', 'Porto-Novo')->first();
        $calavi = City::where('name', 'Abomey-Calavi')->first();
        $parakou = City::where('name', 'Parakou')->first();
        $ouidah = City::where('name', 'Ouidah')->first();

        // Cotonou
        Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Place de l\'Amazone',
            'description' => 'Symbole de la bravoure et de la force des femmes béninoises.',
            'category' => 'monument',
            'latitude' => 6.3494,
            'longitude' => 2.4419,
            'radius_meters' => 100,
            'status' => 'active',
        ]);

        Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Marché Dantokpa',
            'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest.',
            'category' => 'culture',
            'latitude' => 6.3708,
            'longitude' => 2.4358,
            'radius_meters' => 200,
            'status' => 'active',
        ]);

        // Porto-Novo
        Location::create([
            'city_id' => $portoNovo->id,
            'name' => 'Musée Honmè',
            'description' => 'Ancien palais royal des rois de Porto-Novo.',
            'category' => 'historique',
            'latitude' => 6.4711,
            'longitude' => 2.6258,
            'radius_meters' => 100,
            'status' => 'active',
        ]);

        Location::create([
            'city_id' => $portoNovo->id,
            'name' => 'Grande Mosquée',
            'description' => 'Architecture de style afro-brésilien.',
            'category' => 'culture',
            'latitude' => 6.4764,
            'longitude' => 2.6288,
            'radius_meters' => 80,
            'status' => 'active',
        ]);

        // Abomey-Calavi
        Location::create([
            'city_id' => $calavi->id,
            'name' => 'Embarcadère de Ganvié',
            'description' => 'Point de départ pour la Venise de l\'Afrique.',
            'category' => 'nature',
            'latitude' => 6.4472,
            'longitude' => 2.4056,
            'radius_meters' => 150,
            'status' => 'active',
        ]);

        // Parakou
        Location::create([
            'city_id' => $parakou->id,
            'name' => 'Place Bio Guéra',
            'description' => 'Monument en l\'honneur du guerrier Bio Guéra.',
            'category' => 'monument',
            'latitude' => 9.3372,
            'longitude' => 2.6303,
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
            'description' => 'Lieu sacré du culte vodoun.',
            'category' => 'mystere',
            'latitude' => 6.3622,
            'longitude' => 2.0850,
            'radius_meters' => 50,
            'status' => 'active',
        ]);
    }
}
