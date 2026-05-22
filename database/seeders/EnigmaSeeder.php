<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enigma;
use App\Models\Location;

class EnigmaSeeder extends Seeder
{
    public function run(): void
    {
        // --- COTONOU ---
        $amazone = Location::where('name', 'La Statue de l\'Amazone')->first();
        if ($amazone) {
            Enigma::updateOrCreate(
                ['location_id' => $amazone->id],
                [
                    'content' => 'Je mesure 30 mètres de haut et je rends hommage aux guerrières Agodjié. Qui suis-je ?',
                    'answer' => 'L\'Amazone',
                    'indices' => [
                        'Je suis faite de bronze.',
                        'Je suis située près du Palais de la Marina.'
                    ],
                    'difficulty' => 'easy',
                    'reward_coins' => 100,
                ]
            );
        }

        $dantokpa = Location::where('name', 'Marché Dantokpa')->first();
        if ($dantokpa) {
            Enigma::updateOrCreate(
                ['location_id' => $dantokpa->id],
                [
                    'content' => 'Quel est le nom du marché qui s\'étend sur plus de 20 hectares au bord de la lagune de Cotonou ?',
                    'answer' => 'Dantokpa',
                    'indices' => [
                        'C\'est le plus grand marché de l\'Afrique de l\'Ouest.',
                        'Son nom signifie "sur le bord de la lagune de Dan".'
                    ],
                    'difficulty' => 'easy',
                    'reward_coins' => 80,
                ]
            );
        }

        $etoileRouge = Location::where('name', 'Place de l\'Étoile Rouge')->first();
        if ($etoileRouge) {
            Enigma::updateOrCreate(
                ['location_id' => $etoileRouge->id],
                [
                    'content' => 'Quel monument révolutionnaire en forme d\'étoile à cinq branches se trouve au cœur de Cotonou ?',
                    'answer' => 'L\'Étoile Rouge',
                    'indices' => [
                        'Elle symbolise l\'unité nationale.',
                        'C\'est un grand carrefour circulaire.'
                    ],
                    'difficulty' => 'easy',
                    'reward_coins' => 90,
                ]
            );
        }

        // --- OUIDAH ---
        $pythons = Location::where('name', 'Temple des Pythons')->first();
        if ($pythons) {
            Enigma::updateOrCreate(
                ['location_id' => $pythons->id],
                [
                    'content' => 'Dans quel sanctuaire de Ouidah peut-on voir des dizaines de serpents vénérés circuler librement ?',
                    'answer' => 'Le Temple des Pythons',
                    'indices' => [
                        'Ces serpents sont des pythons royaux.',
                        'C\'est un lieu sacré pour le culte Vodoun.'
                    ],
                    'difficulty' => 'easy',
                    'reward_coins' => 120,
                ]
            );
        }

        $kpasse = Location::where('name', 'Forêt Sacrée de Kpassè')->first();
        if ($kpasse) {
            Enigma::updateOrCreate(
                ['location_id' => $kpasse->id],
                [
                    'content' => 'Quel est le nom de la forêt où le roi Kpassè se serait transformé en arbre pour échapper à ses ennemis ?',
                    'answer' => 'Forêt Sacrée de Kpassè',
                    'indices' => [
                        'On y trouve des arbres centenaires.',
                        'C\'est un lieu mystique au cœur de Ouidah.'
                    ],
                    'difficulty' => 'medium',
                    'reward_coins' => 150,
                ]
            );
        }

        $porte = Location::where('name', 'Porte du Non-Retour')->first();
        if ($porte) {
            Enigma::updateOrCreate(
                ['location_id' => $porte->id],
                [
                    'content' => 'Quel monument solennel sur la plage de Ouidah marque le départ forcé des esclaves vers les Amériques ?',
                    'answer' => 'La Porte du Non-Retour',
                    'indices' => [
                        'C\'est un mémorial de la traite négrière.',
                        'Elle se situe à la fin de la Route des Esclaves.'
                    ],
                    'difficulty' => 'easy',
                    'reward_coins' => 110,
                ]
            );
        }
    }
}
