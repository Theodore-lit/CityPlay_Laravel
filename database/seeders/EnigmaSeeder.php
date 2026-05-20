<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enigma;
use App\Models\Location;
use App\Models\EnigmaResponse;

class EnigmaSeeder extends Seeder
{
    public function run(): void
    {
        $amazone = Location::where('name', 'Place de l\'Amazone')->first();
        $dantokpa = Location::where('name', 'Marché Dantokpa')->first();
        $honme = Location::where('name', 'Musée Honmè')->first();
        $mosquee = Location::where('name', 'Grande Mosquée')->first();
        $ganvie = Location::where('name', 'Embarcadère de Ganvié')->first();
        $bioguera = Location::where('name', 'Place Bio Guéra')->first();
        $porteNonRetour = Location::where('name', 'Porte du Non-Retour')->first();
        $templePythons = Location::where('name', 'Temple des Pythons')->first();

        // Cotonou
        $enigma1 = Enigma::create([
            'location_id' => $amazone->id,
            'title' => 'La statue géante',
            'content' => 'Quelle est la hauteur approximative (en mètres) de la statue de l\'Amazone ?',
            'difficulty' => 'medium',
            'answer' => '30',
            'reward_coins' => 15,
            'reward_hearts' => 0,
            'type' => 'devinette',
            'image_path' => 'enigmas/amazone_statue.jpg'
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma1->id, 'content' => '20', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma1->id, 'content' => '30', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma1->id, 'content' => '40', 'is_correct' => false]);

        $enigma2 = Enigma::create([
            'location_id' => $dantokpa->id,
            'title' => 'Le carrefour commercial',
            'content' => 'Quel fleuve borde le marché Dantokpa ?',
            'difficulty' => 'easy',
            'answer' => 'Ouémé',
            'reward_coins' => 10,
            'reward_hearts' => 0,
            'type' => 'devinette',
            'image_path' => 'enigmas/dantokpa_market.jpg'
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma2->id, 'content' => 'Ouémé', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma2->id, 'content' => 'Niger', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma2->id, 'content' => 'Mono', 'is_correct' => false]);

        // Porto-Novo
        $enigma3 = Enigma::create([
            'location_id' => $honme->id,
            'title' => 'Le palais royal',
            'content' => 'Quel roi a construit le palais Honmè ?',
            'difficulty' => 'hard',
            'answer' => 'Toffa',
            'reward_coins' => 25,
            'reward_hearts' => 1,
            'type' => 'aventure',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma3->id, 'content' => 'Ghézo', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma3->id, 'content' => 'Toffa', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma3->id, 'content' => 'Béhanzin', 'is_correct' => false]);

        $enigma4 = Enigma::create([
            'location_id' => $mosquee->id,
            'title' => 'L\'architecture brésilienne',
            'content' => 'De quel pays s\'inspire l\'architecture de cette mosquée ?',
            'difficulty' => 'easy',
            'answer' => 'Brésil',
            'reward_coins' => 10,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma4->id, 'content' => 'Portugal', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma4->id, 'content' => 'Brésil', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma4->id, 'content' => 'Espagne', 'is_correct' => false]);

        // Calavi
        $enigma5 = Enigma::create([
            'location_id' => $ganvie->id,
            'title' => 'La cité lacustre',
            'content' => 'Sur quel lac est bâtie la cité de Ganvié ?',
            'difficulty' => 'medium',
            'answer' => 'Nokoué',
            'reward_coins' => 15,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma5->id, 'content' => 'Ahémé', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma5->id, 'content' => 'Nokoué', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma5->id, 'content' => 'Togo', 'is_correct' => false]);

        // Parakou
        $enigma6 = Enigma::create([
            'location_id' => $bioguera->id,
            'title' => 'Le guerrier wassangari',
            'content' => 'De quelle ville Bio Guéra était-il originaire ?',
            'difficulty' => 'hard',
            'answer' => 'N\'Dali',
            'reward_coins' => 25,
            'reward_hearts' => 1,
            'type' => 'aventure',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma6->id, 'content' => 'Parakou', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma6->id, 'content' => 'N\'Dali', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma6->id, 'content' => 'Nikki', 'is_correct' => false]);

        // Ouidah
        $enigma7 = Enigma::create([
            'location_id' => $porteNonRetour->id,
            'title' => 'Le symbole de l\'oubli',
            'content' => 'Combien de piliers principaux soutiennent la structure de la Porte du Non-Retour ?',
            'difficulty' => 'hard',
            'answer' => '4',
            'reward_coins' => 25,
            'reward_hearts' => 1,
            'type' => 'aventure',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma7->id, 'content' => '2', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma7->id, 'content' => '4', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma7->id, 'content' => '6', 'is_correct' => false]);

        $enigma8 = Enigma::create([
            'location_id' => $templePythons->id,
            'title' => 'Les gardiens sacrés',
            'content' => 'Quelle espèce de python est vénérée dans ce temple ?',
            'difficulty' => 'medium',
            'answer' => 'Python royal',
            'reward_coins' => 15,
            'reward_hearts' => 0,
            'type' => 'devinette',
        ]);

        EnigmaResponse::create(['enigma_id' => $enigma8->id, 'content' => 'Python de Seba', 'is_correct' => false]);
        EnigmaResponse::create(['enigma_id' => $enigma8->id, 'content' => 'Python royal', 'is_correct' => true]);
        EnigmaResponse::create(['enigma_id' => $enigma8->id, 'content' => 'Python birman', 'is_correct' => false]);
    }
}
