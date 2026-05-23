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
                    'history' => 'Inaugurée le 30 juillet 2022 par le Président Patrice Talon, cette statue de 30 mètres rend hommage aux Agodjié, les guerrières intrépides du Royaume de Dahomey. Elle symbolise le courage, le patriotisme et la fierté de la femme béninoise.',
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
                    'history' => 'Fondé en 1963, le marché Dantokpa s\'étend sur plus de 18 hectares. Son nom signifie "sur le bord de la lagune de Dan", Dan étant la divinité du serpent apportant la richesse. C\'est l\'un des plus grands marchés d\'Afrique de l\'Ouest.',
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
                    'history' => 'Construite en 1974 sous le régime militaro-marxiste du général Mathieu Kérékou, cette place symbolise la révolution et l\'unité nationale. Le monument central représente un homme tenant une arme, une houe et un livre.',
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
                    'history' => 'Inaugurée en 1901, cette cathédrale de style néo-gothique est remarquable par ses briques bicolores. Elle est le siège de l\'archidiocèse de Cotonou et l\'un des monuments les plus photographiés de la ville.',
                    'category' => 'culture',
                    'latitude' => 6.3547,
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
                    'history' => 'C\'est ici qu\'ont lieu les défilés militaires officiels chaque 1er août. On y trouve le Monument aux Morts, dédié aux soldats tombés pour la patrie, ainsi que la flamme éternelle.',
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
                    'history' => 'Lieu central de la religion Vodoun, ce temple abrite des pythons royaux inoffensifs. Selon la légende, ces serpents auraient sauvé le roi Kpassè pendant une guerre tribale au 18ème siècle.',
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
                    'history' => 'Cette forêt dense abrite des arbres centenaires et des statues représentant les divinités du panthéon Vodoun. On raconte que le roi Kpassè s\'y est transformé en Iroko pour échapper à ses ennemis.',
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
                    'history' => 'Construit en 1721 par les Portugais sous le nom de São João Baptista de Ajudá, ce fort fut une enclave portugaise jusqu\'en 1961. Il témoigne du passé colonial et de l\'époque de la traite négrière.',
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
                    'history' => 'Érigé en 1992 par l\'UNESCO, ce monument commémore le départ des millions d\'esclaves vers les Amériques. Il marque la fin de la "Route des Esclaves" et le début de l\'exil pour beaucoup.',
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
                    'history' => 'Consacrée en 1909, la basilique de l\'Immaculée Conception est un chef-d\'œuvre d\'architecture coloniale. Elle fait face au Temple des Pythons, illustrant la cohabitation pacifique des religions au Bénin.',
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
