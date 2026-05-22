<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enigma;
use App\Models\Location;

class EnigmaSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère tous les lieux créés par LocationSeeder
        $locations = Location::all();

        $enigmaData = [
            // COTONOU
            'La Statue de l\'Amazone' => [
                'easy' => [
                    'content' => "Je suis la fière guerrière de bronze qui veille sur le boulevard de la Marina.",
                    'answer' => "Amazone",
                    'indices' => ["Je mesure 30m de haut.", "Symbole du courage féminin."],
                ],
                'medium' => [
                    'content' => "Dos à la mer, je symbolise le 'Bénin debout' et rends hommage aux Agodjié.",
                    'answer' => "Agodjié",
                    'indices' => ["Inaugurée en 2022.", "Je porte une lance et un fusil."],
                ],
                'hard' => [
                    'content' => "Fruit de l'artiste Li Xiangqun, mon socle de granit domine le nouveau paysage côtier.",
                    'answer' => "Li Xiangqun",
                    'indices' => ["Je suis le plus grand monument du pays.", "Je représente l'héroïsme national."],
                ],
                'site' => [
                    'content' => "Observez bien la statue. Quel métal compose majoritairement cette structure monumentale ?",
                    'answer' => "Bronze",
                    'indices' => ["Un métal qui verdit avec le temps.", "Alliage de cuivre et d'étain."],
                ]
            ],
            'Marché Dantokpa' => [
                'easy' => [
                    'content' => "Cœur battant de la ville, où l'on trouve tout, du tissu aux épices.",
                    'answer' => "Dantokpa",
                    'indices' => ["Au bord de la lagune.", "On y entend toutes les langues du pays."],
                ],
                'medium' => [
                    'content' => "Le plus grand labyrinthe commercial du pays, entre le pont et la lagune.",
                    'answer' => "Marché",
                    'indices' => ["Mon nom est lié à une divinité.", "Poumon économique."],
                ],
                'hard' => [
                    'content' => "Fondé en 1963, mon nom signifie 'sur le bord de la lagune de Dan'.",
                    'answer' => "Dan",
                    'indices' => ["Dan est le dieu serpent.", "Mon bâtiment principal a plusieurs étages."],
                ],
                'site' => [
                    'content' => "Regardez vers le chenal. Quel pont célèbre relie les deux rives à proximité du marché ?",
                    'answer' => "Ancien Pont",
                    'indices' => ["Construit sous Hubert Maga.", "Il enjambe le chenal."],
                ]
            ],
            'Place de l\'Étoile Rouge' => [
                'easy' => [
                    'content' => "Immense carrefour circulaire marqué par une étoile géante au centre.",
                    'answer' => "Étoile Rouge",
                    'indices' => ["Ma couleur est le rouge.", "Point central de la circulation."],
                ],
                'medium' => [
                    'content' => "Monument révolutionnaire du PRPB, je prône l'unité nationale.",
                    'answer' => "Révolution",
                    'indices' => ["Construite dans les années 70.", "Kérékou l'a inaugurée."],
                ],
                'hard' => [
                    'content' => "Je représente un homme tenant une arme, une houe et un livre.",
                    'answer' => "Unité",
                    'indices' => ["Le monument est une flèche.", "Célèbre la révolution de 1974."],
                ],
                'site' => [
                    'content' => "Observez le monument central. Que tient l'homme en plus du fusil et de la houe ?",
                    'answer' => "Un Livre",
                    'indices' => ["Symbole de connaissance.", "Éducation pour tous."],
                ]
            ],
            'Cathédrale Notre-Dame' => [
                'easy' => [
                    'content' => "Grande église aux rayures rouges et blanches près de l'ancien pont.",
                    'answer' => "Cathédrale",
                    'indices' => ["Plus célèbre église de la ville.", "Architecture colorée."],
                ],
                'medium' => [
                    'content' => "Édifice du début du 20ème siècle dominant le quartier de Ganhi.",
                    'answer' => "Ganhi",
                    'indices' => ["Inaugurée en 1901.", "Style néo-gothique."],
                ],
                'hard' => [
                    'content' => "Unique pour mes carreaux de céramique bicolores en Afrique de lhésitant.",
                    'answer' => "Céramique",
                    'indices' => ["Siège de l'archevêché.", "Non loin du chenal."],
                ],
                'site' => [
                    'content' => "Quelles sont les deux couleurs caractéristiques de la façade ?",
                    'answer' => "Rouge et Blanc",
                    'indices' => ["Couleurs des briques.", "Très visible de loin."],
                ]
            ],
            'Place de l\'Indépendance' => [
                'easy' => [
                    'content' => "Lieu de célébration de la fête nationale le 1er août.",
                    'answer' => "Indépendance",
                    'indices' => ["Quartier administratif.", "On y voit le Monument aux Morts."],
                ],
                'medium' => [
                    'content' => "Entourée de ministères, j'abrite une flamme éternelle.",
                    'answer' => "Flamme",
                    'indices' => ["Centre civique de la nation.", "Lieu des défilés."],
                ],
                'hard' => [
                    'content' => "Lieu symbolique où la souveraineté fut proclamée en 1960.",
                    'answer' => "1960",
                    'indices' => ["Hubert Maga y était.", "Cœur du pouvoir post-colonial."],
                ],
                'site' => [
                    'content' => "Quel est le nom du monument central dédié aux combattants ?",
                    'answer' => "Monument aux Morts",
                    'indices' => ["Hommage aux soldats.", "Tombés pour la patrie."],
                ]
            ],

            // OUIDAH
            'Temple des Pythons' => [
                'easy' => [
                    'content' => "Entrez ici pour voir des serpents sacrés qui ne mordent jamais.",
                    'answer' => "Python",
                    'indices' => ["En liberté dans un temple.", "On peut les porter au cou."],
                ],
                'medium' => [
                    'content' => "Face à la basilique, je suis le sanctuaire du dieu Dangbé.",
                    'answer' => "Dangbé",
                    'indices' => ["Reptiles vénérés.", "Lieu central du Vaudou."],
                ],
                'hard' => [
                    'content' => "Ces reptiles auraient sauvé le roi Kpassè lors d'une guerre ancienne.",
                    'answer' => "Kpassè",
                    'indices' => ["Ils sortent la nuit en ville.", "Interdit de les tuer."],
                ],
                'site' => [
                    'content' => "Quel édifice chrétien est situé exactement en face du temple ?",
                    'answer' => "Basilique",
                    'indices' => ["Signe de cohabitation religieuse.", "Église blanche et bleue."],
                ]
            ],
            'Forêt Sacrée de Kpassè' => [
                'easy' => [
                    'content' => "Parc de statues mystiques et d'arbres géants où un roi a disparu.",
                    'answer' => "Forêt Sacrée",
                    'indices' => ["Fondateur de la ville.", "Divinités sculptées."],
                ],
                'medium' => [
                    'content' => "Lieu où le premier roi de Ouidah s'est transformé en arbre.",
                    'answer' => "Arbre",
                    'indices' => ["À l'entrée de la ville.", "Famille Houédjissou."],
                ],
                'hard' => [
                    'content' => "Mes statues d'acier racontent le panthéon : Legba, Gu et Lissa.",
                    'answer' => "Panthéon",
                    'indices' => ["Arbres centenaires.", "Domaine de l'invisible."],
                ],
                'site' => [
                    'content' => "Quelle divinité de la forge et du fer est représentée par une statue d'acier ?",
                    'answer' => "Gu",
                    'indices' => ["Dieu du fer.", "Patron des forgerons."],
                ]
            ],
            'Fort Portugais' => [
                'easy' => [
                    'content' => "Ancienne forteresse européenne devenue musée d'histoire.",
                    'answer' => "Fort Portugais",
                    'indices' => ["Nom d'un pays d'Europe.", "Canons à l'entrée."],
                ],
                'medium' => [
                    'content' => "Construit en 1721 (Ajuda), j'étais une enclave étrangère.",
                    'answer' => "Portugal",
                    'indices' => ["Enclave jusqu'en 1961.", "Reliques de la traite."],
                ],
                'hard' => [
                    'content' => "Dernier fort resté sous contrôle étranger après 1960.",
                    'answer' => "Ajuda",
                    'indices' => ["Annexé en 1961.", "Résidence du Chacha."],
                ],
                'site' => [
                    'content' => "Quel célèbre trafiquant brésilien, ami du roi Guézo, résidait ici ?",
                    'answer' => "Francisco Félix de Souza",
                    'indices' => ["Surnommé le Chacha.", "Ami du roi Guézo."],
                ]
            ],
            'Porte du Non-Retour' => [
                'easy' => [
                    'content' => "Grand arc de triomphe sur la plage face à l'atlantique.",
                    'answer' => "Non-Retour",
                    'indices' => ["Mémoire des esclaves.", "Fin de la Route."],
                ],
                'medium' => [
                    'content' => "Érigé par l'UNESCO en 1995 pour le départ vers les Amériques.",
                    'answer' => "UNESCO",
                    'indices' => ["Bas-reliefs sombres.", "Sable de Djègbadji."],
                ],
                'hard' => [
                    'content' => "Lieu précis de l'embarquement sur les navires négriers.",
                    'answer' => "Navire",
                    'indices' => ["Mémoire internationale.", "Festival Ouidah 92."],
                ],
                'site' => [
                    'content' => "Quel festival mondial de 1992 est à l'origine de l'érection de cet arc ?",
                    'answer' => "Ouidah 92",
                    'indices' => ["Premier festival Vaudou.", "Nommé selon l'année."],
                ]
            ],
            'Basilique de Ouidah' => [
                'easy' => [
                    'content' => "Plus grande église de Ouidah, toute blanche et bleue.",
                    'answer' => "Basilique",
                    'indices' => ["Plein centre-ville.", "Près des pythons."],
                ],
                'medium' => [
                    'content' => "Première basilique mineure d'Afrique de l'Ouest.",
                    'answer' => "Mineure",
                    'indices' => ["Missionnaires français.", "Inaugurée en 1909."],
                ],
                'hard' => [
                    'content' => "Construction débutée en 1903 sur un ancien palais royal.",
                    'answer' => "1903",
                    'indices' => ["Dédiée à la Vierge.", "Visite de Benoît XVI."],
                ],
                'site' => [
                    'content' => "Quel Pape a visité cette basilique en novembre 2011 ?",
                    'answer' => "Benoît XVI",
                    'indices' => ["Pape allemand.", "Prédécesseur de François."],
                ]
            ],
        ];

        foreach ($locations as $location) {
            if (!isset($enigmaData[$location->name])) continue;

            $data = $enigmaData[$location->name];

            // 1. Facile (Navigation)
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

            // 2. Moyen (Navigation)
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

            // 3. Difficile (Navigation)
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

            // 4. SUR SITE (Questions techniques)
            Enigma::updateOrCreate(
                ['location_id' => $location->id, 'is_site_specific' => true],
                [
                    'content' => $data['site']['content'],
                    'answer' => $data['site']['answer'],
                    'difficulty' => 'medium',
                    'reward_coins' => 100,
                    'type' => 'aventure',
                    'indices' => $data['site']['indices'],
                ]
            );
        }
    }
}
