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
                    'description' => 'Un monument solennel face à l\'Océan Atlantique, marquant le point de départ forcé de millions d\'âmes.',
                    'category' => 'historique',
                    'latitude' => 6.3245,
                    'longitude' => 2.0854,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1590603783180-8736a6552912?q=80&w=1200'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $ouidah->id, 'name' => 'Temple des Pythons'],
                [
                    'description' => 'Découvrez le caractère sacré du python royal dans ce sanctuaire historique au cœur de la ville.',
                    'category' => 'mystere',
                    'latitude' => 6.3622,
                    'longitude' => 2.0850,
                    'radius_meters' => 50,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?q=80&w=1200'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $ouidah->id, 'name' => 'Forêt Sacrée de Kpassè'],
                [
                    'description' => 'Entrez dans le domaine mystique du roi Kpassè, gardé par des divinités sculptées et des arbres centenaires.',
                    'category' => 'mystere',
                    'latitude' => 6.3662,
                    'longitude' => 2.0833,
                    'radius_meters' => 150,
                    'status' => 'active',
                    'is_secret' => true,
                    'images' => ['https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=1200'],
                ]
            );
        }

        // --- PORTO-NOVO ---
        if ($portoNovo) {
            Location::updateOrCreate(
                ['city_id' => $portoNovo->id, 'name' => 'Grande Mosquée de Porto-Novo'],
                [
                    'description' => 'Un chef-d\'œuvre architectural unique, héritage de la culture afro-brésilienne Agouda.',
                    'category' => 'culture',
                    'latitude' => 6.4764,
                    'longitude' => 2.6288,
                    'radius_meters' => 80,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1564507592333-c60657eaa0ae?q=80&w=1200'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $portoNovo->id, 'name' => 'Musée Honmè'],
                [
                    'description' => 'Ancien palais des rois de Porto-Novo, témoin de la puissance de la dynastie Toffa.',
                    'category' => 'historique',
                    'latitude' => 6.4720,
                    'longitude' => 2.6250,
                    'radius_meters' => 100,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?q=80&w=1200'],
                ]
            );
        }

        // --- COTONOU ---
        if ($cotonou) {
            Location::updateOrCreate(
                ['city_id' => $cotonou->id, 'name' => 'Place de l\'Amazone'],
                [
                    'description' => 'L\'Amazone, symbole de la bravoure et de la force de la femme béninoise, veille sur la cité.',
                    'category' => 'historique',
                    'latitude' => 6.3486,
                    'longitude' => 2.4331,
                    'radius_meters' => 120,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1590603783930-9d93dcf99723?q=80&w=1200'],
                ]
            );

            Location::updateOrCreate(
                ['city_id' => $cotonou->id, 'name' => 'Marché Dantokpa'],
                [
                    'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest, une véritable ville dans la ville.',
                    'category' => 'culture',
                    'latitude' => 6.3712,
                    'longitude' => 2.4345,
                    'radius_meters' => 200,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1533900298318-6b8da08a523e?q=80&w=1200'],
                ]
            );
        }

        // --- ABOMEY ---
        if ($abomey) {
            Location::updateOrCreate(
                ['city_id' => $abomey->id, 'name' => 'Palais Royaux d\'Abomey'],
                [
                    'description' => 'Classés à l\'UNESCO, ces palais racontent la puissance militaire du royaume de Dahomey.',
                    'category' => 'historique',
                    'latitude' => 7.1855,
                    'longitude' => 1.9912,
                    'radius_meters' => 250,
                    'status' => 'active',
                    'images' => ['https://images.unsplash.com/photo-1590603783180-8736a6552912?q=80&w=1200'],
                ]
            );
        }
    }
}
