<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';
import {
    Brain, Plus, Trash2, Edit2, ChevronLeft, HelpCircle,
    Clock, Zap, Save, X, CheckCircle2
} from 'lucide-vue-next';

import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    city: Object,
});

const page = usePage();
const backRoute = computed(() => {
    return route('mairie.city.hub', props.city.id);
});



// Quiz Form
const showQuizModal = ref(false);
const editingQuiz = ref(null);
const quizForm = useForm({
    id: null,
    title: '',
    description: '',
    category: 'Histoire',
    difficulty: 'essy',
    xp_reward: 250,
    time_limit: 60,
});

// Question Form
const showQuestionModal = ref(false);
const selectedQuiz = ref(null);
const editingQuestion = ref(null);
const questionForm = useForm({
    id: null,
    question_text: '',
    options: { A: '', B: '', C: '', D: '' },
    correct_option: 'A',
    hint: '',
    explanation: '',
});

const openQuizModal = (quiz = null) => {
    editingQuiz.value = quiz;
    if (quiz) {
        quizForm.id = quiz.id;
        quizForm.difficulty = quiz.difficulty;
        quizForm.title = quiz.title;
        quizForm.description = quiz.description;
        quizForm.category = quiz.category;
        quizForm.xp_reward = quiz.xp_reward;
        quizForm.time_limit = quiz.time_limit;
    } else {
        quizForm.reset();
        quizForm.id = null;
    }
    showQuizModal.value = true;
};

const submitQuiz = () => {
    quizForm.post(route('admin.quizzes.store', props.city.id), {
        onSuccess: () => {
            showQuizModal.value = false;
            quizForm.reset();
        }
    });
};

// const deleteQuiz = (id) => {
//     if (confirm('Supprimer ce quiz et toutes ses questions ?')) {
//         useForm({}).delete(route('admin.quizzes.delete', id));
//     }
// };

//Modal de confirmation 
const showDeleteConfirm = ref(false);
const quizToDelete = ref(null);

const prepareDelete = (id) => {
    quizToDelete.value = id;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    router.delete(route('admin.quizzes.delete', quizToDelete.value), {
        onSuccess: () => { showDeleteConfirm.value = false; }
    });
};

const openQuestionModal = (quiz, question = null) => {
    selectedQuiz.value = quiz;
    editingQuestion.value = question;
    if (question) {
        questionForm.id = question.id;
        questionForm.question_text = question.question_text;
        questionForm.options = { ...question.options };
        questionForm.correct_option = question.correct_option;
        questionForm.hint = question.hint;
        questionForm.explanation = question.explanation;
    } else {
        questionForm.reset();
        questionForm.id = null;
    }
    showQuestionModal.value = true;
};

const submitQuestion = () => {
    questionForm.post(route('admin.questions.store', selectedQuiz.value.id), {
        onSuccess: () => {
            showQuestionModal.value = false;
            questionForm.reset();
        }
    });
};

const showDeleteQuestionConfirm=ref(false);
const questionToDelete=ref(null);

const prepareDeleteQuestion = (id) => {
    // if (confirm('Supprimer cette question ?')) {
    //     useForm({}).delete(route('admin.questions.delete', id));
    // }
    questionToDelete.value = id;
    showDeleteQuestionConfirm.value = true; 
};

const executeDeleteQuestion = () => {
    router.delete(route('admin.questions.delete', questionToDelete.value), {
        onSuccess: () => { showDeleteQuestionConfirm.value = false; }
    });
};
</script>

<template>

    <Head :title="`Quiz HQ : ${city.name}`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div class="flex items-center gap-5">
                    <Link :href="backRoute"
                        class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20">
                        <ChevronLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div class="text-xs text-purple-neon uppercase tracking-[0.2em] font-black mb-1">Éditeur de
                            Défis</div>
                        <h1 class="font-display text-3xl md:text-5xl text-foreground">{{ city.name }}</h1>
                    </div>
                </div>
                <NeonButton @click="openQuizModal()" size="sm" variant="purple">
                    <Plus class="h-4 w-4 mr-2" />Nouveau Quiz
                </NeonButton>
            </div>

            <div class="flex gap-2">
                <button @click="openQuestionModal(quiz)" class="h-11 px-5 rounded-xl bg-electric/10 text-electric border border-electric/30 hover:bg-electric/20 text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                    <Plus class="h-4 w-4" /> Question
                </button>
                <button @click="openQuizModal(quiz)" class="h-11 w-11 rounded-xl glass border-white/10 text-muted-foreground hover:text-white transition-all grid place-items-center">
                    <Edit2 class="h-4 w-4" />
                </button>
                <button @click="prepareDelete(quiz.id)" class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive hover:bg-destructive hover:text-white transition-all grid place-items-center">
                    <Trash2 class="h-4 w-4" />
                </button>
            </div>
          </div>


            <!-- Quizzes List -->
            <div class="space-y-8">
                <div v-for="quiz in city.quizzes" :key="quiz.id"
                    class="glass-strong rounded-[2.5rem] border border-white/5 overflow-hidden">
                    <div
                        class="p-8 border-b border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white/5">
                        <div class="flex items-start gap-5">
                            <div
                                class="h-14 w-14 rounded-2xl bg-purple-neon/10 border border-purple-neon/20 grid place-items-center text-purple-neon shrink-0">
                                <Brain class="h-7 w-7" />
                            </div>
                            <div>
                                <h2 class="font-display text-2xl text-foreground">{{ quiz.title }}</h2>
                                <div class="flex flex-wrap gap-4 mt-2">
                                    <span
                                        class="text-[10px] px-2 py-1 rounded bg-white/5 text-muted-foreground uppercase font-bold tracking-tighter">{{
                                        quiz.category }}</span>
                                    <span class="text-[10px] text-electric font-mono flex items-center gap-1.5">
                                        <Zap class="h-3 w-3" />+{{ quiz.xp_reward }} XP
                                    </span>
                                    <span class="text-[10px] text-cyan-neon font-mono flex items-center gap-1.5">
                                        <Clock class="h-3 w-3" />{{ quiz.time_limit }}s
                                    </span>
                                    <span class="text-[10px] text-muted-foreground uppercase font-black">{{
                                        quiz.questions?.length || 0 }} questions</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openQuestionModal(quiz)"
                                class="h-11 px-5 rounded-xl bg-electric/10 text-electric border border-electric/30 hover:bg-electric/20 text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                                <Plus class="h-4 w-4" /> Question
                            </button>
                            <button @click="openQuizModal(quiz)"
                                class="h-11 w-11 rounded-xl glass border-white/10 text-muted-foreground hover:text-white transition-all grid place-items-center">
                                <Edit2 class="h-4 w-4" />
                            </button>
                            <button @click="deleteQuiz(quiz.id)"
                                class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive hover:bg-destructive hover:text-white transition-all grid place-items-center">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                  </td>
                  <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button @click="openQuestionModal(quiz, q)" class="h-8 w-8 rounded-lg glass border-white/5 text-muted-foreground hover:text-electric transition-all grid place-items-center">
                            <Edit2 class="h-3.5 w-3.5" />
                        </button>
                        <button @click="prepareDeleteQuestion(q.id)" class="h-8 w-8 rounded-lg glass border-destructive/10 text-destructive/60 hover:text-destructive transition-all grid place-items-center">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>


                    <!-- Questions Table -->
                    <div v-if="quiz.questions?.length" class="overflow-x-auto p-4">
                        <table class="w-full text-sm">
                            <thead
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground bg-gaming-darker/50">
                                <tr class="border-b border-white/5">
                                    <th class="text-left p-4">Question</th>
                                    <th class="text-left p-4">Réponse</th>
                                    <th class="text-right p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="q in quiz.questions" :key="q.id"
                                    class="border-b border-white/5 hover:bg-white/5 transition-all">
                                    <td class="p-4">
                                        <div class="text-foreground font-medium max-w-md">{{ q.question_text }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-6 w-6 rounded bg-success/20 border border-success/40 grid place-items-center text-success text-[10px] font-bold">
                                                {{ q.correct_option }}</div>
                                            <div class="text-xs text-muted-foreground truncate max-w-[200px]">{{
                                                q.options[q.correct_option] }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openQuestionModal(quiz, q)"
                                                class="h-8 w-8 rounded-lg glass border-white/5 text-muted-foreground hover:text-electric transition-all grid place-items-center">
                                                <Edit2 class="h-3.5 w-3.5" />
                                            </button>
                                            <button @click="deleteQuestion(q.id)"
                                                class="h-8 w-8 rounded-lg glass border-destructive/10 text-destructive/60 hover:text-destructive transition-all grid place-items-center">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div v-else class="p-12 text-center text-muted-foreground italic text-sm">
                        Aucune question déployée pour ce quiz.
                    </div>
                </div>

                <div v-if="!city.quizzes?.length"
                    class="p-20 text-center glass-strong rounded-[3rem] border border-white/5">
                    <Brain class="h-16 w-16 mx-auto mb-6 text-muted-foreground/20" />
                    <h3 class="font-display text-2xl text-foreground">Zone de Défis Vierge</h3>
                    <p class="text-muted-foreground mt-2 max-w-sm mx-auto">Commencez par créer un quiz pour tester les
                        connaissances des explorateurs dans cette ville.</p>
                </div>
            </div>
        </div>

        <!-- QUIZ MODAL -->
        <Modal :show="showQuizModal" @close="showQuizModal = false">
            <div class="p-8 bg-gaming-darker border border-purple-neon/20 rounded-[2.5rem] overflow-hidden relative">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                <h2 class="font-display text-2xl text-foreground mb-8 relative z-10">{{ editingQuiz ? 'Modifier le Quiz'
                    : 'Nouveau Quiz Tactique' }}</h2>


    
<!-- test -->
    <ConfirmModal 
    :show="showDeleteConfirm"
    title="Supprimer ce quiz ?"
    message="Cette action est irréversible et supprimera toutes les questions associées."
    variant="danger"
    confirmText="Supprimer définitivement"
    @close="showDeleteConfirm = false"
    @confirm="executeDelete"
   
/>

<ConfirmModal 
    :show="showDeleteQuestionConfirm"
    title="Supprimer cette question ?"
    message="Voulez-vous vraiment retirer cette question du quiz ?"
    variant="danger"
    confirmText="Supprimer"
    @close="showDeleteQuestionConfirm = false"
    @confirm="executeDeleteQuestion"
/>

    <!-- QUIZ MODAL -->
    <Modal :show="showQuizModal" @close="showQuizModal = false">
        <div class="p-8 bg-gaming-darker border border-purple-neon/20 rounded-[2.5rem] overflow-hidden relative">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
            <h2 class="font-display text-2xl text-foreground mb-8 relative z-10">{{ editingQuiz ? 'Modifier le Quiz' : 'Nouveau Quiz Tactique' }}</h2>

            <form @submit.prevent="submitQuiz" class="space-y-6 relative z-10">
                <GlowInput label="Titre du Quiz" v-model="quizForm.title" placeholder="Ex: Les Secrets de Cotonou" required />
                <div class="grid grid-cols-2 gap-6">

                <form @submit.prevent="submitQuiz" class="space-y-6 relative z-10">
                    <GlowInput label="Titre du Quiz" v-model="quizForm.title" placeholder="Ex: Les Secrets de Cotonou"
                        required />
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Catégorie</label>
                            <select v-model="quizForm.category"
                                class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-foreground focus:border-purple-neon outline-none">
                                <option value="Histoire">Histoire</option>
                                <option value="Culture">Culture</option>
                                <option value="Géographie">Géographie</option>
                                <option value="Gastronomie">Gastronomie</option>
                                <option value="Tradition">Tradition</option>
                            </select>
                        </div>
                        <GlowInput label="Temps Limite (sec)" type="number" v-model="quizForm.time_limit" required />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <GlowInput label="Récompense XP" type="number" v-model="quizForm.xp_reward" required />
                        <div class="space-y-2">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Difficulté</label>
                            <select v-model="quizForm.difficulty"
                                class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-foreground focus:border-purple-neon outline-none">
                                <option value="easy">🟢 Facile</option>
                                <option value="medium">🟡 Moyen</option>
                                <option value="hard">🔴 Difficile</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Description</label>
                        <textarea v-model="quizForm.description"
                            class="w-full h-24 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-foreground placeholder:text-muted-foreground/40 focus:border-purple-neon outline-none resize-none"
                            placeholder="Briefing du défi..."></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <NeonButton type="button" variant="outline" class="flex-1" @click="showQuizModal = false">
                            Annuler</NeonButton>
                        <NeonButton type="submit" class="flex-1" :disabled="quizForm.processing" variant="purple">
                            Déployer Quiz</NeonButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- QUESTION MODAL -->
        <Modal :show="showQuestionModal" @close="showQuestionModal = false">
            <div
                class="p-8 bg-gaming-darker border border-electric/20 rounded-[2.5rem] overflow-hidden relative max-h-[90vh] overflow-y-auto">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                <h2 class="font-display text-2xl text-foreground mb-8 relative z-10">{{ editingQuestion ? 'Modifier la Question' : 'Nouvelle Question' }}</h2>

                <form @submit.prevent="submitQuestion" class="space-y-6 relative z-10">
                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Énoncé
                            de la Question</label>
                        <textarea v-model="questionForm.question_text"
                            class="w-full h-24 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-foreground focus:border-electric outline-none resize-none"
                            placeholder="Saisissez la question..." required></textarea>
                    </div>

                    <div class="grid gap-4">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Options
                            de Réponse</label>
                        <div v-for="opt in ['A', 'B', 'C', 'D']" :key="opt" class="flex gap-3">
                            <button type="button" @click="questionForm.correct_option = opt" :class="cn(
                                'h-12 w-12 rounded-xl border font-display font-bold transition-all',
                                questionForm.correct_option === opt ? 'bg-success border-success text-black' : 'bg-white/5 border-white/10 text-foreground hover:border-white/20'
                            )">
                                {{ opt }}
                            </button>
                            <input v-model="questionForm.options[opt]"
                                class="flex-1 h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-foreground focus:border-electric outline-none"
                                :placeholder="`Réponse ${opt}...`" required />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <GlowInput label="Indice Tactique" v-model="questionForm.hint"
                            placeholder="Un petit coup de pouce..." />
                        <GlowInput label="Explication (Post-résolution)" v-model="questionForm.explanation"
                            placeholder="Pour la culture..." />
                    </div>

                    <div class="pt-4 flex gap-3">
                        <NeonButton type="button" variant="outline" class="flex-1" @click="showQuestionModal = false">
                            Annuler</NeonButton>
                        <NeonButton type="submit" class="flex-1" :disabled="questionForm.processing">Enregistrer
                            Question</NeonButton>
                    </div>
                </form>
            </div>
        </Modal>

        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1.25rem;
}
</style>
