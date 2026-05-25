<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Brain, Clock, Zap, ArrowRight, CheckCircle2, XCircle, Info, Trophy, Frown, Lightbulb } from 'lucide-vue-next';
import { Pause, Play } from 'lucide-vue-next';

const props = defineProps({
    quiz: Object
});

const page = usePage();

// ==========================================
// 1. CONFIGURATION INITIALE DU LOCALSTORAGE
// ==========================================
// Déclarées ici au scope global du script pour que TOUT LE MONDE puisse y accéder
const storageKey = `cityplay_progress_${props.quiz?.id || 'game'}`;
const savedData = JSON.parse(localStorage.getItem(storageKey)) || null;

// ==========================================
// 2. DÉCLARATION DES REFS DE BASE (Hydratées par le storage si dispo)
// ==========================================
const currentQuestionIndex = ref(savedData?.currentQuestionIndex || 0);
const answers = ref(savedData?.answers || []);
const timeLeft = ref(savedData?.timeLeft !== undefined ? savedData.timeLeft : 30);
const isPaused = ref(savedData?.isPaused || false);

// Autres états volatils de l'interface
const isFinished = ref(false);
const timer = ref(null);
const hearts = ref(5);
const xpGained = ref(0);
const usedHintsCount = ref(0);
const showHint = ref(false);
const feedback = ref(null); // 'correct' or 'incorrect'
const isSubmitting = ref(false);
const isTimeOut = ref(false);
const showTimeoutModal = ref(false);
const showHintModal = ref(false);

// ==========================================
// 3. HOOKS DE CYCLE DE VIE
// ==========================================
onMounted(() => {
    // Si aucune sauvegarde locale n'existait, on donne le temps max défini par le quiz
    if (!savedData || savedData.timeLeft === undefined) {
        timeLeft.value = props.quiz?.time_limit || 30;
    }

    // On démarre le chrono dès le chargement si le jeu n'est pas gelé en pause
    if (!isPaused.value) {
        startTimer();
    }
});

onUnmounted(() => {
    clearInterval(timer.value);
});

// ==========================================
// 4. SURVEILLANCE ET SAUVEGARDE EN DIRECT (WATCH)
// ==========================================
watch(
    [currentQuestionIndex, answers, timeLeft, isPaused],
    ([newIndex, newAnswers, newTime, newProgressPaused]) => {
        const dataToSave = {
            currentQuestionIndex: newIndex,
            answers: newAnswers,
            timeLeft: newTime,
            isPaused: newProgressPaused
        };
        localStorage.setItem(storageKey, JSON.stringify(dataToSave));
    },
    { deep: true }
);

// ==========================================
// 5. PROPRIÉTÉS CALCULÉES (COMPUTED)
// ==========================================
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

// ==========================================
// 6. LOGIQUE ET ACTIONS DU JEU
// ==========================================
const startTimer = () => {
    clearInterval(timer.value);
    timer.value = setInterval(() => {
        if (!isPaused.value && timeLeft.value > 0) {
            timeLeft.value--;
        } else if (timeLeft.value <= 0) {
            clearInterval(timer.value);
            isTimeOut.value = true;
            showTimeoutModal.value = true;
        }
    }, 1000);
};

const togglePause = () => {
    isPaused.value = !isPaused.value;
};

const selectOption = (option) => {
    if (isSubmitting.value || !currentQuestion.value || answers.value[currentQuestionIndex.value] || isPaused.value) return;

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

const triggerHint = () => {
    if (answers.value[currentQuestionIndex.value] || isPaused.value) return;
    showHintModal.value = true;
};

const confirmHint = () => {
    showHintModal.value = false;

    if (showHint.value || !currentQuestion.value?.hint) return;

    if (page.props.auth.user.xp < 50) {
        alert("XP insuffisants pour un indice (50 XP requis).");
        return;
    }

    router.post(route('player.use-hint'), {}, {
        onSuccess: () => {
            showHint.value = true;
            usedHintsCount.value++;
        },
        onError: () => {
            alert("Une erreur est survenue lors de l'utilisation de l'indice.");
        }
    });
};

const submitResults = async () => {
    try {
        if (isSubmitting.value) return;
        isSubmitting.value = true;
        clearInterval(timer.value);

        router.post(route('player.quiz.submit', props.quiz.id), {
            answers: answers.value,
            hints_used: usedHintsCount.value,
            time_left: timeLeft.value,
            failed: hearts.value <= 0,
        }, {
            onSuccess: () => {
                isFinished.value = true;
                isSubmitting.value = false;
                // Nettoyage impératif de la clé spécifique
                localStorage.removeItem(storageKey);
            },
            onError: () => {
                isSubmitting.value = false;
                alert("Une erreur est survenue.");
            }
        });
    } catch (error) {
        console.error("Erreur lors de l'envoi :", error);
        isSubmitting.value = false;
    }
};

const handleRetry = () => {
    currentQuestionIndex.value = 0;
    answers.value = [];
    isFinished.value = false;
    isTimeOut.value = false;
    timeLeft.value = props.quiz?.time_limit || 30;
    hearts.value = 5;
    xpGained.value = 0;
    usedHintsCount.value = 0;
    showHint.value = false;
    feedback.value = null;
    isPaused.value = false;
    startTimer();
};

const continueWithHeart = () => {
    if (page.props.auth.user.hearts < 1) {
        alert("Vous n'avez plus de cœurs ! Achetez-en un dans la boutique.");
        return;
    }

    isSubmitting.value = true;
    router.post(route('player.quiz.retry', props.quiz.id), {}, {
        onSuccess: () => {
            isTimeOut.value = false;
            showTimeoutModal.value = false;
            isFinished.value = false;
            timeLeft.value = 20; 
            isSubmitting.value = false;
            startTimer();
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const handleTimeoutSubmit = () => {
    showTimeoutModal.value = false;
    isFinished.value = true;
    submitResults();
};

const calculateStars = () => {
    const correctCount = answers.value.filter((ans, index) => {
        return ans === questions.value[index]?.correct_option;
    }).length;

    if (correctCount === 5) return 3;
    if (correctCount === 4) return 2;
    if (correctCount === 3) return 1;
    return 0;
};
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
                        <!-- <p class="text-xs text-muted-foreground uppercase tracking-widest">{{ quiz.category }}</p> -->
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Le Chronomètre -->
                    <div class="glass px-4 py-2 rounded-xl flex items-center gap-2"
                        :class="timeLeft < 10 ? 'text-destructive border-destructive/50 animate-pulse' : 'text-electric'">
                        <Clock class="h-4 w-4" />
                        <span class="font-display font-bold">{{ timeLeft }}s</span>
                    </div>

                    <!-- Le Bouton Pause -->
                    <button @click="togglePause"
                        class="glass p-2 rounded-xl flex items-center justify-center text-electric hover:bg-white/10 transition-colors"
                        :class="{ 'text-destructive border-destructive/30': isPaused }" title="Pause / Reprendre">
                        <!-- Icône dynamique : change selon l'état pause -->
                        <Pause v-if="!isPaused" class="h-4 w-4" />
                        <Play v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-10">
                <div
                    class="flex justify-between text-xs mb-2 text-muted-foreground uppercase tracking-widest font-bold">
                    <span v-if="totalQuestions > 0">Question {{ currentQuestionIndex + 1 }} sur {{ totalQuestions
                    }}</span>
                    <div class="flex items-center gap-2 text-electric animate-pulse" v-if="xpGained > 0">
                        <Zap class="h-3 w-3 fill-current" />
                        <span>+{{ xpGained }} XP</span>
                    </div>
                    <span>{{ Math.round(progress) }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gaming-darker overflow-hidden border border-white/5">
                    <div class="h-full bg-blue-500 transition-all duration-500" :style="{ width: `${progress}%` }" />
                </div>
            </div>

            <!-- No Quiz/Questions Error State -->
            <div v-if="totalQuestions === 0"
                class="glass-strong rounded-3xl p-12 text-center border border-destructive/20">
                <Info class="h-12 w-12 text-destructive mx-auto mb-4" />
                <h2 class="text-xl font-display text-white mb-2">Aucun contenu disponible</h2>
                <p class="text-muted-foreground mb-8">DÃ©solÃ©, ce quiz ne contient aucune question pour le moment.</p>
                <Link :href="route('player.cities')">
                    <NeonButton>Retour aux villes</NeonButton>
                </Link>
            </div>

            <!-- Question Card -->
            <div v-else-if="!isFinished"
                class="glass-strong rounded-3xl p-8 md:p-10 border border-electric/20 relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-xl md:text-2xl font-display text-white relative z-10 flex-1 pr-4">
                        {{ currentQuestion?.question_text || 'Chargement...' }}
                    </h2>
                    <div class="flex justify-end mb-4">
                        <button v-if="!showHint && currentQuestion?.hint && !isPaused" @click="triggerHint"
                            type="button"
                            class="glass px-4 py-2 rounded-xl flex items-center gap-2 text-sm text-amber-400 hover:bg-amber-500/10 border border-amber-500/20 transition-all active:scale-95 group">
                            <Lightbulb class="h-4 w-4 fill-current text-amber-400 animate-pulse" />
                            <span class="font-medium">Besoin d'un indice ?</span>
                            <span
                                class="bg-red-500/20 text-red-400 text-xs px-2 py-0.5 rounded-lg border border-red-500/30 font-bold ml-1">
                                -50 XP
                            </span>
                        </button>
                    </div>

                    <div v-if="showHintModal"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">

                        <div
                            class="glass max-w-md w-full p-6 rounded-2xl border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.8)] text-center animate-in fade-in zoom-in-95 duration-200">

                            <div
                                class="mx-auto h-12 w-12 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 mb-4">
                                <Lightbulb class="h-6 w-6 fill-current animate-bounce" />
                            </div>

                            <h3 class="text-xl font-display font-bold text-white mb-2">Débloquer l'indice ?</h3>
                            <p class="text-sm text-muted-foreground mb-6">
                                L'utilisation d'un indice va déduire <span class="text-red-400 font-bold">-50 XP</span>
                                de ton score sur ce défi. Es-tu sûr de vouloir continuer ?
                            </p>

                            <div class="flex gap-4 justify-center">
                                <button @click="showHintModal = false"
                                    class="px-4 py-2 rounded-xl border border-white/10 text-white hover:bg-white/5 transition-colors text-sm font-medium w-28">
                                    Annuler
                                </button>

                                <button @click="confirmHint"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold hover:opacity-90 shadow-[0_0_15px_rgba(245,158,11,0.4)] transition-all active:scale-95 text-sm w-28">
                                    Confirmer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="showHint"
                    class="mb-8 p-4 rounded-2xl bg-warning/5 border border-warning/20 text-warning text-xs italic animate-fade-down">
                    💡 <span class="text-white">{{ currentQuestion?.hint }}</span>
                </div>

                <div class="grid gap-4 relative z-10 mb-10">
                    <div v-if="currentQuestion && (!currentQuestion.options || (typeof currentQuestion.options === 'object' && Object.keys(currentQuestion.options).length === 0))"
                        class="text-white text-center p-8 border border-dashed border-white/10 rounded-2xl">
                        Aucune option de réponse n'a été configurée pour cette question.
                    </div>
                    <button v-for="(text, key) in currentQuestion?.options" :key="key" @click="selectOption(key)"
                        :disabled="!!answers[currentQuestionIndex] || isPaused"
                        class="w-full p-5 rounded-2xl border-2 transition-all duration-300 text-left flex items-center gap-4 group"
                        :class="[
                            answers[currentQuestionIndex] === key
                                ? (key === currentQuestion.correct_option
                                    ? 'bg-green-500/20 border-green-500 shadow-[0_0_20px_rgba(34,197,94,0.4)] text-white'
                                    : 'bg-red-500/20 border-red-500 shadow-[0_0_20px_rgba(239,68,68,0.4)] text-white')
                                : (answers[currentQuestionIndex]
                                    ? 'bg-white/5 border-white/10 opacity-50 cursor-not-allowed'
                                    : 'bg-white/5 border-white/10 text-muted-foreground hover:bg-white/10 hover:border-white/20')
                        ]">
                        <div class="h-8 w-8 rounded-lg border-2 border-current grid place-items-center font-display font-bold text-sm shrink-0 transition-colors"
                            :class="[
                                answers[currentQuestionIndex] === key
                                    ? (key === currentQuestion.correct_option ? 'bg-green-500 text-white border-green-500' : 'bg-red-500 text-white border-red-500')
                                    : ''
                            ]">
                            {{ key }}
                        </div>
                        <span class="font-bold text-white">{{ text }}</span>

                        <CheckCircle2
                            v-if="answers[currentQuestionIndex] === key && key === currentQuestion.correct_option"
                            class="h-6 w-6 text-green-500 ml-auto" />
                        <XCircle v-if="answers[currentQuestionIndex] === key && key !== currentQuestion.correct_option"
                            class="h-6 w-6 text-red-500 ml-auto" />
                    </button>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-white/5 relative z-10">
                    <button v-if="currentQuestionIndex < totalQuestions - 1" @click="nextQuestion"
                        :disabled="!answers[currentQuestionIndex]"
                        class="px-8 py-3 rounded-xl bg-electric text-white font-bold hover:shadow-neon disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                        Suivant
                        <ArrowRight class="h-4 w-4" />
                    </button>

                    <button v-else @click="submitResults" :disabled="!answers[currentQuestionIndex] || isSubmitting"
                        class="px-8 py-3 rounded-xl bg-success text-white font-bold shadow-[0_0_15px_rgba(34,197,94,0.3)] hover:shadow-[0_0_25px_rgba(34,197,94,0.5)] disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                        <Trophy class="h-4 w-4" /> Terminer le Quiz
                    </button>
                </div>
            </div>

            <!-- Results View -->
            <div v-else class="text-center animate-fade-up">
                <div v-if="isTimeOut" class="mb-8">
                    <div
                        class="h-24 w-24 rounded-3xl bg-destructive mx-auto grid place-items-center shadow-[0_0_30px_rgba(239,68,68,0.5)] mb-6 animate-pulse">
                        <Clock class="h-12 w-12 text-white" />
                    </div>
                    <h2 class="font-display text-4xl text-white mb-2 uppercase">TEMPS ÉCOULÉ !</h2>
                    <p class="text-muted-foreground text-sm mb-10">
                        Le chronomètre s'est arrêté avant que vous ne terminiez.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button @click="continueWithHeart" :disabled="isSubmitting"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-premium text-white font-black uppercase tracking-widest hover:scale-105 transition-all flex items-center justify-center gap-3 shadow-neon">
                            <Heart class="h-5 w-5 fill-current animate-pulse" />
                            Continuer la partie (-1 ❤️)
                        </button>
                        <button @click="handleRetry"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl border-2 border-white/10 text-white font-black uppercase tracking-widest hover:bg-white/5 transition-all">
                            Réessayer
                        </button>
                    </div>
                </div>

                <div v-else>
                    <div
                        class="h-24 w-24 rounded-3xl bg-gradient-electric mx-auto grid place-items-center shadow-neon mb-6 animate-bounce">
                        <Trophy v-if="calculateStars() >= 2" class="h-12 w-12 text-white" />
                        <Frown v-else class="h-12 w-12 text-white" />
                    </div>

                    <h2 class="font-display text-4xl text-white mb-2 uppercase">
                        {{ calculateStars() >= 2 ? 'FÉLICITATIONS !' : 'COURAGE !' }}
                    </h2>
                    <p class="text-muted-foreground text-sm mb-10">
                        {{ calculateStars() >= 2 ? 'Vous avez brillamment surmonté les épreuves.' : 'Continuez à vous  entraîner pour décrocher toutes les étoiles.' }}
                    </p>

                    <div v-if="hearts > 0" class="flex justify-center gap-3 mb-10">
                        <Star v-for="s in 3" :key="s" :class="[
                            'h-12 w-12 transition-all duration-700',
                            s <= calculateStars() ? 'text-yellow-400 fill-yellow-400 scale-110' : 'text-white/5'
                        ]" :style="{ transitionDelay: `${s * 200}ms` }" />
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-10">
                        <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                            <div class="text-[10px] text-muted-foreground uppercase mb-1">XP GAGNÉS</div>
                            <div class="text-2xl font-display text-electric">+{{ xpGained }}</div>
                        </div>
                        <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                            <div class="text-[10px] text-muted-foreground uppercase mb-1">INDICES UTILISÉS</div>
                            <div class="text-2xl font-display text-warning">{{ usedHintsCount }}</div>
                        </div>
                        <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                            <div class="text-[10px] text-muted-foreground uppercase mb-1">TEMPS RESTANT</div>
                            <div class="text-2xl font-display text-cyan-neon">{{ timeLeft }}s</div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button @click="handleRetry"
                            class="w-full sm:w-auto px-8 py-3 rounded-xl border-2 border-white/10 text-white font-bold hover:bg-white/5 transition-all flex items-center justify-center gap-2 group">
                            Réessayer
                        </button>
                        <Link :href="route('player.cities')">
                            <NeonButton variant="outline" class="w-full sm:w-auto">
                                Revenir aux Quiz
                            </NeonButton>
                        </Link>
                    </div>
                </div>
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
            </div>

            <!-- MODAL TEMPS ÉCOULÉ -->
            <Modal :show="showTimeoutModal" @close="handleTimeoutSubmit">
                <div
                    class="p-8 bg-gaming-darker border border-destructive/30 rounded-3xl overflow-hidden relative text-center">
                    <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

                    <div class="relative z-10">
                        <div
                            class="h-20 w-20 rounded-2xl bg-destructive/20 border border-destructive/50 grid place-items-center mx-auto mb-6 shadow-[0_0_30px_rgba(239,68,68,0.2)]">
                            <Clock class="h-10 w-10 text-destructive animate-pulse" />
                        </div>

                        <h2 class="font-display text-3xl text-white mb-2 uppercase tracking-tight">Temps Écoulé !</h2>
                        <p class="text-muted-foreground text-sm mb-8 px-4">
                            Le chronomètre s'est arrêté. Voulez-vous utiliser un cœur pour continuer la partie et
                            obtenir 30 secondes supplémentaires ?
                        </p>

                        <div class="flex flex-col gap-3">
                            <button @click="continueWithHeart" :disabled="isSubmitting"
                                class="w-full py-4 rounded-2xl bg-gradient-premium text-white font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3 shadow-neon">
                                <Heart class="h-5 w-5 fill-current" />
                                Continuer (-1 ❤️)
                            </button>

                            <button @click="handleTimeoutSubmit"
                                class="w-full py-4 rounded-2xl border-2 border-white/5 text-muted-foreground font-bold uppercase tracking-widest hover:bg-white/5 transition-all">
                                Arrêter et voir le score
                            </button>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
    </SiteLayout>
</template>
