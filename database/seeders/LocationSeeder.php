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

        // Cotonou
        $amazone = Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Place de l\'Amazone',
            'description' => 'Symbole de la bravoure et de la force des femmes béninoises.',
            'category' => 'monument',
            'latitude' => 6.3494,
            'longitude' => 2.4419,
            'radius_meters' => 100,
            'status' => 'active',
            'order' => 1
        ]);

        LocationImage::create([
            'location_id' => $amazone->id,
            'image_path' => 'locations/amazone_1.jpg'
        ]);

        $dantokpa = Location::create([
            'city_id' => $cotonou->id,
            'name' => 'Marché Dantokpa',
            'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest.',
            'category' => 'culture',
            'latitude' => 6.3708,
            'longitude' => 2.4358,
            'radius_meters' => 200,
            'status' => 'active',
            'order' => 2
        ]);

        LocationImage::create([
            'location_id' => $dantokpa->id,
            'image_path' => 'locations/dantokpa_1.jpg'
        ]);

        // Porto-Novo
        $honme = Location::create([
            'city_id' => $portoNovo->id,
            'name' => 'Musée Honmè',
            'description' => 'Ancien palais royal des rois de Porto-Novo.',
            'category' => 'historique',
            'latitude' => 6.4711,
            'longitude' => 2.6258,
            'radius_meters' => 100,
            'status' => 'active',
            'order' => 3
        ]);

        LocationImage::create([
            'location_id' => $honme->id,
            'image_path' => 'locations/honme_1.jpg'
        ]);

        $mosquee = Location::create([
            'city_id' => $portoNovo->id,
            'name' => 'Grande Mosquée',
            'description' => 'Architecture de style afro-brésilien.',
            'category' => 'culture',
            'latitude' => 6.4764,
            'longitude' => 2.6288,
            'radius_meters' => 80,
            'status' => 'active',
            'order' => 4
        ]);

        LocationImage::create([
            'location_id' => $mosquee->id,
            'image_path' => 'locations/mosquee_1.jpg'
        ]);

        // Abomey-Calavi
        $ganvie = Location::create([
            'city_id' => $calavi->id,
            'name' => 'Embarcadère de Ganvié',
            'description' => 'Point de départ pour la Venise de l\'Afrique.',
            'category' => 'nature',
            'latitude' => 6.4472,
            'longitude' => 2.4056,
            'radius_meters' => 150,
            'status' => 'active',
            'order' => 5
        ]);

        LocationImage::create([
            'location_id' => $ganvie->id,
            'image_path' => 'locations/ganvie_1.jpg'
        ]);

        // Parakou
        $bioguera = Location::create([
            'city_id' => $parakou->id,
            'name' => 'Place Bio Guéra',
            'description' => 'Monument en l\'honneur du guerrier Bio Guéra.',
            'category' => 'monument',
            'latitude' => 9.3372,
            'longitude' => 2.6303,
            'radius_meters' => 100,
            'status' => 'active',
            'order' => 6
        ]);

        LocationImage::create([
            'location_id' => $bioguera->id,
            'image_path' => 'locations/bioguera_1.jpg'
        ]);

        // Ouidah
        $porteNonRetour = Location::create([
            'city_id' => $ouidah->id,
            'name' => 'Porte du Non-Retour',
            'description' => 'Mémorial érigé sur la plage en mémoire de la traite négrière.',
            'category' => 'historique',
            'latitude' => 6.3245,
            'longitude' => 2.0854,
            'radius_meters' => 100,
            'status' => 'active',
            'order' => 7
        ]);

        LocationImage::create([
            'location_id' => $porteNonRetour->id,
            'image_path' => 'locations/porte_1.jpg'
        ]);

        $templePythons = Location::create([
            'city_id' => $ouidah->id,
            'name' => 'Temple des Pythons',
            'description' => 'Lieu sacré du culte vodoun.',
            'category' => 'mystere',
            'latitude' => 6.3622,
            'longitude' => 2.0850,
            'radius_meters' => 50,
            'status' => 'active',
            'order' => 8
        ]);

        LocationImage::create([
            'location_id' => $templePythons->id,
            'image_path' => 'locations/pythons_1.jpg'
        ]);
    }
}
