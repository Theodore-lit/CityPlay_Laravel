<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\City;

class MassiveQuizSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all();
        $quizTemplates = [
            [
                'title' => 'Histoire de :city',
                'description' => 'Découvrez les événements clés qui ont marqué l\'histoire de :city.',
                'questions' => [
                    [
                        'question' => 'Quelle est la période historique principale associée à :city ?',
                        'options' => ['Le Moyen Âge', 'L\'ère industrielle', 'L\'époque romaine', 'La Renaissance'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Pensez à l\'origine de la ville',
                        'explanation' => 'Chaque ville a une période historique qui l\'a définie.'
                    ],
                    [
                        'question' => 'Qui est la personnalité historique la plus liée à :city ?',
                        'options' => ['Un roi', 'Un poète', 'Un inventeur', 'Un leader'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Une figure emblématique',
                        'explanation' => 'De nombreuses villes sont associées à des personnalités célèbres.'
                    ],
                    [
                        'question' => 'Quel événement a marqué le développement de :city ?',
                        'options' => ['Une bataille', 'Une exposition universelle', 'La construction d\'un monument', 'La découverte d\'une ressource'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Un tournant important',
                        'explanation' => 'Un événement clé peut transformer une ville.'
                    ]
                ]
            ],
            [
                'title' => 'Culture et Traditions de :city',
                'description' => 'Testez vos connaissances sur les traditions et la culture locale de :city.',
                'questions' => [
                    [
                        'question' => 'Quel est le plat traditionnel de :city ?',
                        'options' => ['Un ragout', 'Une spécialité sucrée', 'Un poisson', 'Un plat végétarien'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Gastronomie locale',
                        'explanation' => 'Chaque ville a ses spécialités culinaires.'
                    ],
                    [
                        'question' => 'Quelle fête est traditionnellement célébrée à :city ?',
                        'options' => ['Le carnaval', 'Une fête religieuse', 'Un festival de musique', 'Un marché de Noël'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Événement annuel',
                        'explanation' => 'Les fêtes font partie de l\'identité culturelle d\'une ville.'
                    ],
                    [
                        'question' => 'Quel artiste est originaire de :city ?',
                        'options' => ['Un peintre', 'Un musicien', 'Un écrivain', 'Un cinéaste'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Créateur',
                        'explanation' => 'De nombreuses villes ont vu naître des talents artistiques.'
                    ]
                ]
            ],
            [
                'title' => 'Géographie et Paysages de :city',
                'description' => 'Explorez les particularités géographiques de :city.',
                'questions' => [
                    [
                        'question' => 'Quelle rivière traverse :city ?',
                        'options' => ['Une rivière principale', 'Un affluent', 'Un canal', 'Un lac'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Cours d\'eau',
                        'explanation' => 'Les rivières ont souvent joué un rôle dans l\'établissement des villes.'
                    ],
                    [
                        'question' => 'Quel type de paysage entoure :city ?',
                        'options' => ['La montagne', 'La plaine', 'La côte', 'La forêt'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Environnement naturel',
                        'explanation' => 'Le paysage influence le caractère d\'une ville.'
                    ],
                    [
                        'question' => 'Quel monument naturel est à proximité de :city ?',
                        'options' => ['Une cascade', 'Une grotte', 'Une falaise', 'Un parc national'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Spectacle de la nature',
                        'explanation' => 'Les villes sont souvent près de sites naturels magnifiques.'
                    ]
                ]
            ],
            [
                'title' => 'Architecture de :city',
                'description' => 'Découvrez les styles architecturaux qui définissent :city.',
                'questions' => [
                    [
                        'question' => 'Quel style architectural prédomine à :city ?',
                        'options' => ['Gothique', 'Classique', 'Art nouveau', 'Moderne'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Style des bâtiments',
                        'explanation' => 'L\'architecture est la signature visuelle d\'une ville.'
                    ],
                    [
                        'question' => 'Quel est le monument le plus emblématique de :city ?',
                        'options' => ['Une cathédrale', 'Un palais', 'Une tour', 'Un pont'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Symbole de la ville',
                        'explanation' => 'Chaque ville a son monument emblématique.'
                    ],
                    [
                        'question' => 'Quel quartier historique de :city est le plus connu ?',
                        'options' => ['Le vieux-port', 'La vieille ville', 'Le quartier des artères', 'Le quartier universitaire'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Quartier ancien',
                        'explanation' => 'Les vieux quartiers conservent l\'histoire de la ville.'
                    ]
                ]
            ],
            [
                'title' => 'Sport et Loisirs à :city',
                'description' => 'Testez vos connaissances sur les activités et le sport à :city.',
                'questions' => [
                    [
                        'question' => 'Quel sport est le plus populaire à :city ?',
                        'options' => ['Le football', 'Le basketball', 'Le rugby', 'Le tennis'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Sport collectif',
                        'explanation' => 'Les villes ont souvent une équipe sportive emblématique.'
                    ],
                    [
                        'question' => 'Quel événement sportif est organisé à :city ?',
                        'options' => ['Un marathon', 'Un tournoi', 'Une régate', 'Un match international'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Compétition',
                        'explanation' => 'Les villes accueillent souvent des événements sportifs majeurs.'
                    ],
                    [
                        'question' => 'Quel espace vert est le plus apprécié à :city ?',
                        'options' => ['Un parc', 'Un jardin', 'Un bois', 'Un promenade'],
                        'correct' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => 'Lieu de détente',
                        'explanation' => 'Les espaces verts sont essentiels au bien-être des habitants.'
                    ]
                ]
            ]
        ];

        foreach ($cities as $city) {
            // Créer 5-10 quiz par ville (max 10)
            $numQuizzes = rand(5, 10);
            $usedTemplateKeys = array_rand($quizTemplates, min($numQuizzes, count($quizTemplates)));
            
            // Ensure $usedTemplateKeys is always an array
            if (!is_array($usedTemplateKeys)) {
                $usedTemplateKeys = [$usedTemplateKeys];
            }
            
            // If we need more quizzes than templates, duplicate some
            if (count($usedTemplateKeys) < $numQuizzes) {
                $extraKeys = [];
                for ($i = 0; $i < $numQuizzes - count($usedTemplateKeys); $i++) {
                    $extraKeys[] = array_rand($quizTemplates);
                }
                $usedTemplateKeys = array_merge($usedTemplateKeys, $extraKeys);
            }
            
            $usedTemplateKeys = array_unique($usedTemplateKeys);

            foreach ($usedTemplateKeys as $idx) {
                $template = $quizTemplates[$idx];
                $title = str_replace(':city', $city->name, $template['title']);
                $description = str_replace(':city', $city->name, $template['description']);
                
                $quiz = Quiz::updateOrCreate(
                    ['city_id' => $city->id, 'title' => $title],
                    [
                        'description' => $description,
                        'category' => $this->getRandomCategory(),
                        'xp_reward' => rand(100, 300),
                        'time_limit' => rand(30, 60),
                    ]
                );

                // Chaque quiz a 3-5 questions
                $numQuestions = rand(3, 5);
                for ($j = 0; $j < $numQuestions; $j++) {
                    $qTemplate = $template['questions'][$j % count($template['questions'])];
                    $questionText = str_replace(':city', $city->name, $qTemplate['question']);
                    
                    $options = [];
                    foreach ($qTemplate['options'] as $letter => $optionText) {
                        $options[$letter] = $optionText;
                    }
                    
                    Question::updateOrCreate(
                        ['quiz_id' => $quiz->id, 'question_text' => $questionText],
                        [
                            'options' => $options,
                            'correct_option' => $qTemplate['correct'],
                            'hint' => $qTemplate['hint'],
                            'explanation' => $qTemplate['explanation'],
                        ]
                    );
                }
            }
        }
    }

    private function getRandomCategory(): string
    {
        $categories = ['Histoire', 'Culture', 'Géographie', 'Gastronomie', 'Tradition'];
        return $categories[array_rand($categories)];
    }
}
