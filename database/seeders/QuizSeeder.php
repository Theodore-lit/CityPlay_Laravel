<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Quiz: Culture & Histoire du Bénin
        $quiz1 = Quiz::updateOrCreate(
            ['title' => 'Culture & Histoire du Bénin'],
            [
                'description' => 'Testez vos connaissances sur l\'ancien Royaume du Dahomey et les traditions locales.',
                'category' => 'Histoire',
                'xp_reward' => 300,
                'time_limit' => 45,
            ]
        );

        $questions1 = [
            [
                'question_text' => 'Quelle ville était la capitale de l\'ancien Royaume du Dahomey ?',
                'options' => ['A' => 'Cotonou', 'B' => 'Porto-Novo', 'C' => 'Abomey', 'D' => 'Ouidah'],
                'correct_option' => 'C',
                'explanation' => 'Abomey était le cœur politique et militaire du puissant Royaume du Dahomey.'
            ],
            [
                'question_text' => 'Qui était le dernier roi souverain du Dahomey avant la colonisation ?',
                'options' => ['A' => 'Roi Guezo', 'B' => 'Roi Béhanzin', 'C' => 'Roi Agoli-Agbo', 'D' => 'Roi Toffa'],
                'correct_option' => 'B',
                'explanation' => 'Le Roi Béhanzin est célèbre pour sa résistance héroïque contre les troupes françaises.'
            ],
            [
                'question_text' => 'Quel monument célèbre se trouve à Ouidah pour commémorer la traite négrière ?',
                'options' => ['A' => 'La Porte du Non-Retour', 'B' => 'La Place des Martyrs', 'C' => 'Le Temple des Pythons', 'D' => 'Le Palais Royal'],
                'correct_option' => 'A',
                'explanation' => 'La Porte du Non-Retour est un mémorial poignant situé sur la plage de Ouidah.'
            ],
            [
                'question_text' => 'Quel est le nom des célèbres guerrières du Royaume du Dahomey ?',
                'options' => ['A' => 'Les Agodjié', 'B' => 'Les Dahoméennes', 'C' => 'Les Minon', 'D' => 'Toutes les réponses (A et C)'],
                'correct_option' => 'D',
                'explanation' => 'Les Agodjié (ou Minon, "nos mères") étaient les guerrières d\'élite du roi.'
            ]
        ];

        foreach ($questions1 as $q) {
            Question::updateOrCreate(
                ['quiz_id' => $quiz1->id, 'question_text' => $q['question_text']],
                $q
            );
        }

        // 2. Quiz: Porto-Novo & Patrimoine
        $quiz2 = Quiz::updateOrCreate(
            ['title' => 'Porto-Novo & Patrimoine'],
            [
                'description' => 'Découvrez la ville aux trois noms et son architecture afro-brésilienne unique.',
                'category' => 'Culture',
                'xp_reward' => 250,
                'time_limit' => 30,
            ]
        );

        $questions2 = [
            [
                'question_text' => 'Quel est l\'autre nom historique de Porto-Novo signifiant "la grande maison" ?',
                'options' => ['A' => 'Adjatchè', 'B' => 'Hogbonou', 'C' => 'Gléhué', 'D' => 'Xogbonou'],
                'correct_option' => 'B',
                'explanation' => 'Hogbonou est le nom Goun de la ville, signifiant "la grande maison".'
            ],
            [
                'question_text' => 'Quelle religion est célèbre pour sa Grande Mosquée au style de cathédrale à Porto-Novo ?',
                'options' => ['A' => 'Catholicisme', 'B' => 'Islam', 'C' => 'Protestantisme', 'D' => 'Vodoun'],
                'correct_option' => 'B',
                'explanation' => 'La Grande Mosquée de Porto-Novo a une architecture afro-brésilienne unique rappelant une cathédrale.'
            ]
        ];

        foreach ($questions2 as $q) {
            Question::updateOrCreate(
                ['quiz_id' => $quiz2->id, 'question_text' => $q['question_text']],
                $q
            );
        }
    }
}
