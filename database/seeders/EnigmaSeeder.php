<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enigma;
use App\Models\Location;

class EnigmaSeeder extends Seeder
{
    public function run(): void
    {
        $amazone = Location::where('name', 'Place de l\'Amazone')->first();
        $dantokpa = Location::where('name', 'Marché Dantokpa')->first();
        $porteNonRetour = Location::where('name', 'Porte du Non-Retour')->first();
        $templePythons = Location::where('name', 'Temple des Pythons')->first();

        // Place de l'Amazone
        Enigma::create([
            'location_id' => $amazone->id,
            'title' => 'La statue géante',
            'content' => 'Quelle est la hauteur approximative (en mètres) de la statue de l\'Amazone ?',
            'difficulty' => 'medium',
            'answer' => '30',
            'reward_coins' => 15,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);

        // Marché Dantokpa
        Enigma::create([
            'location_id' => $dantokpa->id,
            'title' => 'Le carrefour commercial',
            'content' => 'Quel fleuve borde le marché Dantokpa ?',
            'difficulty' => 'easy',
            'answer' => 'Ouémé',
            'reward_coins' => 10,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);

        // Porte du Non-Retour
        Enigma::create([
            'location_id' => $porteNonRetour->id,
            'title' => 'Le symbole de l\'oubli',
            'content' => 'Combien de piliers principaux soutiennent la structure de la Porte du Non-Retour ?',
            'difficulty' => 'hard',
            'answer' => '4',
            'reward_coins' => 25,
            'reward_hearts' => 1,
            'type' => 'aventure',
        ]);

        // Temple des Pythons
        Enigma::create([
            'location_id' => $templePythons->id,
            'title' => 'Les gardiens sacrés',
            'content' => 'Quelle espèce de python est vénérée dans ce temple ?',
            'difficulty' => 'medium',
            'answer' => 'Python royal',
            'reward_coins' => 15,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);
    }
}
