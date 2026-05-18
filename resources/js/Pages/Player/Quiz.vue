<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Brain, Clock, Zap, ArrowRight, CheckCircle2, XCircle, Info, Trophy } from 'lucide-vue-next';

const props = defineProps({
    quiz: Object
});

const currentQuestionIndex = ref(0);
const answers = ref([]);
const timer = ref(null);
const hearts = ref(5);
const xpGained = ref(0);
const usedHintsCount = ref(0);
const showHint = ref(false);
const feedback = ref(null); // 'correct' or 'incorrect'
const isSubmitting = ref(false);

// Simplification et robustesse de la rÃ©cupÃ©ration des questions
const questions = computed(() => {
    const q = props.quiz?.questions;
    if (!q) return [];
    return Array.isArray(q) ? q : Object.values(q);
});

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || null);
const totalQuestions = computed(() => questions.value.length);
const progress = computed(() => {
    if (totalQuestions.value === 0) return 0;
    return ((currentQuestionIndex.value + 1) / totalQuestions.value) * 100;
});

// Initialisation sÃ©curisÃ©e du temps
const timeLeft = ref(60);

const selectOption = (option) => {
    // Bloquer si déjà soumis, si pas de question, ou si une réponse a DÉJÀ été choisie pour cette question
    if (isSubmitting.value || !currentQuestion.value || answers.value[currentQuestionIndex.value]) return;
    
    // Mise à jour de la réponse (premier et unique clic autorisé)
    const newAnswers = [...answers.value];
    newAnswers[currentQuestionIndex.value] = option;
    answers.value = newAnswers;

    if (option === currentQuestion.value.correct_option) {
        xpGained.value += 100;
        feedback.value = 'correct';
    } else {
        feedback.value = 'incorrect';
        if (hearts.value > 0) hearts.value--;
    }

    if (hearts.value <= 0) {
        setTimeout(() => {
            submitResults();
        }, 1500);
    }
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < totalQuestions.value - 1) {
        currentQuestionIndex.value++;
        showHint.value = false;
        feedback.value = null;
    }
};

const submitResults = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    clearInterval(timer.value);
    
    router.post(route('player.quiz.submit', props.quiz.id), {
        answers: answers.value,
        hints_used: usedHintsCount.value,
        time_left: timeLeft.value,
        failed: hearts.value <= 0,
    }, {
        onError: () => {
            alert("Une erreur est survenue.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const useHint = () => {
    if (!showHint.value && currentQuestion.value?.hint) {
        showHint.value = true;
        usedHintsCount.value++;
    }
};

const startTimer = () => {
    timer.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            submitResults();
        }
    }, 1000);
};

onMounted(() => {
    timeLeft.value = props.quiz?.time_limit || 60;
    startTimer();
});

onUnmounted(() => {
    clearInterval(timer.value);
});
</script>

<template>
    <Head :title="`Quiz: ${quiz?.title || 'Chargement...'}`" />

    <SiteLayout hideFooter>
        <div class="mx-auto max-w-3xl px-4 py-12">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8" v-if="quiz">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl bg-gradient-electric grid place-items-center shadow-neon">
                        <Brain class="h-6 w-6 text-electric-foreground" />
                    </div>
                    <div>
                        <h1 class="font-display text-xl text-white">{{ quiz.title }}</h1>
                        <p class="text-xs text-muted-foreground uppercase tracking-widest">{{ quiz.category }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="glass px-4 py-2 rounded-xl flex items-center gap-2" :class="timeLeft < 10 ? 'text-destructive border-destructive/50 animate-pulse' : 'text-electric'">
                        <Clock class="h-4 w-4" />
                        <span class="font-display font-bold">{{ timeLeft }}s</span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-10">
                <div class="flex justify-between text-xs mb-2 text-muted-foreground uppercase tracking-widest font-bold">
                    <span v-if="totalQuestions > 0">Question {{ currentQuestionIndex + 1 }} sur {{ totalQuestions }}</span>
                    <div class="flex items-center gap-2 text-electric animate-pulse" v-if="xpGained > 0">
                        <Zap class="h-3 w-3 fill-current" />
                        <span>+{{ xpGained }} XP</span>
                    </div>
                    <span>{{ Math.round(progress) }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gaming-darker overflow-hidden border border-white/5">
                    <div class="h-full bg-gradient-electric transition-all duration-500" :style="{ width: `${progress}%` }" />
                </div>
            </div>

            <!-- No Quiz/Questions Error State -->
            <div v-if="totalQuestions === 0" class="glass-strong rounded-3xl p-12 text-center border border-destructive/20">
                <Info class="h-12 w-12 text-destructive mx-auto mb-4" />
                <h2 class="text-xl font-display text-white mb-2">Aucun contenu disponible</h2>
                <p class="text-muted-foreground mb-8">DÃ©solÃ©, ce quiz ne contient aucune question pour le moment.</p>
                <Link :href="route('player.cities')">
                    <NeonButton>Retour aux villes</NeonButton>
                </Link>
            </div>

            <!-- Question Card -->
            <div v-else class="glass-strong rounded-3xl p-8 md:p-10 border border-electric/20 relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-xl md:text-2xl font-display text-white relative z-10 flex-1 pr-4">
                        {{ currentQuestion?.question_text || 'Chargement...' }}
                    </h2>
                    <button 
                        v-if="!showHint && currentQuestion?.hint"
                        @click="useHint"
                        class="h-10 px-4 rounded-xl glass border-warning/40 text-warning text-[10px] font-black uppercase tracking-widest hover:bg-warning/10 transition-all shrink-0"
                    >
                        Indice
                    </button>
                </div>

                <div v-if="showHint" class="mb-8 p-4 rounded-2xl bg-warning/5 border border-warning/20 text-warning text-xs italic animate-fade-down">
                    ðŸ’¡ <span class="text-white">{{ currentQuestion?.hint }}</span>
                </div>

                <div class="grid gap-4 relative z-10 mb-10">
                    <div v-if="currentQuestion && (!currentQuestion.options || (typeof currentQuestion.options === 'object' && Object.keys(currentQuestion.options).length === 0))" class="text-white text-center p-8 border border-dashed border-white/10 rounded-2xl">
                        Aucune option de rÃ©ponse n'a Ã©tÃ© configurÃ©e pour cette question.
                    </div>
                    <button 
                        v-for="(text, key) in currentQuestion?.options" 
                        :key="key"
                        @click="selectOption(key)"
                        :disabled="!!answers[currentQuestionIndex]"
                        class="w-full p-5 rounded-2xl border transition-all duration-300 text-left flex items-center gap-4 group"
                        :class="[
                            answers[currentQuestionIndex] === key 
                                ? (key === currentQuestion.correct_option 
                                    ? 'bg-success/20 border-success shadow-[0_0_20px_rgba(34,197,94,0.2)] text-white' 
                                    : 'bg-destructive/20 border-destructive shadow-[0_0_20px_rgba(239,68,68,0.2)] text-white')
                                : (answers[currentQuestionIndex] 
                                    ? 'bg-white/5 border-white/10 opacity-50 cursor-not-allowed' 
                                    : 'bg-white/5 border-white/10 text-muted-foreground hover:bg-white/10 hover:border-white/20')
                        ]"
                    >
                        <div class="h-8 w-8 rounded-lg border border-current grid place-items-center font-display font-bold text-sm shrink-0 transition-colors"
                             :class="[
                                 answers[currentQuestionIndex] === key 
                                    ? (key === currentQuestion.correct_option ? 'bg-success text-white' : 'bg-destructive text-white')
                                    : ''
                             ]">
                            {{ key }}
                        </div>
                        <span class="font-medium text-white">{{ text }}</span>
                        
                        <CheckCircle2 v-if="answers[currentQuestionIndex] === key && key === currentQuestion.correct_option" class="h-5 w-5 text-success ml-auto" />
                        <XCircle v-if="answers[currentQuestionIndex] === key && key !== currentQuestion.correct_option" class="h-5 w-5 text-destructive ml-auto" />
                    </button>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-white/5 relative z-10">
                    <button 
                        v-if="currentQuestionIndex < totalQuestions - 1"
                        @click="nextQuestion"
                        :disabled="!answers[currentQuestionIndex]"
                        class="px-8 py-3 rounded-xl bg-electric text-white font-bold hover:shadow-neon disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                    >
                        Suivant <ArrowRight class="h-4 w-4" />
                    </button>

                    <button 
                        v-else
                        @click="submitResults"
                        :disabled="!answers[currentQuestionIndex] || isSubmitting"
                        class="px-8 py-3 rounded-xl bg-success text-white font-bold shadow-[0_0_15px_rgba(34,197,94,0.3)] hover:shadow-[0_0_25px_rgba(34,197,94,0.5)] disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                    >
                        <Trophy class="h-4 w-4" /> Terminer le Quiz
                    </button>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>
