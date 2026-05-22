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

        // Mapping des énigmes par lieu
        $enigmaData = [
            'Porte du Non-Retour' => [
                'easy' => [
                    'content' => "Je me dresse face à l'immensité bleue, là où le voyage commençait sans espoir de retour. Que suis-je ?",
                    'answer' => "Mémorial",
                    'indices' => ["Un monument pour se souvenir.", "M _ _ _ _ _ _ L"],
                ],
                'medium' => [
                    'content' => "Combien de piliers soutiennent ma structure principale, symbolisant les âmes parties vers l'inconnu ?",
                    'answer' => "Deux",
                    'indices' => ["Regardez les grandes colonnes.", "D _ _ X"],
                ],
                'hard' => [
                    'content' => "Sur mon arche, des silhouettes marchent enchaînées. Vers quel point cardinal regardent-elles ?",
                    'answer' => "Ouest",
                    'indices' => ["La direction du soleil couchant.", "O _ _ _ T"],
                ],
                'site' => [
                    'content' => "Regardez les bas-reliefs sur les côtés. Quel animal est représenté aux pieds des captifs ?",
                    'answer' => "Chien",
                    'indices' => ["Un fidèle compagnon détourné.", "C _ _ _ N"],
                ]
            ],
            'Temple des Pythons' => [
                'easy' => [
                    'content' => "Je rampe sans pattes et je suis vénéré ici comme une divinité protectrice. Qui suis-je ?",
                    'answer' => "Python",
                    'indices' => ["Un reptile sacré.", "P _ _ _ _ N"],
                ],
                'medium' => [
                    'content' => "Face à moi se dresse une église. Quel est le nom de cette basilique qui cohabite avec le sacré ancien ?",
                    'answer' => "Immaculée",
                    'indices' => ["Lié à la Vierge Marie.", "I _ _ _ _ _ _ _ E"],
                ],
                'hard' => [
                    'content' => "Dans la cour, un arbre centenaire abrite les esprits. De quelle espèce s'agit-il ?",
                    'answer' => "Iroko",
                    'indices' => ["Un géant de la forêt.", "I _ _ _ O"],
                ],
                'site' => [
                    'content' => "Entrez dans le sanctuaire. Combien de pythons pouvez-vous compter sur le premier muret à gauche ?",
                    'answer' => "Douze",
                    'indices' => ["Comptez bien les anneaux.", "D _ _ _ E"],
                ]
            ],
            'Place de l\'Amazone' => [
                'easy' => [
                    'content' => "Je suis le symbole de la fierté nationale, une femme guerrière haute de plusieurs mètres. Où suis-je ?",
                    'answer' => "Cotonou",
                    'indices' => ["La capitale économique.", "C _ _ _ _ _ U"],
                ],
                'medium' => [
                    'content' => "Quelle arme est tenue fermement par la main droite de la statue géante ?",
                    'answer' => "Fusil",
                    'indices' => ["Une arme à feu ancienne.", "F _ _ _ L"],
                ],
                'hard' => [
                    'content' => "Sous mes pieds, une inscription rend hommage aux filles du Dahomey. Quel mot qualifie leur bravoure ?",
                    'answer' => "Héroïque",
                    'indices' => ["Digne d'une légende.", "H _ _ _ _ _ _ E"],
                ],
                'site' => [
                    'content' => "Observez le socle du monument. Quel est le matériau principal utilisé pour le revêtement ?",
                    'answer' => "Granit",
                    'indices' => ["Une pierre très dure.", "G _ _ _ _ T"],
                ]
            ],
            'Grande Mosquée de Porto-Novo' => [
                'easy' => [
                    'content' => "On dirait une église du Brésil, mais je suis un lieu de prière musulman. Qui suis-je ?",
                    'answer' => "Mosquée",
                    'indices' => ["Lieu de culte.", "M _ _ _ _ E"],
                ],
                'medium' => [
                    'content' => "De quelle couleur vive sont peintes mes façades, rappelant le style baroque ?",
                    'answer' => "Jaune",
                    'indices' => ["La couleur du soleil.", "J _ _ _ E"],
                ],
                'hard' => [
                    'content' => "Le style architectural qui m'a inspiré vient d'Amérique latine. Comment appelle-t-on ces anciens esclaves revenus au pays ?",
                    'answer' => "Agouda",
                    'indices' => ["Le nom de la communauté.", "A _ _ _ _ A"],
                ],
                'site' => [
                    'content' => "Comptez les fenêtres sur la façade principale du premier étage. Quel est leur nombre ?",
                    'answer' => "Sept",
                    'indices' => ["Un chiffre porte-bonheur.", "S _ _ T"],
                ]
            ],
        ];

        foreach ($locations as $location) {
            $data = $enigmaData[$location->name] ?? [
                'easy' => [
                    'content' => "Je suis un lieu emblématique de {$location->name}. Trouvez-moi pour continuer l'aventure.",
                    'answer' => "Découverte",
                    'indices' => ["L'action de trouver.", "D _ _ _ _ _ _ _ E"],
                ],
                'medium' => [
                    'content' => "Cherchez l'histoire cachée derrière les murs de {$location->name}.",
                    'answer' => "Secret",
                    'indices' => ["Quelque chose de caché.", "S _ _ _ _ T"],
                ],
                'hard' => [
                    'content' => "La clé de ce mystère réside dans le passé glorieux de {$location->name}.",
                    'answer' => "Héritage",
                    'indices' => ["Ce que nous laissent nos ancêtres.", "H _ _ _ _ _ _ E"],
                ],
                'site' => [
                    'content' => "Regardez autour de vous. Quelle est la couleur dominante de ce site ?",
                    'answer' => "Rouge",
                    'indices' => ["La couleur de la terre.", "R _ _ _ E"],
                ]
            ];

            // 1. Énigme de déblocage (Facile)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'easy', 'is_site_specific' => false],
                [
                    'content' => $data['easy']['content'],
                    'answer' => $data['easy']['answer'],
                    'reward_coins' => 10,
                    'type' => 'aventure',
                    'indices' => $data['easy']['indices'],
                ]
            );

            // 2. Énigme de déblocage (Moyen)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'medium', 'is_site_specific' => false],
                [
                    'content' => $data['medium']['content'],
                    'answer' => $data['medium']['answer'],
                    'reward_coins' => 25,
                    'type' => 'aventure',
                    'indices' => $data['medium']['indices'],
                ]
            );

            // 3. Énigme de déblocage (Difficile)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'difficulty' => 'hard', 'is_site_specific' => false],
                [
                    'content' => $data['hard']['content'],
                    'answer' => $data['hard']['answer'],
                    'reward_coins' => 50,
                    'type' => 'aventure',
                    'indices' => $data['hard']['indices'],
                ]
            );

            // 4. Énigme SUR SITE (Analyse du lieu)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'is_site_specific' => true],
                [
                    'content' => $data['site']['content'],
                    'answer' => $data['site']['answer'],
                    'difficulty' => 'medium',
                    'reward_coins' => 100,
                    'type' => 'aventure',
                    'indices' => $data['site']['indices'],
                    'image_path' => 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?q=80&w=800',
                ]
            );
        }
    }
}
