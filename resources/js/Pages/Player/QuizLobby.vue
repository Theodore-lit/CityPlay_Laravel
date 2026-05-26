<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Lock, CheckCircle2, Play, Trophy, ChevronRight, Star } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
    quizzes: Object, // { easy: [], medium: [], hard: [] }
    completedQuizIds: Array,
    levels: Object, // { easy: { unlocked: true }, ... }
});

const activeLevel = ref('easy');

const isQuizCompleted = (quizId) => {
    return props.completedQuizIds.includes(quizId);
};
</script>

<template>
    <Head :title="`Quiz Lobby - ${city.name}`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('player.cities')" class="text-electric text-sm flex items-center gap-2 mb-4 hover:underline">
                    <ChevronRight class="h-4 w-4 rotate-180" /> Retour aux villes
                </Link>
                <h1 class="font-display text-4xl text-white">Quiz & Devinettes : <span class="text-electric">{{ city.name }}</span></h1>
                <p class="text-gray-400 mt-2">Réussissez tous les quiz d'un niveau pour débloquer le suivant.</p>
            </div>

            <!-- Niveaux de difficulté (Tabs) -->
            <div class="grid grid-cols-3 gap-4 mb-10">
                <button
                    v-for="(levelInfo, key) in levels"
                    :key="key"
                    @click="levelInfo.unlocked ? activeLevel = key : null"
                    :class="[
                        'relative p-6 rounded-2xl border backdrop-blur-xl transition-all duration-300 flex flex-col items-center gap-3 overflow-hidden group',
                        activeLevel === key ? 'bg-gradient-to-br from-electric/20 to-electric/5 border-electric/50 shadow-[0_0_20px_rgba(0,255,200,0.2)]' : 'bg-gradient-to-br from-white/15 to-white/5 border-white/20 hover:border-electric/40 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]',
                        !levelInfo.unlocked ? 'opacity-60 cursor-not-allowed grayscale' : 'cursor-pointer'
                    ]"
                >
                    <div v-if="!levelInfo.unlocked" class="absolute top-2 right-2">
                        <Lock class="h-4 w-4 text-gray-500" />
                    </div>

                    <div :class="[
                        'h-12 w-12 rounded-xl flex items-center justify-center text-2xl shadow-lg',
                        key === 'easy' ? 'bg-green-500/20 text-green-500' :
                        key === 'medium' ? 'bg-yellow-500/20 text-yellow-500' : 'bg-red-500/20 text-red-500'
                    ]">
                        <Trophy v-if="levelInfo.unlocked" class="h-6 w-6" />
                        <Lock v-else class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <div class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ key === 'easy' ? 'Facile' : key === 'medium' ? 'Moyen' : 'Difficile' }}</div>
                        <div class="text-white font-display text-lg">
                            {{ quizzes[key].filter(q => isQuizCompleted(q.id)).length }} / {{ quizzes[key].length }}
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-1 bg-white/5 rounded-full mt-2 overflow-hidden">
                        <div
                            class="h-full bg-electric transition-all duration-500"
                            :style="{ width: `${(quizzes[key].filter(q => isQuizCompleted(q.id)).length / quizzes[key].length) * 100}%` }"
                        ></div>
                    </div>
                </button>
            </div>

            <!-- Liste des Quiz pour le niveau actif -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="quiz in quizzes[activeLevel]"
                    :key="quiz.id"
                    class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl p-5 border border-white/20 flex flex-col justify-between group hover:border-electric/40 transition-all shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
                >
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div :class="[
                                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest',
                                isQuizCompleted(quiz.id) ? 'bg-green-500/20 text-green-400' : 'bg-electric/20 text-electric'
                            ]">
                                {{ isQuizCompleted(quiz.id) ? 'Réussi' : 'À faire' }}
                            </div>
                            <div class="flex gap-1">
                                <Star v-for="i in 3" :key="i" class="h-3 w-3 text-yellow-400" :class="isQuizCompleted(quiz.id) ? 'fill-yellow-400' : 'opacity-20'" />
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white group-hover:text-electric transition-colors">{{ quiz.title }}</h3>
                        <p class="text-gray-400 text-sm mt-2 line-clamp-2">{{ quiz.description }}</p>
                    </div>

                    <div class="mt-6 flex items-center justify-between pt-4 border-t border-white/5">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Récompense</span>
                            <span class="text-white font-mono font-bold">+{{ quiz.xp_reward }} XP</span>
                        </div>

                        <Link
                            :href="route('player.quiz', quiz.id)"
                            class="flex items-center gap-2 bg-electric hover:bg-electric-dark text-white px-5 py-2 rounded-xl font-bold transition-all shadow-lg hover:shadow-neon"
                        >
                            <Play v-if="!isQuizCompleted(quiz.id)" class="h-4 w-4 fill-current" />
                            <CheckCircle2 v-else class="h-4 w-4" />
                            {{ isQuizCompleted(quiz.id) ? 'Refaire' : 'Jouer' }}
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="quizzes[activeLevel].length === 0" class="col-span-full py-20 text-center bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-3xl border border-dashed border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                    <div class="text-4xl mb-4">📭</div>
                    <div class="text-white font-display text-xl">Aucun quiz disponible pour ce niveau</div>
                    <p class="text-gray-400 mt-2">Revenez plus tard pour de nouveaux défis !</p>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.shadow-neon {
    box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
}
</style>
