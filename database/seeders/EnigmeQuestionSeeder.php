<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Enigma;
use App\Models\EnigmaQuestion;
use App\Models\EnigmaQuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnigmeQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyage des tables pour repartir sur une base propre
        Schema::disableForeignKeyConstraints();
        DB::table('enigma_questions')->truncate();
        DB::table('enigma_question_options')->truncate();
        Schema::enableForeignKeyConstraints();



        // Mapping complet : Énigme (par nom de lieu) -> Questions -> Options
        $data = [
            // COTONOU
            'La Statue de l\'Amazone' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quel est le nom traditionnel donné à ces femmes guerrières en langue Fon ?",
                            'options' => [
                                ['text' => "Agodjié (ou Minon)", 'is_correct' => true],
                                ['text' => "Ashanti", 'is_correct' => false],
                                ['text' => "Vodounsi", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quelle est la matière principale de cette structure monumentale de 30m ?",
                            'options' => [
                                ['text' => "Bronze", 'is_correct' => true],
                                ['text' => "Marbre", 'is_correct' => false],
                                ['text' => "Acier inoxydable", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Marché Dantokpa' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quelle divinité protectrice a son autel sur le bord de la lagune près du marché ?",
                            'options' => [
                                ['text' => "Dan (le dieu serpent)", 'is_correct' => true],
                                ['text' => "Legba", 'is_correct' => false],
                                ['text' => "Gu", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quel pont célèbre enjambe le chenal à proximité immédiate du marché ?",
                            'options' => [
                                ['text' => "L'Ancien Pont (Pont Hubert Maga)", 'is_correct' => true],
                                ['text' => "Le Pont Martin Luther King", 'is_correct' => false],
                                ['text' => "Le Troisième Pont", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Place de l\'Étoile Rouge' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quelle idéologie politique ce monument célébrait-il lors de sa construction en 1974 ?",
                            'options' => [
                                ['text' => "Le Marxisme-Léninisme", 'is_correct' => true],
                                ['text' => "Le Libéralisme", 'is_correct' => false],
                                ['text' => "La Monarchie constitutionnelle", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Que tient l'homme au sommet du monument en plus du fusil et de la houe ?",
                            'options' => [
                                ['text' => "Un Livre (symbole de savoir)", 'is_correct' => true],
                                ['text' => "Un drapeau", 'is_correct' => false],
                                ['text' => "Une torche éternelle", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Cathédrale Notre-Dame' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quelles sont les deux couleurs caractéristiques qui ornent la façade de cet édifice néo-gothique ?",
                            'options' => [
                                ['text' => "Rouge et Blanc", 'is_correct' => true],
                                ['text' => "Bleu et Jaune", 'is_correct' => false],
                                ['text' => "Vert et Ocre", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "En quelle année cet édifice emblématique du quartier Ganhi a-t-il été inauguré ?",
                            'options' => [
                                ['text' => "1901", 'is_correct' => true],
                                ['text' => "1960", 'is_correct' => false],
                                ['text' => "1885", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Place de l\'Indépendance' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Comment s'appelle le monument central situé sur cette place ?",
                            'options' => [
                                ['text' => "Le Monument aux Morts", 'is_correct' => true],
                                ['text' => "La Porte du Non-Retour", 'is_correct' => false],
                                ['text' => "L'Arc de Triomphe", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quelle date historique est célébrée chaque année sur cette place par un défilé ?",
                            'options' => [
                                ['text' => "Le 1er Août (Fête nationale)", 'is_correct' => true],
                                ['text' => "Le 10 Janvier", 'is_correct' => false],
                                ['text' => "Le 22 Juin", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],

            // OUIDAH
            'Temple des Pythons' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quelle espèce de python, inoffensive pour l'homme, est vénérée dans ce sanctuaire ?",
                            'options' => [
                                ['text' => "Le Python Royal (Regius)", 'is_correct' => true],
                                ['text' => "Le Python de Seba", 'is_correct' => false],
                                ['text' => "L'Anaconda vert", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quel monument chrétien se trouve exactement en face du temple, symbolisant la tolérance ?",
                            'options' => [
                                ['text' => "La Basilique de l'Immaculée Conception", 'is_correct' => true],
                                ['text' => "Le Fort Portugais", 'is_correct' => false],
                                ['text' => "La Cathédrale Notre-Dame", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Forêt Sacrée de Kpassè' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "En quel arbre sacré le roi Kpassè, fondateur de Ouidah, se serait-il transformé ?",
                            'options' => [
                                ['text' => "Un Iroko", 'is_correct' => true],
                                ['text' => "Un Fromager", 'is_correct' => false],
                                ['text' => "Un Baobab", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quelle divinité de la forge et du fer est représentée par une statue d'acier dans la forêt ?",
                            'options' => [
                                ['text' => "Gu", 'is_correct' => true],
                                ['text' => "Heviosso", 'is_correct' => false],
                                ['text' => "Legba", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Fort Portugais' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "En quelle année ce fort (São João Baptista de Ajudá) a-t-il été officiellement annexé par le Dahomey ?",
                            'options' => [
                                ['text' => "1961", 'is_correct' => true],
                                ['text' => "1900", 'is_correct' => false],
                                ['text' => "1975", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quel trafiquant brésilien, devenu Vice-Roi de Ouidah, est une figure centrale de l'histoire du fort ?",
                            'options' => [
                                ['text' => "Francisco Félix de Souza (Le Chacha)", 'is_correct' => true],
                                ['text' => "Dom Pedro", 'is_correct' => false],
                                ['text' => "Vasco de Gama", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Porte du Non-Retour' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quel arbre situé sur la route menant à la plage servait aux esclaves à 'oublier' leur passé ?",
                            'options' => [
                                ['text' => "L'Arbre de l'Oubli", 'is_correct' => true],
                                ['text' => "L'Arbre du Retour", 'is_correct' => false],
                                ['text' => "Le Palmier sacré", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quel festival mondial de 1992 a permis l'érection de ce monument commémoratif ?",
                            'options' => [
                                ['text' => "Ouidah 92 (Festival des Arts et Cultures Vodoun)", 'is_correct' => true],
                                ['text' => "Le Festival mondial des esclaves", 'is_correct' => false],
                                ['text' => "La Biennale de Ouidah", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Basilique de Ouidah' => [
                'site' => [
                    'questions' => [
                        [
                            'text' => "En quelle année la construction de cette basilique, de style colonial, s'est-elle achevée ?",
                            'options' => [
                                ['text' => "1909", 'is_correct' => true],
                                ['text' => "1960", 'is_correct' => false],
                                ['text' => "1925", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "Quel Pape a visité ce lieu en 2011, rendant hommage à l'histoire religieuse du pays ?",
                            'options' => [
                                ['text' => "Benoît XVI", 'is_correct' => true],
                                ['text' => "Jean-Paul II", 'is_correct' => false],
                                ['text' => "François", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
        ];

        // On parcourt les lieux
        $locations = Location::all();

        foreach ($locations as $location) {
            if (!isset($data[$location->name])) continue;

            foreach ($data[$location->name] as $type => $enigmaContent) {
                // Pour cet exercice, on se concentre sur les énigmes "site" (is_site_specific = true)
                $isSiteSpecific = ($type === 'site');

                $enigma = Enigma::where('location_id', $location->id)
                    ->where('is_site_specific', $isSiteSpecific)
                    ->first();

                if ($enigma) {
                    foreach ($enigmaContent['questions'] as $qData) {
                        $question = EnigmaQuestion::create([
                            'enigma_id' => $enigma->id,
                            'question_text' => $qData['text'],
                        ]);

                        foreach ($qData['options'] as $oData) {
                            EnigmaQuestionOption::create([
                                'enigma_question_id' => $question->id,
                                'option_text' => $oData['text'],
                                'is_correct' => $oData['is_correct'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
