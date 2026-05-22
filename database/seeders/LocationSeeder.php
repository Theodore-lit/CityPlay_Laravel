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
        $ouidah = City::where('name', 'Ouidah')->first();

        // --- COTONOU ---
        if ($cotonou) {
            $locations = [
                [
                    'name' => 'La Statue de l\'Amazone',
                    'description' => 'Fière guerrière de bronze de 30m, symbole du courage féminin et hommage aux Agodjié.',
                    'latitude' => 6.3482,
                    'longitude' => 2.4334,
                    'category' => 'historique',
                    'images' => ['https://images.unsplash.com/photo-1590603783930-9d93dcf99723?q=80&w=1200'],
                ],
                [
                    'name' => 'Marché Dantokpa',
                    'description' => 'Le plus grand marché de l\'Afrique de l\'Ouest, véritable poumon économique au bord de la lagune.',
                    'latitude' => 6.3654,
                    'longitude' => 2.4389,
                    'category' => 'culture',
                    'images' => ['https://images.unsplash.com/photo-1572522085350-997f80598715?q=80&w=1200'],
                ],
                [
                    'name' => 'Place de l\'Étoile Rouge',
                    'description' => 'Monument révolutionnaire emblématique célébrant l\'unité nationale et l\'histoire politique du pays.',
                    'latitude' => 6.3750,
                    'longitude' => 2.4110,
                    'category' => 'historique',
                    'images' => ['https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?q=80&w=1200'],
                ],
                [
                    'name' => 'Cathédrale Notre-Dame',
                    'description' => 'Célèbre église aux rayures rouges et blanches, joyau architectural néo-gothique de Cotonou.',
                    'latitude' => 6.3551,
                    'longitude' => 2.4338,
                    'category' => 'culture',
                    'images' => ['https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?q=80&w=1200'],
                ],
                [
                    'name' => 'Place de l\'Indépendance',
                    'description' => 'Lieu historique où fut proclamée la souveraineté en 1960, abritant le Monument aux Morts.',
                    'latitude' => 6.3536,
                    'longitude' => 2.4285,
                    'category' => 'historique',
                    'images' => ['https://images.unsplash.com/photo-1564507592333-c60657eaa0ae?q=80&w=1200'],
                ],
            ];

            foreach ($locations as $loc) {
                Location::updateOrCreate(
                    ['city_id' => $cotonou->id, 'name' => $loc['name']],
                    array_merge($loc, ['radius_meters' => 100, 'status' => 'active'])
                );
            }
        }

        // --- OUIDAH ---
        if ($ouidah) {
            $locations = [
                [
                    'name' => 'Temple des Pythons',
                    'description' => 'Sanctuaire sacré du dieu Dangbé où vivent des pythons royaux vénérés et inoffensifs.',
                    'latitude' => 6.3619,
                    'longitude' => 2.0850,
                    'category' => 'mystere',
                    'images' => ['https://images.unsplash.com/photo-1590603783180-8736a6552912?q=80&w=1200'],
                ],
                [
                    'name' => 'Forêt Sacrée de Kpassè',
                    'description' => 'Domaine mystique peuplé de divinités et d\'arbres centenaires, lieu de disparition du roi Kpassè.',
                    'latitude' => 6.3575,
                    'longitude' => 2.0818,
                    'category' => 'mystere',
                    'images' => ['https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=1200'],
                ],
                [
                    'name' => 'Fort Portugais',
                    'description' => 'Ancienne enclave portugaise (Fort Ajuda) devenue musée d\'histoire, témoin de la traite négrière.',
                    'latitude' => 6.3631,
                    'longitude' => 2.0880,
                    'category' => 'historique',
                    'images' => ['https://images.unsplash.com/photo-1590603783930-9d93dcf99723?q=80&w=1200'],
                ],
                [
                    'name' => 'Porte du Non-Retour',
                    'description' => 'Monument solennel sur la plage marquant le point de départ des navires négriers vers les Amériques.',
                    'latitude' => 6.3262,
                    'longitude' => 2.0833,
                    'category' => 'historique',
                    'images' => ['https://images.unsplash.com/photo-1590603783180-8736a6552912?q=80&w=1200'],
                ],
                [
                    'name' => 'Basilique de Ouidah',
                    'description' => 'Première basilique mineure d\'Afrique de l\'Ouest, chef-d\'œuvre colonial face au Temple des Pythons.',
                    'latitude' => 6.3623,
                    'longitude' => 2.0847,
                    'category' => 'culture',
                    'images' => ['https://images.unsplash.com/photo-1572522085350-997f80598715?q=80&w=1200'],
                ],
            ];

            foreach ($locations as $loc) {
                Location::updateOrCreate(
                    ['city_id' => $ouidah->id, 'name' => $loc['name']],
                    array_merge($loc, ['radius_meters' => 100, 'status' => 'active'])
                );
            }
        }
    }
}
