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
            Enigma::create([
                'location_id' => $location->id,
                'content' => "Je suis le premier indice pour localiser {$location->name}. Je suis facile à trouver si vous connaissez l'histoire du quartier.",
                'answer' => "Histoire",
                'difficulty' => 'easy',
                'reward_coins' => 10,
                'type' => 'aventure',
                'is_site_specific' => false,
                'indices' => ["C'est lié au passé.", "H_ _ _ _ _RE"],
            ]);

            // 2. Énigme de déblocage (Moyen)
            Enigma::create([
                'location_id' => $location->id,
                'content' => "Pour me trouver, cherchez l'ombre du grand baobab qui ne meurt jamais.",
                'answer' => "Tradition",
                'difficulty' => 'medium',
                'reward_coins' => 25,
                'type' => 'aventure',
                'is_site_specific' => false,
                'indices' => ["C'est une valeur transmise.", "T_ _ _ _ _ _ _N"],
            ]);

            // 3. Énigme de déblocage (Difficile)
            Enigma::create([
                'location_id' => $location->id,
                'content' => "Le vent souffle du Nord, mais ma boussole indique l'âme des ancêtres.",
                'answer' => "Esprit",
                'difficulty' => 'hard',
                'reward_coins' => 50,
                'type' => 'aventure',
                'is_site_specific' => false,
                'indices' => ["Ce n'est pas matériel.", "E_ _ _ _T"],
            ]);

            // 4. Énigme SUR SITE (Analyse du lieu)
            Enigma::create([
                'location_id' => $location->id,
                'content' => "Maintenant que vous êtes ici, regardez la plaque commémorative. Quelle année y est inscrite ?",
                'answer' => "1960",
                'difficulty' => 'medium',
                'reward_coins' => 100,
                'type' => 'aventure',
                'is_site_specific' => true,
                'indices' => ["Regardez le métal.", "L'année de l'indépendance."],
            ]);
        }
    }
}
