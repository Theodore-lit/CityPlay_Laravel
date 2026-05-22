<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Enigma;
use App\Models\EnigmaQuestion;
use App\Models\EnigmaQuestionOption;
use Illuminate\Support\Facades\DB;

class EnigmeQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyage des tables pour repartir sur une base propre
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('enigma_questions')->truncate();
        DB::table('enigma_question_options')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Mapping complet : Énigme -> Questions -> Options
        $data = [
            'Porte du Non-Retour' => [
                'easy' => [
                    'questions' => [
                        [
                            'text' => "Quel est l'objectif principal de ce monument ?",
                            'options' => [
                                ['text' => "Commémorer les victimes de la traite négrière", 'is_correct' => true],
                                ['text' => "Célébrer la victoire d'une bataille navale", 'is_correct' => false],
                                ['text' => "Marquer l'entrée d'un port commercial moderne", 'is_correct' => false],
                            ]
                        ],
                        [
                            'text' => "En quelle année ce monument a-t-il été inauguré ?",
                            'options' => [
                                ['text' => "1992 (lors du festival Ouidah 92)", 'is_correct' => true],
                                ['text' => "1960 (indépendance du Bénin)", 'is_correct' => false],
                                ['text' => "2010 (cinquantenaire)", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'medium' => [
                    'questions' => [
                        [
                            'text' => "Que symbolisent les deux grands piliers centraux ?",
                            'options' => [
                                ['text' => "Le lien brisé entre l'Afrique et sa diaspora", 'is_correct' => true],
                                ['text' => "Les deux anciens rois d'Ouidah", 'is_correct' => false],
                                ['text' => "Les deux religions dominantes de la région", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'hard' => [
                    'questions' => [
                        [
                            'text' => "Vers quel point cardinal la porte est-elle orientée, symbolisant le départ ?",
                            'options' => [
                                ['text' => "L'Ouest (vers l'Océan Atlantique)", 'is_correct' => true],
                                ['text' => "L'Est (vers le lever du soleil)", 'is_correct' => false],
                                ['text' => "Le Nord (vers les terres intérieures)", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'site' => [
                    'questions' => [
                        [
                            'text' => "Quel animal est sculpté aux pieds des captifs sur les bas-reliefs ?",
                            'options' => [
                                ['text' => "Le chien (symbole de garde et de traque)", 'is_correct' => true],
                                ['text' => "Le lion (symbole de royauté)", 'is_correct' => false],
                                ['text' => "Le serpent (symbole de divinité)", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Temple des Pythons' => [
                'easy' => [
                    'questions' => [
                        [
                            'text' => "Quelle espèce de python est vénérée ici ?",
                            'options' => [
                                ['text' => "Le Python Royal (Regius)", 'is_correct' => true],
                                ['text' => "Le Python de Seba", 'is_correct' => false],
                                ['text' => "Le Boa Constrictor", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'medium' => [
                    'questions' => [
                        [
                            'text' => "Quel monument religieux fait face au Temple, illustrant la tolérance ?",
                            'options' => [
                                ['text' => "La Basilique de l'Immaculée Conception", 'is_correct' => true],
                                ['text' => "La Grande Mosquée d'Ouidah", 'is_correct' => false],
                                ['text' => "Une synagogue historique", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'hard' => [
                    'questions' => [
                        [
                            'text' => "Quel arbre sacré trône dans la cour du temple ?",
                            'options' => [
                                ['text' => "L'Iroko centenaire", 'is_correct' => true],
                                ['text' => "Le Baobab géant", 'is_correct' => false],
                                ['text' => "Le Fromager royal", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'site' => [
                    'questions' => [
                        [
                            'text' => "Combien de pythons sont traditionnellement représentés sur le muret principal ?",
                            'options' => [
                                ['text' => "Douze", 'is_correct' => true],
                                ['text' => "Sept", 'is_correct' => false],
                                ['text' => "Vingt-quatre", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Place de l\'Amazone' => [
                'easy' => [
                    'questions' => [
                        [
                            'text' => "Qui est la figure historique qui a inspiré cette statue ?",
                            'options' => [
                                ['text' => "La Reine Tassi Hangbé", 'is_correct' => true],
                                ['text' => "La Reine Victoria", 'is_correct' => false],
                                ['text' => "L'Amazone Seh-Dong-Hong-Be", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'medium' => [
                    'questions' => [
                        [
                            'text' => "Quelle arme la guerrière tient-elle dans sa main droite ?",
                            'options' => [
                                ['text' => "Un fusil à silex", 'is_correct' => true],
                                ['text' => "Un sabre recourbé", 'is_correct' => false],
                                ['text' => "Une lance traditionnelle", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'hard' => [
                    'questions' => [
                        [
                            'text' => "Quel terme qualifie la bravoure des Amazones sur le socle ?",
                            'options' => [
                                ['text' => "Héroïque", 'is_correct' => true],
                                ['text' => "Légendaire", 'is_correct' => false],
                                ['text' => "Éternelle", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'site' => [
                    'questions' => [
                        [
                            'text' => "En quel matériau est fait le revêtement du socle ?",
                            'options' => [
                                ['text' => "Granit", 'is_correct' => true],
                                ['text' => "Marbre", 'is_correct' => false],
                                ['text' => "Béton poli", 'is_correct' => false],
                            ]
                        ]
                    ]
                ]
            ],
            'Grande Mosquée de Porto-Novo' => [
                'easy' => [
                    'questions' => [
                        [
                            'text' => "Quel style architectural caractérise cette mosquée ?",
                            'options' => [
                                ['text' => "Afro-brésilien (Baroque)", 'is_correct' => true],
                                ['text' => "Soudano-sahélien", 'is_correct' => false],
                                ['text' => "Ottoman classique", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'medium' => [
                    'questions' => [
                        [
                            'text' => "Quelle couleur domine la façade de la mosquée ?",
                            'options' => [
                                ['text' => "Le jaune ocre", 'is_correct' => true],
                                ['text' => "Le bleu turquoise", 'is_correct' => false],
                                ['text' => "Le blanc immaculé", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'hard' => [
                    'questions' => [
                        [
                            'text' => "Comment appelle-t-on les anciens esclaves revenus du Brésil ?",
                            'options' => [
                                ['text' => "Les Agoudas", 'is_correct' => true],
                                ['text' => "Les Créoles", 'is_correct' => false],
                                ['text' => "Les Métis", 'is_correct' => false],
                            ]
                        ]
                    ]
                ],
                'site' => [
                    'questions' => [
                        [
                            'text' => "Combien de fenêtres compte la façade principale du premier étage ?",
                            'options' => [
                                ['text' => "Sept", 'is_correct' => true],
                                ['text' => "Cinq", 'is_correct' => false],
                                ['text' => "Neuf", 'is_correct' => false],
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

            foreach ($data[$location->name] as $difficulty => $enigmaContent) {
                $isSiteSpecific = ($difficulty === 'site');

                // Trouver l'énigme correspondante
                $enigmaQuery = Enigma::where('location_id', $location->id)
                    ->where('is_site_specific', $isSiteSpecific);

                if (!$isSiteSpecific) {
                    $enigmaQuery->where('difficulty', $difficulty);
                }

                $enigma = $enigmaQuery->first();

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
