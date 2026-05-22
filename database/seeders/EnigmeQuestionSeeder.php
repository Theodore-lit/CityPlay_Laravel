<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Enigma;
use Illuminate\Support\Facades\DB;

class EnigmeQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On nettoie la table pour éviter les doublons en cas de "db:seed" multiple
        DB::table('enigma_questions')->truncate();

        // Le même mapping pour cibler précisément les bonnes énigmes
        $questionsData = [
            'Porte du Non-Retour' => [
                'easy' => [
                    "Quelles émotions ce mémorial cherche-t-il à transmettre aux visiteurs ?",
                    "En quelle année ce monument historique a-t-il été érigé à Ouidah ?"
                ],
                'medium' => [
                    "Que symbolise l'espace vide situé exactement entre ces deux piliers ?",
                    "Quelle est la hauteur approximative de ces colonnes ?"
                ],
                'hard' => [
                    "Pourquoi les captifs marchaient-ils spécifiquement vers l'Ouest (l'océan) ?",
                    "Quels pays d'embarquement attendaient ces navires à l'horizon ?"
                ],
                'site' => [
                    "Quelle est la posture du chien représenté aux pieds des captifs ?",
                    "Quels autres symboles de soumission ou de garde sont visibles sur ce bas-relief ?"
                ]
            ],
            'Temple des Pythons' => [
                'easy' => [
                    "Quelle est la variété exacte de python vénérée dans ce temple (ils sont inoffensifs) ?",
                    "Tous les combien de jours les pythons sont-ils relâchés dans la nature pour se nourrir ?"
                ],
                'medium' => [
                    "Quel message de tolérance cette proximité immédiate entre le Temple et la Basilique envoie-t-elle ?",
                    "En quelle année la communauté chrétienne s'est-elle installée en face du temple ?"
                ],
                'hard' => [
                    "Quel rituel vodoun spécifique est pratiqué au pied de cet Iroko sacré ?",
                    "Pourquoi entoure-t-on parfois la base de cet arbre d'un tissu blanc ?"
                ],
                'site' => [
                    "Quelle est la matière du muret sur lequel reposent ces douze pythons ?",
                    "Y a-t-il des offrandes visibles déposées à proximité de ce muret ?"
                ]
            ],
            'Place de l\'Amazone' => [
                'easy' => [
                    "Qui est la figure historique réelle qui a inspiré cette statue géante ?",
                    "Quel est le nom traditionnel donné à ces femmes guerrières en langue Fon (Minon) ?"
                ],
                'medium' => [
                    "De quel type de fusil s'agit-il (historiquement fourni lors du commerce) ?",
                    "Quelle posture générale la guerrière adopte-t-elle avec son arme ?"
                ],
                'hard' => [
                    "Quel roi du Dahomey a officiellement structuré l'armée des Amazones ?",
                    "Citez une bataille célèbre où les Amazones ont démontré leur bravoure héroïque."
                ],
                'site' => [
                    "Quelle est la couleur du granit choisi pour le revêtement de ce socle ?",
                    "Quels motifs ou gravures discrètes peut-on observer sur les flancs du socle ?"
                ]
            ],
            'Grande Mosquée de Porto-Novo' => [
                'easy' => [
                    "De quel monument de Salvador de Bahia cette mosquée est-elle la réplique presque conforme ?",
                    "Pourquoi les anciens esclaves ont-ils choisi ce style plutôt qu'un style afro-islamique traditionnel ?"
                ],
                'medium' => [
                    "Quelles sont les couleurs secondaires qui accompagnent le jaune sur les moulures ?",
                    "À quelle fréquence la peinture de cette façade baroque est-elle rafraîchie ?"
                ],
                'hard' => [
                    "Quels sont les noms de famille Agouda emblématiques encore présents à Porto-Novo ?",
                    "En quelle année les travaux de construction de cette mosquée unique ont-ils débuté ?"
                ],
                'site' => [
                    "Quelle est la forme géométrique des arches de ces sept fenêtres ?",
                    "Des détails en fer forgé sont-ils visibles sur les garde-corps de ces fenêtres ?"
                ]
            ],
        ];

        // On boucle sur les lieux existants en BDD
        $locations = Location::all();

        foreach ($locations as $location) {
            // On vérifie si on a des questions spécifiques pour ce lieu, sinon on passe
            if (!isset($questionsData[$location->name])) {
                continue;
            }

            foreach ($questionsData[$location->name] as $difficulty => $questions) {
                // Étape clé : retrouver l'ID de l'énigme correspondante en BDD
                $isSiteSpecific = ($difficulty === 'site');

                $enigmaQuery = Enigma::where('location_id', $location->id)
                    ->where('is_site_specific', $isSiteSpecific);

                // Si ce n'est pas l'énigme "site", on filtre aussi par difficulté
                if (!$isSiteSpecific) {
                    $enigmaQuery->where('difficulty', $difficulty);
                }

                $enigma = $enigmaQuery->first();

                // Si l'énigme existe, on insère ses questions
                if ($enigma) {
                    foreach ($questions as $questionText) {
                        DB::table('enigma_questions')->insert([
                            'enigma_id' => $enigma->id,
                            'question_text' => $questionText,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}