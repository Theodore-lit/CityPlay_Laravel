<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuizDifficultySeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all();

        if ($cities->isEmpty()) {
            return;
        }

        foreach ($cities as $city) {
            // Créer 10 quiz Faciles
            for ($i = 1; $i <= 10; $i++) {
                $quiz = Quiz::create([
                    'city_id' => $city->id,
                    'title' => "Quiz Facile #$i - {$city->name}",
                    'description' => "Un quiz simple pour découvrir {$city->name}.",
                    'difficulty' => 'easy',
                    'category' => 'Découverte',
                    'xp_reward' => 500, // 5 questions * 100 XP
                    'time_limit' => 60,
                ]);
                $this->addDummyQuestions($quiz);
            }

            // Créer 10 quiz Moyens
            for ($i = 1; $i <= 10; $i++) {
                $quiz = Quiz::create([
                    'city_id' => $city->id,
                    'title' => "Quiz Moyen #$i - {$city->name}",
                    'description' => "Un défi intermédiaire pour les connaisseurs de {$city->name}.",
                    'difficulty' => 'medium',
                    'category' => 'Culture',
                    'xp_reward' => 500,
                    'time_limit' => 45,
                ]);
                $this->addDummyQuestions($quiz);
            }

            // Créer 10 quiz Difficiles
            for ($i = 1; $i <= 10; $i++) {
                $quiz = Quiz::create([
                    'city_id' => $city->id,
                    'title' => "Quiz Difficile #$i - {$city->name}",
                    'description' => "Le test ultime sur les secrets de {$city->name}.",
                    'difficulty' => 'hard',
                    'category' => 'Expert',
                    'xp_reward' => 500,
                    'time_limit' => 30,
                ]);
                $this->addDummyQuestions($quiz);
            }
        }
    }

    private function addDummyQuestions($quiz)
    {
        for ($j = 1; $j <= 5; $j++) {
            Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => "Question n°$j pour {$quiz->title} ?",
                'options' => [
                    'A' => 'Réponse correcte',
                    'B' => 'Mauvaise réponse 1',
                    'C' => 'Mauvaise réponse 2',
                    'D' => 'Mauvaise réponse 3'
                ],
                'correct_option' => 'A',
                'explanation' => "Explication pour la question n°$j."
            ]);
        }
    }
}
