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

        foreach ($cities as $city) {
            // Créer 30 quiz par ville
            for ($i = 1; $i <= 30; $i++) {
                $quiz = Quiz::create([
                    'city_id' => $city->id,
                    'title' => "Défi {$city->name} #{$i}",
                    'description' => "Testez vos connaissances sur l'histoire et la culture de {$city->name}. Défi n°{$i}.",
                    'category' => $this->getRandomCategory(),
                    'xp_reward' => rand(150, 500),
                    'time_limit' => rand(30, 90),
                ]);

                // Chaque quiz a 5 questions
                for ($j = 1; $j <= 5; $j++) {
                    Question::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => "Question {$j} pour le quiz {$i} de {$city->name} : Quel est ce monument historique ?",
                        'options' => [
                            'A' => "Option A de la question {$j}",
                            'B' => "Option B de la question {$j}",
                            'C' => "Option C de la question {$j}",
                            'D' => "Option D de la question {$j}",
                        ],
                        'correct_option' => ['A', 'B', 'C', 'D'][rand(0, 3)],
                        'hint' => "Indice pour la question {$j} : Regardez bien l'architecture.",
                        'explanation' => "Explication détaillée de la réponse pour la question {$j}.",
                    ]);
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
