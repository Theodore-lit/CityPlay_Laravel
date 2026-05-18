<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quiz = Quiz::create([
            'title' => 'Culture & Histoire du Bénin',
            'description' => 'Testez vos connaissances sur l\'ancien Royaume du Dahomey et les traditions locales.',
            'category' => 'Histoire',
            'xp_reward' => 250,
            'time_limit' => 30,
        ]);

        $questions = [
            [
                'question_text' => 'Quelle ville était la capitale de l\'ancien Royaume du Dahomey ?',
                'options' => [
                    'A' => 'Cotonou',
                    'B' => 'Porto-Novo',
                    'C' => 'Abomey',
                    'D' => 'Ouidah'
                ],
                'correct_option' => 'C',
                'explanation' => 'Abomey était le cœur politique et militaire du puissant Royaume du Dahomey.'
            ],
            [
                'question_text' => 'Qui était le dernier roi souverain du Dahomey avant la colonisation ?',
                'options' => [
                    'A' => 'Roi Guezo',
                    'B' => 'Roi Béhanzin',
                    'C' => 'Roi Agoli-Agbo',
                    'D' => 'Roi Toffa'
                ],
                'correct_option' => 'B',
                'explanation' => 'Le Roi Béhanzin est célèbre pour sa résistance héroïque contre les troupes françaises.'
            ],
            [
                'question_text' => 'Quel monument célèbre se trouve à Ouidah pour commémorer la traite négrière ?',
                'options' => [
                    'A' => 'La Porte du Non-Retour',
                    'B' => 'La Place des Martyrs',
                    'C' => 'Le Temple des Pythons',
                    'D' => 'Le Palais Royal'
                ],
                'correct_option' => 'A',
                'explanation' => 'La Porte du Non-Retour est un mémorial poignant situé sur la plage de Ouidah.'
            ]
        ];

        foreach ($questions as $q) {
            Question::create(array_merge($q, ['quiz_id' => $quiz->id]));
        }

        $quiz2 = Quiz::create([
            'title' => 'Géographie & Nature',
            'description' => 'Découvrez les paysages variés, du Parc de la Pendjari aux cités lacustres.',
            'category' => 'Géographie',
            'xp_reward' => 150,
            'time_limit' => 20,
        ]);

        Question::create([
            'quiz_id' => $quiz2->id,
            'question_text' => 'Quelle est la "Venise de l\'Afrique" située au Bénin ?',
            'options' => [
                'A' => 'Sô-Ava',
                'B' => 'Ganvié',
                'C' => 'Grand-Popo',
                'D' => 'Bopa'
            ],
            'correct_option' => 'B',
            'explanation' => 'Ganvié est une cité lacustre unique au monde, construite sur le lac Nokoué.'
        ]);
    }
}
