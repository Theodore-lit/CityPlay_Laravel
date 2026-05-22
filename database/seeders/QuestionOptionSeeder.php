<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Enigma;
use App\Models\EnigmaQuestion; // Assure-toi que ce modèle existe
use Illuminate\Support\Facades\DB;

class QuestionOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyage de la table avant insertion pour éviter les doublons
        DB::table('enigma_question_options')->truncate();

        // Mapping complet des questions associées à leurs options de réponse
        $optionsData = [
            'Porte du Non-Retour' => [
                'easy' => [
                    "Quelles émotions ce mémorial cherche-t-il à transmettre aux visiteurs ?" => [
                        ['text' => "Le recueillement et le devoir de mémoire", 'is_correct' => true],
                        ['text' => "La joie et la célébration festive", 'is_correct' => false],
                        ['text' => "L'indifférence face au passé", 'is_correct' => false],
                    ],
                    "En quelle année ce monument historique a-t-il été érigé à Ouidah ?" => [
                        ['text' => "1992", 'is_correct' => true],
                        ['text' => "1960", 'is_correct' => false],
                        ['text' => "2005", 'is_correct' => false],
                    ]
                ],
                'medium' => [
                    "Que symbolise l'espace vide situé exactement entre ces deux piliers ?" => [
                        ['text' => "Le départ vers le grand vide de l'inconnu", 'is_correct' => true],
                        ['text' => "Une simple porte d'entrée pour les touristes", 'is_correct' => false],
                        ['text' => "Une zone d'observation astronomique", 'is_correct' => false],
                    ],
                    "Quelle est la hauteur approximative de ces colonnes ?" => [
                        ['text' => "Environ 15 mètres", 'is_correct' => true],
                        ['text' => "Environ 5 mètres", 'is_correct' => false],
                        ['text' => "Environ 35 mètres", 'is_correct' => false],
                    ]
                ],
                'hard' => [
                    "Pourquoi les captifs marchaient-ils spécifiquement vers l'Ouest (l'océan) ?" => [
                        ['text' => "Car c'est là qu'attendaient les navires négriers", 'is_correct' => true],
                        ['text' => "Pour suivre les routes caravanières terrestres", 'is_correct' => false],
                        ['text' => "Parce que le marché principal s'y trouvait", 'is_correct' => false],
                    ],
                    "Quels pays d'embarquement attendaient ces navires à l'horizon ?" => [
                        ['text' => "Les Amériques et les Caraïbes", 'is_correct' => true],
                        ['text' => "L'Asie et l'Inde", 'is_correct' => false],
                        ['text' => "L'Europe du Nord uniquement", 'is_correct' => false],
                    ]
                ],
                'site' => [
                    "Quelle est la posture du chien représenté aux pieds des captifs ?" => [
                        ['text' => "Il est assis et menaçant, symbolisant la garde", 'is_correct' => true],
                        ['text' => "Il est en train de courir après un gibier", 'is_correct' => false],
                        ['text' => "Il est couché et endormi", 'is_correct' => false],
                    ],
                    "Quels autres symboles de soumission ou de garde sont visibles sur ce bas-relief ?" => [
                        ['text' => "Des chaînes et des gardes armés", 'is_correct' => true],
                        ['text' => "Des couronnes de fleurs", 'is_correct' => false],
                        ['text' => "Des instruments de musique festifs", 'is_correct' => false],
                    ]
                ]
            ],
            'Temple des Pythons' => [
                'easy' => [
                    "Quelle est la variété exacte de python vénérée dans ce temple (ils sont inoffensifs) ?" => [
                        ['text' => "Le Python royal", 'is_correct' => true],
                        ['text' => "Le Python de Seba", 'is_correct' => false],
                        ['text' => "Le Python réticulé", 'is_correct' => false],
                    ],
                    "Tous les combien de jours les pythons sont-ils relâchés dans la nature pour se nourrir ?" => [
                        ['text' => "Tous les 7 jours", 'is_correct' => true],
                        ['text' => "Tous les jours", 'is_correct' => false],
                        ['text' => "Tous les mois", 'is_correct' => false],
                    ]
                ],
                'medium' => [
                    "Quel message de tolérance cette proximité immédiate entre le Temple et la Basilique envoie-t-elle ?" => [
                        ['text' => "Le dialogue et la cohabitation pacifique des religions", 'is_correct' => true],
                        ['text' => "Une rivalité historique non résolue", 'is_correct' => false],
                        ['text' => "La fusion des deux cultes en un seul", 'is_correct' => false],
                    ],
                    "En quelle année la communauté chrétienne s'est-elle installée en face du temple ?" => [
                        ['text' => "Au début du XXe siècle (liaison historique)", 'is_correct' => true],
                        ['text' => "Au XVIIe siècle", 'is_correct' => false],
                        ['text' => "En 2010", 'is_correct' => false],
                    ]
                ],
                'hard' => [
                    "Quel rituel vodoun spécifique est pratiqué au pied de cet Iroko sacré ?" => [
                        ['text' => "Des libations et prières aux divinités de la terre", 'is_correct' => true],
                        ['text' => "Des pièces de théâtre profanes", 'is_correct' => false],
                        ['text' => "Des baptêmes chrétiens", 'is_correct' => false],
                    ],
                    "Pourquoi entoure-t-on parfois la base de cet arbre d'un tissu blanc ?" => [
                        ['text' => "Pour marquer son caractère sacré et divin", 'is_correct' => true],
                        ['text' => "Pour le protéger des insectes", 'is_correct' => false],
                        ['text' => "Pour indiquer qu'il va être coupé", 'is_correct' => false],
                    ]
                ],
                'site' => [
                    "Quelle est la matière du muret sur lequel reposent ces douze pythons ?" => [
                        ['text' => "En terre battue / argile traditionnelle", 'is_correct' => true],
                        ['text' => "En marbre blanc importé", 'is_correct' => false],
                        ['text' => "En briques de verre modernes", 'is_correct' => false],
                    ],
                    "Y a-t-il des offrandes visibles déposées à proximité de ce muret ?" => [
                        ['text' => "Oui, du kpakpa (pâte), de l'huile ou de l'argent", 'is_correct' => true],
                        ['text' => "Non, l'endroit doit rester totalement vide", 'is_correct' => false],
                        ['text' => "Uniquement des bougies parfumées occidentales", 'is_correct' => false],
                    ]
                ]
            ],
            'Place de l\'Amazone' => [
                'easy' => [
                    "Qui est la figure historique réelle qui a inspiré cette statue géante ?" => [
                        ['text' => "La reine Tassi Hangbé", 'is_correct' => true],
                        ['text' => "La reine Pokou", 'is_correct' => false],
                        ['text' => "La reine Yennenga", 'is_correct' => false],
                    ],
                    "Quel est le nom traditionnel donné à ces femmes guerrières en langue Fon (Minon) ?" => [
                        ['text' => "Agodojié ou Minon", 'is_correct' => true],
                        ['text' => "Ashanti", 'is_correct' => false],
                        ['text' => "Vodounsi", 'is_correct' => false],
                    ]
                ],
                'medium' => [
                    "De quel type de fusil s'agit-il (historiquement fourni lors du commerce) ?" => [
                        ['text' => "Un fusil à silex ou rutilant ancien", 'is_correct' => true],
                        ['text' => "Un fusil d'assaut automatique moderne", 'is_correct' => false],
                        ['text' => "Une carabine laser de science-fiction", 'is_correct' => false],
                    ],
                    "Quelle posture générale la guerrière adopte-t-elle avec son arme ?" => [
                        ['text' => "Elle la tient fermement, droite et fière, tournée vers l'avant", 'is_correct' => true],
                        ['text' => "Elle la pointe directement vers le sol", 'is_correct' => false],
                        ['text' => "Elle la porte sur son dos en marchant", 'is_correct' => false],
                    ]
                ],
                'hard' => [
                    "Quel roi du Dahomey a officiellement structuré l'armée des Amazones ?" => [
                        ['text' => "Le Roi Agaja (ou le Roi Guézo pour son apogée)", 'is_correct' => true],
                        ['text' => "Le Roi Béhanzin", 'is_correct' => false],
                        ['text' => "Le Roi Houegbadja", 'is_correct' => false],
                    ],
                    "Citez une bataille célèbre où les Amazones ont démontré leur bravoure héroïque." => [
                        ['text' => "La bataille d'Atchoupa (contre les troupes françaises)", 'is_correct' => true],
                        ['text' => "La bataille de Waterloo", 'is_correct' => false],
                        ['text' => "La bataille d'Isandhlwana", 'is_correct' => false],
                    ]
                ],
                'site' => [
                    "Quelle est la couleur du granit choisi pour le revêtement de ce socle ?" => [
                        ['text' => "Un gris anthracite sombre et poli", 'is_correct' => true],
                        ['text' => "Un rose ocre vif", 'is_correct' => false],
                        ['text' => "Un vert émeraude pailleté", 'is_correct' => false],
                    ],
                    "Quels motifs ou gravures discrètes peut-on observer sur les flancs du socle ?" => [
                        ['text' => "Des inscriptions historiques textuelles", 'is_correct' => true],
                        ['text' => "Des sculptures d'animaux de la savane", 'is_correct' => false],
                        ['text' => "Des symboles de notes de musique", 'is_correct' => false],
                    ]
                ]
            ],
            'Grande Mosquée de Porto-Novo' => [
                'easy' => [
                    "De quel monument de Salvador de Bahia cette mosquée est-elle la réplique presque conforme ?" => [
                        ['text' => "L'église Notre-Dame du Rosaire des Noirs", 'is_correct' => true],
                        ['text' => "La cathédrale de Brasilia", 'is_correct' => false],
                        ['text' => "Le Christ Rédempteur", 'is_correct' => false],
                    ],
                    "Pourquoi les anciens esclaves ont-ils choisi ce style plutôt qu'un style afro-islamique traditionnel ?" => [
                        ['text' => "Parce qu'ils maîtrisaient l'architecture baroque apprise au Brésil", 'is_correct' => true],
                        ['text' => "Parce que les plans leur ont été offerts par le roi du Portugal", 'is_correct' => false],
                        ['text' => "C'était une obligation imposée par le gouvernement local", 'is_correct' => false],
                    ]
                ],
                'medium' => [
                    "Quelles sont les couleurs secondaires qui accompagnent le jaune sur les moulures ?" => [
                        ['text' => "Le marron/rouge brique et le blanc", 'is_correct' => true],
                        ['text' => "Le bleu ciel et le violet fuchsia", 'is_correct' => false],
                        ['text' => "Le vert sapin et l'argenté", 'is_correct' => false],
                    ],
                    "À quelle fréquence la peinture de cette façade baroque est-elle rafraîchie ?" => [
                        ['text' => "Régulièrement lors des grandes fêtes ou restaurations", 'is_correct' => true],
                        ['text' => "Elle n'a jamais été repeinte depuis sa création", 'is_correct' => false],
                        ['text' => "Tous les 50 ans précisément", 'is_correct' => false],
                    ]
                ],
                'hard' => [
                    "Quels sont les noms de famille Agouda emblématiques encore présents à Porto-Novo ?" => [
                        ['text' => "De Souza, da Silva, Almeida", 'is_correct' => true],
                        ['text' => "Koffi, Mensah, Sow", 'is_correct' => false],
                        ['text' => "Traoré, Diallo, Cissé", 'is_correct' => false],
                    ],
                    "En quelle année les travaux de construction de cette mosquée unique ont-ils débuté ?" => [
                        ['text' => "En 1912 (inaugurée vers 1935)", 'is_correct' => true],
                        ['text' => "En 1650", 'is_correct' => false],
                        ['text' => "En 1985", 'is_correct' => false],
                    ]
                ],
                'site' => [
                    "Quelle est la forme géométrique des arches de ces sept fenêtres ?" => [
                        ['text' => "Des arches en ogive de style baroque/gothique", 'is_correct' => true],
                        ['text' => "Des rectangles parfaits sans aucune courbe", 'is_correct' => false],
                        ['text' => "Des hublots parfaitement circulaires", 'is_correct' => false],
                    ],
                    "Des détails en fer forgé sont-ils visibles sur les garde-corps de ces fenêtres ?" => [
                        ['text' => "Oui, typiques de l'artisanat d'influence afro-brésilienne", 'is_correct' => true],
                        ['text' => "Non, il s'agit de balustrades en plastique moderne", 'is_correct' => false],
                        ['text' => "Ce sont de simples planches de bois brut", 'is_correct' => false],
                    ]
                ]
            ],
        ];

        // Récupération de tous les lieux pour parcourir l'arborescence
        $locations = Location::all();

        foreach ($locations as $location) {
            // Si le lieu n'est pas dans notre dictionnaire d'options, on l'ignore
            if (!isset($optionsData[$location->name])) {
                continue;
            }

            foreach ($optionsData[$location->name] as $difficulty => $questions) {
                $isSiteSpecific = ($difficulty === 'site');

                // 1. Trouver l'énigme parente
                $enigmaQuery = Enigma::where('location_id', $location->id)
                    ->where('is_site_specific', $isSiteSpecific);

                if (!$isSiteSpecific) {
                    $enigmaQuery->where('difficulty', $difficulty);
                }

                $enigma = $enigmaQuery->first();

                if ($enigma) {
                    // 2. Parcourir les questions textuelles définies pour cette énigme
                    foreach ($questions as $questionText => $options) {
                        
                        // Trouver la ligne correspondante dans la table `enigma_questions`
                        // On utilise DB ou le modèle si disponible : EnigmaQuestion::where(...)
                        $questionRow = DB::table('enigma_questions')
                            ->where('enigma_id', $enigma->id)
                            ->where('question_text', $questionText)
                            ->first();

                        if ($questionRow) {
                            // 3. Insérer les options pour cette question précise
                            foreach ($options as $option) {
                                DB::table('enigma_question_options')->insert([
                                    'enigma_question_id' => $questionRow->id,
                                    'option_text'        => $option['text'],
                                    'is_correct'         => $option['is_correct'],
                                    'created_at'         => now(),
                                    'updated_at'         => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}