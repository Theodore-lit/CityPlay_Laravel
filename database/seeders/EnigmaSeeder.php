<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enigma;
use App\Models\Location;

class EnigmaSeeder extends Seeder
{
    public function run(): void
    {
        $locations = Location::all();

        foreach ($locations as $location) {
            // 1. Énigme de déblocage (Facile)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'easy', 'is_site_specific' => false],
                [
                    'content' => "Je suis le premier indice pour localiser {$location->name}. Je suis facile à trouver si vous connaissez l'histoire du quartier.",
                    'answer' => "Histoire",
                    'reward_coins' => 10,
                    'type' => 'aventure',
                    'indices' => ["C'est lié au passé.", "H_ _ _ _ _RE"],
                ]
            );

            // 2. Énigme de déblocage (Moyen)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'medium', 'is_site_specific' => false],
                [
                    'content' => "Pour me trouver, cherchez l'ombre du grand baobab qui ne meurt jamais.",
                    'answer' => "Tradition",
                    'reward_coins' => 25,
                    'type' => 'aventure',
                    'indices' => ["C'est une valeur transmise.", "T_ _ _ _ _ _ _N"],
                ]
            );

            // 3. Énigme de déblocage (Difficile)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'hard', 'is_site_specific' => false],
                [
                    'content' => "Le vent souffle du Nord, mais ma boussole indique l'âme des ancêtres.",
                    'answer' => "Esprit",
                    'reward_coins' => 50,
                    'type' => 'aventure',
                    'indices' => ["Ce n'est pas matériel.", "E_ _ _ _T"],
                ]
            );

            // 4. Énigme SUR SITE (Analyse du lieu)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'is_site_specific' => true],
                [
                    'content' => "Maintenant que vous êtes ici, regardez la plaque commémorative. Quelle année y est inscrite ?",
                    'answer' => "1960",
                    'difficulty' => 'medium',
                    'reward_coins' => 100,
                    'type' => 'aventure',
                    'indices' => ["Regardez le métal.", "L'année de l'indépendance."],
                    'image_path' => 'enigmas/enigma_site.webp',
                ]
            );
        }
    }
}
