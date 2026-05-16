<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Brain, Clock, Zap, ArrowRight, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps({
    quiz: Object
});

const currentQuestionIndex = ref(0);
const answers = ref([]);
const isFinished = ref(false);
const timeLeft = ref(props.quiz.time_limit);
const timer = ref(null);

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value]);
const progress = computed(() => ((currentQuestionIndex.value + 1) / props.quiz.questions.length) * 100);

const selectOption = (option) => {
    answers.value[currentQuestionIndex.value] = option;
    
    setTimeout(() => {
        if (currentQuestionIndex.value < props.quiz.questions.length - 1) {
            currentQuestionIndex.value++;
        } else {
            finishQuiz();
        }
    }, 500);
};

const finishQuiz = () => {
    isFinished.value = true;
    clearInterval(timer.value);
    
    router.post(route('player.quiz.submit', props.quiz.id), {
        answers: answers.value
    });
};

const startTimer = () => {
    timer.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            finishQuiz();
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
                
                <h2 class="text-xl md:text-2xl font-display text-white mb-8 relative z-10">
                    {{ currentQuestion.question_text }}
                </h2>

                <div class="grid gap-4 relative z-10">
                    <button 
                        v-for="(text, key) in currentQuestion.options" 
                        :key="key"
                        @click="selectOption(key)"
                        class="w-full p-5 rounded-2xl border transition-all duration-300 text-left flex items-center gap-4 group"
                        :class="[
                            answers[currentQuestionIndex] === key 
                            ? 'bg-electric/20 border-electric shadow-neon text-white' 
                            : 'bg-white/5 border-white/10 text-muted-foreground hover:bg-white/10 hover:border-white/20'
                        ]"
                    >
                        <div class="h-8 w-8 rounded-lg border border-current grid place-items-center font-display font-bold text-sm shrink-0 group-hover:bg-electric group-hover:text-electric-foreground transition-colors">
                            {{ key }}
                        </div>
                        <span class="font-medium">{{ text }}</span>
                    </button>
                </div>
            </div>

            <!-- Finish Overlay -->
            <div v-else class="glass-strong rounded-3xl p-12 text-center border border-electric/20">
                <div class="h-20 w-20 rounded-full bg-electric/20 grid place-items-center mx-auto mb-6">
                    <Zap class="h-10 w-10 text-electric animate-pulse-glow" />
                </div>
                <h2 class="font-display text-3xl text-white mb-4">Analyse des résultats...</h2>
                <p class="text-muted-foreground">Veuillez patienter pendant que nous calculons vos XP et mettons à jour votre rang.</p>
            </div>
        </div>
    </SiteLayout>
</template>
