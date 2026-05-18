<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Brain, Clock, Zap, ArrowRight, CheckCircle2, XCircle, Heart, Info, Star, Trophy } from 'lucide-vue-next';

const props = defineProps({
    quiz: Object
});

const currentQuestionIndex = ref(0);
const answers = ref([]);
const isFinished = ref(false);
const timeLeft = ref(props.quiz.time_limit);
const timer = ref(null);
const hearts = ref(3);
const usedHintsCount = ref(0);
const showHint = ref(false);
const feedback = ref(null); // 'correct' or 'incorrect'
const isSubmitting = ref(false);

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value]);
const progress = computed(() => ((currentQuestionIndex.value + 1) / props.quiz.questions.length) * 100);

const selectOption = (option) => {
    if (feedback.value || isSubmitting.value) return;

    answers.value[currentQuestionIndex.value] = option;
    
    if (option === currentQuestion.value.correct_option) {
        feedback.value = 'correct';
    } else {
        feedback.value = 'incorrect';
        hearts.value--;
        if (hearts.value <= 0) {
            setTimeout(submitResults, 1000);
            return;
        }
    }

    setTimeout(() => {
        feedback.value = null;
        showHint.value = false;
        if (currentQuestionIndex.value < props.quiz.questions.length - 1) {
            currentQuestionIndex.value++;
        } else {
            submitResults();
        }
    }, 1500);
};

const submitResults = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    clearInterval(timer.value);
    
    router.post(route('player.quiz.submit', props.quiz.id), {
        answers: answers.value,
        hearts_left: hearts.value,
        hints_used: usedHintsCount.value
    }, {
        onSuccess: () => {
            isFinished.value = true;
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
            alert("Une erreur est survenue lors de l'enregistrement des résultats.");
        }
    });
};

const useHint = () => {
    if (!showHint.value) {
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
    startTimer();
});

onUnmounted(() => {
    clearInterval(timer.value);
});
</script>

<template>
    <Head :title="`Quiz: ${quiz.title}`" />

    <SiteLayout hideFooter>
        <div class="mx-auto max-w-3xl px-4 py-12">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
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
                    <div class="glass px-3 py-2 rounded-xl flex items-center gap-2 text-red-500 border-red-500/20">
                        <Heart class="h-4 w-4 fill-current" />
                        <span class="font-display font-bold">{{ hearts }}</span>
                    </div>
                    <div class="glass px-4 py-2 rounded-xl flex items-center gap-2" :class="timeLeft < 10 ? 'text-destructive border-destructive/50 animate-pulse' : 'text-electric'">
                        <Clock class="h-4 w-4" />
                        <span class="font-display font-bold">{{ timeLeft }}s</span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-10">
                <div class="flex justify-between text-xs mb-2 text-muted-foreground uppercase tracking-widest font-bold">
                    <span>Question {{ currentQuestionIndex + 1 }} sur {{ quiz.questions.length }}</span>
                    <span>{{ Math.round(progress) }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gaming-darker overflow-hidden border border-white/5">
                    <div class="h-full bg-gradient-electric transition-all duration-500" :style="{ width: `${progress}%` }" />
                </div>
            </div>

            <!-- Question Card -->
            <div v-if="!isFinished" class="glass-strong rounded-3xl p-8 md:p-10 border border-electric/20 relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-xl md:text-2xl font-display text-white relative z-10 flex-1 pr-4">
                        {{ currentQuestion.question_text }}
                    </h2>
                    <button 
                        v-if="!showHint"
                        @click="useHint"
                        class="h-10 px-4 rounded-xl glass border-warning/40 text-warning text-[10px] font-black uppercase tracking-widest hover:bg-warning/10 transition-all shrink-0"
                    >
                        Indice
                    </button>
                </div>

                <div v-if="showHint" class="mb-8 p-4 rounded-2xl bg-warning/5 border border-warning/20 text-warning text-xs italic animate-fade-down">
                    💡 {{ currentQuestion.hint || 'Réfléchissez bien à l\'histoire locale.' }}
                </div>

                <div class="grid gap-4 relative z-10">
                    <button 
                        v-for="(text, key) in currentQuestion.options" 
                        :key="key"
                        @click="selectOption(key)"
                        class="w-full p-5 rounded-2xl border transition-all duration-300 text-left flex items-center gap-4 group"
                        :class="[
                            feedback === 'correct' && currentQuestion.correct_option === key 
                            ? 'bg-success/20 border-success shadow-[0_0_20px_rgba(34,197,94,0.2)] text-white' 
                            : feedback === 'incorrect' && answers[currentQuestionIndex] === key 
                            ? 'bg-destructive/20 border-destructive shadow-[0_0_20px_rgba(239,68,68,0.2)] text-white'
                            : answers[currentQuestionIndex] === key 
                            ? 'bg-electric/20 border-electric text-white' 
                            : 'bg-white/5 border-white/10 text-muted-foreground hover:bg-white/10 hover:border-white/20'
                        ]"
                    >
                        <div class="h-8 w-8 rounded-lg border border-current grid place-items-center font-display font-bold text-sm shrink-0 group-hover:bg-electric group-hover:text-electric-foreground transition-colors">
                            {{ key }}
                        </div>
                        <span class="font-medium">{{ text }}</span>
                        
                        <CheckCircle2 v-if="feedback === 'correct' && currentQuestion.correct_option === key" class="h-5 w-5 text-success ml-auto animate-bounce" />
                        <XCircle v-if="feedback === 'incorrect' && answers[currentQuestionIndex] === key" class="h-5 w-5 text-destructive ml-auto animate-shake" />
                    </button>
                </div>
            </div>

            <!-- Results View -->
            <div v-else class="text-center animate-fade-up">
                <div class="h-24 w-24 rounded-3xl bg-gradient-electric mx-auto grid place-items-center shadow-neon mb-6 animate-bounce">
                    <Trophy v-if="hearts > 0" class="h-12 w-12 text-white" />
                    <Heart v-else class="h-12 w-12 text-white" />
                </div>
                
                <h2 class="font-display text-4xl text-white mb-2 uppercase">
                    {{ hearts > 0 ? 'DÉFI TERMINÉ !' : 'SESSION ÉCHOUÉE' }}
                </h2>
                <p class="text-muted-foreground text-sm mb-10">
                    {{ hearts > 0 ? 'Vous avez brillamment surmonté les épreuves.' : 'Vos vies sont épuisées. Entraînez-vous encore !' }}
                </p>

                <div v-if="hearts > 0" class="flex justify-center gap-3 mb-10">
                    <Star 
                        v-for="s in 3" 
                        :key="s" 
                        :class="[
                            'h-12 w-12 transition-all duration-700',
                            s <= (usedHintsCount === 0 ? 3 : (usedHintsCount === 1 ? 2 : 1)) ? 'text-yellow-400 fill-yellow-400 scale-110' : 'text-white/5'
                        ]"
                        :style="{ transitionDelay: `${s * 200}ms` }"
                    />
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                    <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-muted-foreground uppercase mb-1">XP GAGNÉS</div>
                        <div class="text-2xl font-display text-electric">+{{ hearts > 0 ? quiz.xp_reward : 0 }}</div>
                    </div>
                    <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-muted-foreground uppercase mb-1">VIES RESTANTES</div>
                        <div class="text-2xl font-display text-red-500">{{ hearts }} / 3</div>
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
                    <Link :href="route('player.cities')">
                        <NeonButton variant="outline" class="w-full sm:w-auto">
                            Revenir aux Quiz
                        </NeonButton>
                    </Link>
                    <Link :href="route('player.modes')">
                        <NeonButton class="w-full sm:w-auto">
                            Changer de Mode <ArrowRight class="ml-2 h-4 w-4" />
                        </NeonButton>
                    </Link>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>
