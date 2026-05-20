<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Brain, Clock, Zap, ArrowRight, CheckCircle2, XCircle, Info, Trophy, Frown, Heart, Eye, Activity, Target } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    quiz: Object
});

const page = usePage();
const currentQuestionIndex = ref(0);
const answers = ref([]);
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

// Simplification et robustesse de la récupération des questions
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

// Initialisation sécurisée du temps
const timeLeft = ref(60);
onMounted(() => {
    timeLeft.value = props.quiz?.time_limit || 60;
    startTimer();
});

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
        onSuccess: () => {
            isFinished.value = true;
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
            alert("Une erreur est survenue.");
        }
    });
};

const handleRetry = () => {
    // Réinitialisation simple sans consommer de cœur (bouton juste informatif ou relance)
    currentQuestionIndex.value = 0;
    answers.value = [];
    isFinished.value = false;
    isTimeOut.value = false;
    timeLeft.value = props.quiz?.time_limit || 60;
    hearts.value = 5;
    xpGained.value = 0;
    usedHintsCount.value = 0;
    showHint.value = false;
    feedback.value = null;
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
            timeLeft.value = 30; // On redonne 30 secondes pour continuer
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

const useHint = () => {
    if (!showHint.value && currentQuestion.value?.hint) {
        showHint.value = true;
        usedHintsCount.value++;
    }
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

const startTimer = () => {
    clearInterval(timer.value);
    timer.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timer.value);
            isTimeOut.value = true;
            showTimeoutModal.value = true;
        }
    }, 1000);
};

onUnmounted(() => {
    clearInterval(timer.value);
});
</script>

<template>
    <Head :title="`Quiz: ${quiz?.title || 'Chargement...'}`" />

    <SiteLayout hideFooter isHUD>
        <HUDHeader />

        <div class="mx-auto max-w-4xl px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Header HUD -->
            <div class="flex items-center justify-between mb-12" v-if="quiz">
                <div class="flex items-center gap-6">
                    <div class="h-16 w-16 rounded-2xl bg-primary/10 border-2 border-primary/40 grid place-items-center shadow-[0_0_25px_rgba(6,182,212,0.3)] relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 bg-primary/5 animate-pulse" />
                        <Brain class="h-8 w-8 text-primary relative z-10 drop-shadow-[0_0_8px_#06b6d4]" />
                    </div>
                    <div>
                        <div class="text-[9px] text-primary font-black uppercase tracking-[0.4em] mb-1.5 flex items-center gap-2">
                            <Activity class="h-3 w-3 animate-pulse" /> MISSION_ACTIVE // DATA_EXTRACTION
                        </div>
                        <h1 class="font-display text-2xl md:text-3xl text-white font-black uppercase italic tracking-tighter">{{ quiz.title }}</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="px-6 py-3 rounded-2xl border-2 backdrop-blur-xl flex items-center gap-4 transition-all duration-500" 
                         :class="timeLeft < 10 ? 'border-red-500 bg-red-500/10 text-red-500 shadow-[0_0_20px_rgba(239,68,68,0.3)] animate-pulse' : 'border-white/10 bg-white/5 text-primary'">
                        <Clock class="h-5 w-5" />
                        <span class="font-display font-black italic text-xl tracking-tighter">{{ timeLeft }}S</span>
                    </div>
                </div>
            </div>

            <!-- Progress HUD -->
            <div class="mb-12">
                <div class="flex justify-between text-[10px] mb-4 text-white/40 font-black uppercase tracking-[0.3em]">
                    <span v-if="totalQuestions > 0">QUERY_SEQUENCE: {{ currentQuestionIndex + 1 }} / {{ totalQuestions }}</span>
                    <div class="flex items-center gap-2 text-primary animate-pulse" v-if="xpGained > 0">
                        <Zap class="h-3.5 w-3.5" />
                        <span>+{{ xpGained }} XP_COLLECTED</span>
                    </div>
                    <span>SYNC_STATUS: {{ Math.round(progress) }}%</span>
                </div>
                <!-- Segmented Progress Bar -->
                <div class="segmented-progress h-3">
                    <div v-for="seg in 20" :key="seg" 
                         :class="cn('progress-segment', (seg * 5) <= progress ? 'active' : '')">
                    </div>
                </div>
            </div>

            <!-- No Quiz Error State -->
            <div v-if="totalQuestions === 0" class="hud-glass-card rounded-[3rem] p-16 text-center border-2 border-dashed border-red-500/20 max-w-2xl mx-auto">
                <Info class="h-16 w-16 text-red-500 mx-auto mb-6 opacity-40" />
                <h2 class="text-3xl font-display font-black text-white uppercase italic tracking-tighter mb-4">SYSTEM_ERROR: NO_DATA</h2>
                <p class="text-white/40 text-sm font-bold uppercase tracking-widest mb-10 leading-relaxed">Désolé, ce module de données ne contient aucune séquence valide pour le moment.</p>
                <HUDButton :href="route('player.cities')" variant="primary" class="h-14 px-10">
                    RETOUR_SECTEURS
                </HUDButton>
            </div>

            <!-- Question Card HUD -->
            <div v-else-if="!isFinished" class="neon-border-box p-8 md:p-12 overflow-hidden group">
                <div class="neon-corner top-0 left-0 border-r-0 border-b-0" />
                <div class="neon-corner top-0 right-0 border-l-0 border-b-0" />
                <div class="neon-corner bottom-0 left-0 border-r-0 border-t-0" />
                <div class="neon-corner bottom-0 right-0 border-l-0 border-t-0" />

                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                
                <div class="flex justify-between items-start mb-10 relative z-10">
                    <h2 class="text-2xl md:text-3xl font-display font-black text-white uppercase italic tracking-tighter leading-tight flex-1 pr-8">
                        {{ currentQuestion?.question_text || 'INITIALIZING_QUERY...' }}
                    </h2>
                    <HUDButton 
                        v-if="!showHint && currentQuestion?.hint"
                        @click="useHint"
                        variant="magenta"
                        class="h-10 px-6 scale-90"
                    >
                        <Eye class="h-4 w-4 mr-2" /> DÉCODER_INDICE
                    </HUDButton>
                </div>

                <Transition name="fade">
                    <div v-if="showHint" class="mb-10 p-6 rounded-2xl bg-amber-500/5 border border-amber-500/20 text-[10px] text-amber-500 font-black uppercase tracking-widest italic animate-fade-down">
                        <div class="flex gap-3">
                            <Info class="h-4 w-4 shrink-0" />
                            <span>{{ currentQuestion?.hint }}</span>
                        </div>
                    </div>
                </Transition>

                <div class="grid gap-6 relative z-10 mb-12">
                    <button 
                        v-for="(text, key) in currentQuestion?.options" 
                        :key="key"
                        @click="selectOption(key)"
                        :disabled="!!answers[currentQuestionIndex]"
                        class="w-full p-6 rounded-2xl border-2 transition-all duration-500 text-left flex items-center gap-6 group/option overflow-hidden relative"
                        :class="[
                            answers[currentQuestionIndex] === key 
                                ? (key === currentQuestion.correct_option 
                                    ? 'bg-green-500/10 border-green-500 shadow-[0_0_30px_rgba(34,197,94,0.2)] text-white' 
                                    : 'bg-red-500/10 border-red-500 shadow-[0_0_30px_rgba(239,68,68,0.2)] text-white')
                                : (answers[currentQuestionIndex] 
                                    ? 'bg-white/5 border-white/5 opacity-30 cursor-not-allowed' 
                                    : 'bg-white/[0.02] border-white/10 text-white/60 hover:border-primary/40 hover:bg-primary/5 hover:text-white')
                        ]"
                    >
                        <div class="absolute inset-0 grid-bg opacity-0 group-hover/option:opacity-10 transition-opacity" />
                        
                        <div class="h-10 w-10 rounded-xl border-2 grid place-items-center font-display font-black italic text-lg shrink-0 transition-all duration-500"
                             :class="[
                                 answers[currentQuestionIndex] === key 
                                    ? (key === currentQuestion.correct_option ? 'bg-green-500 text-black border-green-500 shadow-[0_0_15px_#22c55e]' : 'bg-red-500 text-black border-red-500 shadow-[0_0_15px_#ef4444]')
                                    : 'border-white/20 bg-white/5 group-hover/option:border-primary group-hover/option:text-primary group-hover/option:shadow-[0_0_10px_rgba(6,182,212,0.3)]'
                             ]">
                            {{ key }}
                        </div>
                        <span class="font-black uppercase tracking-widest text-sm relative z-10">{{ text }}</span>
                        
                        <div class="ml-auto flex items-center gap-3 relative z-10">
                            <CheckCircle2 v-if="answers[currentQuestionIndex] === key && key === currentQuestion.correct_option" class="h-7 w-7 text-green-500 drop-shadow-[0_0_8px_#22c55e]" />
                            <XCircle v-if="answers[currentQuestionIndex] === key && key !== currentQuestion.correct_option" class="h-7 w-7 text-red-500 drop-shadow-[0_0_8px_#ef4444]" />
                        </div>
                    </button>
                </div>

                <!-- Navigation HUD -->
                <div class="flex items-center justify-between pt-8 border-t border-white/5 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5">
                            <Heart v-for="h in 5" :key="h" 
                                   :class="cn('h-5 w-5 transition-all duration-700', 
                                   h <= hearts ? 'text-red-500 fill-red-500 drop-shadow-[0_0_8px_#ef4444]' : 'text-white/5')" />
                        </div>
                        <span class="text-[9px] font-black text-white/20 uppercase tracking-[0.3em]">INTEGRITY_SHIELD</span>
                    </div>

                    <div class="flex gap-4">
                        <HUDButton 
                            v-if="currentQuestionIndex < totalQuestions - 1"
                            @click="nextQuestion"
                            :disabled="!answers[currentQuestionIndex]"
                            variant="primary"
                            class="h-12 px-10"
                        >
                            <div class="flex items-center gap-2">
                                <span>SUIVANT</span>
                                <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                            </div>
                        </HUDButton>

                        <HUDButton 
                            v-else
                            @click="submitResults"
                            :disabled="!answers[currentQuestionIndex] || isSubmitting"
                            variant="primary"
                            class="h-12 px-10"
                        >
                            <div class="flex items-center gap-2">
                                <Target class="h-4 w-4" />
                                <span>TERMINER_SEQUENCE</span>
                            </div>
                        </HUDButton>
                    </div>
                </div>
            </div>

            <!-- Results View HUD -->
            <div v-else class="text-center animate-fade-up">
                <div v-if="isTimeOut" class="max-w-2xl mx-auto">
                    <div class="h-32 w-32 rounded-[2.5rem] bg-red-500/10 border-2 border-red-500/40 mx-auto grid place-items-center shadow-[0_0_40px_rgba(239,68,68,0.2)] mb-8 animate-pulse relative">
                        <div class="absolute inset-0 bg-red-500/5 animate-ping rounded-[2.5rem]" />
                        <Clock class="h-14 w-14 text-red-500 relative z-10" />
                    </div>
                    <h2 class="font-display text-5xl md:text-6xl font-black text-white mb-4 uppercase italic tracking-tighter">TEMPS_ÉCOULÉ !</h2>
                    <p class="text-white/40 text-sm font-bold uppercase tracking-[0.4em] mb-12">
                        DÉFAILLANCE_TEMPORELLE : LE CHRONOMÈTRE S'EST ARRÊTÉ AVANT LA SYNCHRONISATION.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <HUDButton 
                            @click="continueWithHeart"
                            :disabled="isSubmitting"
                            variant="magenta"
                            class="h-16 px-10 rounded-2xl flex-1 max-w-xs"
                        >
                            <div class="flex items-center gap-3">
                                <Heart class="h-5 w-5 fill-current animate-pulse" />
                                <span>CONTINUER_MISSION (-1 ❤️)</span>
                            </div>
                        </HUDButton>
                        <button 
                            @click="handleRetry"
                            class="flex-1 max-w-xs h-16 rounded-2xl border-2 border-white/10 text-white font-black uppercase tracking-[0.3em] text-[10px] hover:bg-white/5 transition-all"
                        >
                            RÉINITIALISER_SEQUENCE
                        </button>
                    </div>
                </div>

                <div v-else class="max-w-3xl mx-auto">
                    <div class="h-32 w-32 rounded-[2.5rem] bg-primary/10 border-2 border-primary/40 mx-auto grid place-items-center shadow-[0_0_40px_rgba(6,182,212,0.2)] mb-8 animate-bounce relative">
                        <div class="absolute inset-0 bg-primary/5 animate-ping rounded-[2.5rem]" />
                        <Trophy v-if="calculateStars() >= 2" class="h-14 w-14 text-primary relative z-10 drop-shadow-[0_0_12px_#06b6d4]" />
                        <Frown v-else class="h-14 w-14 text-white/40 relative z-10" />
                    </div>
                    
                    <h2 class="font-display text-5xl md:text-6xl text-white font-black uppercase italic tracking-tighter mb-4">
                        {{ calculateStars() >= 2 ? 'MISSION_SUCCÈS' : 'SEQUENCE_INCOMPLETE' }}
                    </h2>
                    <p class="text-white/40 text-sm font-bold uppercase tracking-[0.4em] mb-12">
                        {{ calculateStars() >= 2 ? 'VOUS AVEZ BRILLAMMENT EXTRAIT LES DONNÉES DU SECTEUR.' : 'EXTRACTION PARTIELLE : CONTINUEZ L\'ENTRAÎNEMENT POUR UNE SYNC TOTALE.' }}
                    </p>

                    <div v-if="hearts > 0" class="flex justify-center gap-6 mb-16">
                        <Star 
                            v-for="s in 3" 
                            :key="s" 
                            :class="[
                                'h-16 w-16 transition-all duration-1000 drop-shadow-[0_0_15px_rgba(234,179,8,0.5)]',
                                s <= calculateStars() ? 'text-amber-400 fill-amber-400 scale-110' : 'text-white/5'
                            ]"
                            :style="{ transitionDelay: `${s * 300}ms` }"
                        />
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-16">
                        <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-primary/40 transition-all">
                            <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">XP_EXTRACTION</div>
                            <div class="text-3xl font-display text-primary font-black italic tracking-tighter">+{{ xpGained }}</div>
                        </div>
                        <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-magenta-500/40 transition-all">
                            <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">HINTS_DECRYPTED</div>
                            <div class="text-3xl font-display text-magenta-500 font-black italic tracking-tighter">{{ usedHintsCount }}</div>
                        </div>
                        <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-cyan-400/40 transition-all">
                            <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">TIME_ELAPSED</div>
                            <div class="text-3xl font-display text-cyan-400 font-black italic tracking-tighter">{{ timeLeft }}S</div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <button 
                            @click="handleRetry"
                            class="flex-1 max-w-xs h-16 rounded-2xl border-2 border-white/10 text-white font-black uppercase tracking-[0.3em] text-[10px] hover:bg-white/5 transition-all"
                        >
                            RÉINITIALISER_SEQUENCE
                        </button>
                        <HUDButton :href="route('player.cities')" variant="primary" class="h-16 px-10 rounded-2xl flex-1 max-w-xs">
                            <div class="flex items-center gap-3">
                                <span>RETOUR_SECTEURS</span>
                                <ArrowRight class="h-5 w-5" />
                            </div>
                        </HUDButton>
                    </div>
                </div>
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
            </div>

            <!-- MODAL TEMPS ÉCOULÉ HUD -->
            <Modal :show="showTimeoutModal" @close="handleTimeoutSubmit">
                <div class="p-1 rounded-[3rem] bg-gradient-to-br from-red-500/40 via-white/5 to-primary/40">
                    <div class="p-12 bg-zinc-950 rounded-[2.9rem] overflow-hidden relative text-center max-w-lg mx-auto">
                        <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
                        
                        <div class="relative z-10">
                            <div class="h-24 w-24 rounded-full bg-red-500/10 border-2 border-red-500/40 grid place-items-center mx-auto mb-8 shadow-[0_0_40px_rgba(239,68,68,0.2)]">
                                <Clock class="h-12 w-12 text-red-500 animate-pulse" />
                            </div>
                            
                            <h2 class="font-display text-4xl text-white font-black uppercase italic tracking-tighter mb-4">CHRONO_EXPIRED</h2>
                            <p class="text-white/40 text-xs font-bold uppercase tracking-[0.2em] mb-10 px-4 leading-loose">
                                Le flux temporel a été interrompu. Voulez-vous injecter un cœur de secours pour prolonger la séquence de 30 secondes ?
                            </p>

                            <div class="flex flex-col gap-4">
                                <HUDButton 
                                    @click="continueWithHeart"
                                    :disabled="isSubmitting"
                                    variant="primary"
                                    class="h-16 rounded-2xl"
                                >
                                    <div class="flex items-center gap-3">
                                        <Heart class="h-5 w-5 fill-current" />
                                        <span>INJECTER_COEUR (-1 ❤️)</span>
                                    </div>
                                </HUDButton>
                                
                                <button 
                                    @click="handleTimeoutSubmit"
                                    class="h-14 rounded-2xl border-2 border-white/5 text-white/40 font-black uppercase tracking-[0.3em] text-[10px] hover:bg-white/5 transition-all"
                                >
                                    ABANDONNER_SEQUENCE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
