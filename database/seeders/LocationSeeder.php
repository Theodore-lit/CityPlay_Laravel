<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\City;
use App\Models\LocationImage;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Nettoyage pour éviter les doublons d'images
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LocationImage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $cotonou = City::where('name', 'Cotonou')->first();
        $ouidah = City::where('name', 'Ouidah')->first();

        // --- COTONOU ---
        if ($cotonou) {
            $cotonouLocations = [
                [
                    'name' => 'La Statue de l\'Amazone',
                    'description' => 'La fière guerrière de bronze qui veille sur le boulevard de la Marina.',
                    'category' => 'historique',
                    'latitude' => 6.3482,
                    'longitude' => 2.4334,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/cotonou/amazone-1.jpg',
                        'images/locations/cotonou/amazone-2.jpg'
                    ]
                ],
                [
                    'name' => 'Marché Dantokpa',
                    'description' => 'Cœur battant de la ville, le plus grand labyrinthe commercial du pays.',
                    'category' => 'culture',
                    'latitude' => 6.3654,
                    'longitude' => 2.4389,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/cotonou/dantokpa-1.jpg'
                    ]
                ],
                [
                    'name' => 'Place de l\'Étoile Rouge',
                    'description' => 'Immense carrefour circulaire marqué par une étoile géante au centre.',
                    'category' => 'historique',
                    'latitude' => 6.3750,
                    'longitude' => 2.4110,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/cotonou/etoile-rouge-1.jpg'
                    ]
                ],
                [
                    'name' => 'Cathédrale Notre-Dame',
                    'description' => 'Grande église aux rayures rouges et blanches près de l\'ancien pont.',
                    'category' => 'culture',
                    'latitude' => 6.3545,
                    'longitude' => 2.4377,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/cotonou/cathedrale-1.jpg'
                    ]
                ],
                [
                    'name' => 'Place de l\'Indépendance',
                    'description' => 'Lieu de célébration de la fête nationale le 1er août.',
                    'category' => 'historique',
                    'latitude' => 6.3536,
                    'longitude' => 2.4285,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/cotonou/independance-1.jpg'
                    ]
                ],
            ];

            foreach ($cotonouLocations as $loc) {
                $images = $loc['images'] ?? [];
                unset($loc['images']);

                $location = Location::updateOrCreate(
                    ['city_id' => $cotonou->id, 'name' => $loc['name']],
                    $loc
                );

                foreach ($images as $path) {
                    LocationImage::create([
                        'location_id' => $location->id,
                        'image_path' => $path
                    ]);
                }
            }
        }

        // --- OUIDAH ---
        if ($ouidah) {
            $ouidahLocations = [
                [
                    'name' => 'Temple des Pythons',
                    'description' => 'Sanctuaire historique abritant des serpents sacrés en liberté.',
                    'category' => 'mystere',
                    'latitude' => 6.3619,
                    'longitude' => 2.0850,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/ouidah/pythons-1.jpg'
                    ]
                ],
                [
                    'name' => 'Forêt Sacrée de Kpassè',
                    'description' => 'Parc de statues mystiques et d\'arbres géants où un roi a disparu.',
                    'category' => 'mystere',
                    'latitude' => 6.3575,
                    'longitude' => 2.0818,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/ouidah/foret-sacree-1.jpg'
                    ]
                ],
                [
                    'name' => 'Fort Portugais',
                    'description' => 'Ancienne forteresse européenne devenue musée d\'histoire.',
                    'category' => 'historique',
                    'latitude' => 6.3631,
                    'longitude' => 2.0880,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/ouidah/fort-portugais-1.jpg'
                    ]
                ],
                [
                    'name' => 'Porte du Non-Retour',
                    'description' => 'Grand arc de triomphe sur la plage face à l\'atlantique, mémoire de la traite.',
                    'category' => 'historique',
                    'latitude' => 6.3262,
                    'longitude' => 2.0833,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/ouidah/non-retour-1.jpg'
                    ]
                ],
                [
                    'name' => 'Basilique de Ouidah',
                    'description' => 'Première basilique mineure d\'Afrique de l\'Ouest, toute blanche et bleue.',
                    'category' => 'culture',
                    'latitude' => 6.3623,
                    'longitude' => 2.0847,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => [
                        'images/locations/ouidah/basilique-1.jpg'
                    ]
                ],
            ];

            foreach ($ouidahLocations as $loc) {
                $images = $loc['images'] ?? [];
                unset($loc['images']);

                $location = Location::updateOrCreate(
                    ['city_id' => $ouidah->id, 'name' => $loc['name']],
                    $loc
                );

                foreach ($images as $path) {
                    LocationImage::create([
                        'location_id' => $location->id,
                        'image_path' => $path
                    ]);
                }
            }
        }
    }
}
