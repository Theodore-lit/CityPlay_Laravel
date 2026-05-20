<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Lock, CheckCircle2, Play, Trophy, ChevronRight, Star, Brain, Zap, Activity } from 'lucide-vue-next';
import { ref } from 'vue';
import { cn } from '@/lib/utils';

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

    <SiteLayout isHUD>
        <HUDHeader />

        <div class="mx-auto max-w-7xl px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <Link :href="route('player.cities')" class="text-primary text-[10px] font-black uppercase tracking-[0.3em] flex items-center gap-2 hover:translate-x-[-4px] transition-transform">
                            <ChevronRight class="h-4 w-4 rotate-180" /> RETOUR_SECTEURS
                        </Link>
                    </div>
                    <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4">DATA_QUERY_MODULE // {{ city.name.toUpperCase() }}</div>
                    <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
                        QUIZ & <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">DEVINETTES</span>
                    </h1>
                </div>
                <div class="px-6 py-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl hidden md:block">
                    <div class="flex items-center gap-3">
                        <Activity class="h-4 w-4 text-primary animate-pulse" />
                        <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">SIGNAL_STABLE // 100%_SYNC</span>
                    </div>
                </div>
            </div>

            <!-- Niveaux de difficulté (Tabs) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <button 
                    v-for="(levelInfo, key) in levels" 
                    :key="key"
                    @click="levelInfo.unlocked ? activeLevel = key : null"
                    :class="[
                        'relative p-8 rounded-[2rem] border-2 transition-all duration-500 flex flex-col items-center gap-4 overflow-hidden group',
                        activeLevel === key ? 'border-primary bg-primary/10 shadow-[0_0_30px_rgba(6,182,212,0.15)]' : 'border-white/5 bg-white/[0.02] hover:border-white/20',
                        !levelInfo.unlocked ? 'opacity-40 grayscale cursor-not-allowed' : 'cursor-pointer'
                    ]"
                >
                    <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                    
                    <div v-if="!levelInfo.unlocked" class="absolute top-4 right-4">
                        <Lock class="h-4 w-4 text-white/20" />
                    </div>
                    
                    <div :class="[
                        'h-16 w-16 rounded-2xl flex items-center justify-center border-2 transition-all duration-500 relative overflow-hidden',
                        key === 'easy' ? 'border-green-500/40 bg-green-500/10 text-green-500 shadow-[0_0_20px_rgba(34,197,94,0.2)]' : 
                        key === 'medium' ? 'border-amber-500/40 bg-amber-500/10 text-amber-500 shadow-[0_0_20px_rgba(245,158,11,0.2)]' : 
                        'border-red-500/40 bg-red-500/10 text-red-500 shadow-[0_0_20px_rgba(239,68,68,0.2)]'
                    ]">
                        <div class="absolute inset-0 bg-current opacity-10 animate-pulse" />
                        <Trophy v-if="levelInfo.unlocked" class="h-8 w-8 relative z-10" />
                        <Lock v-else class="h-8 w-8 relative z-10" />
                    </div>

                    <div class="text-center relative z-10">
                        <div class="text-[10px] font-black uppercase tracking-[0.4em] text-white/40 mb-1">{{ key === 'easy' ? 'FACILE' : key === 'medium' ? 'MOYEN' : 'DIFFICILE' }}</div>
                        <div class="text-white font-display text-2xl font-black italic tracking-tighter">
                            {{ quizzes[key].filter(q => isQuizCompleted(q.id)).length }} / {{ quizzes[key].length }}
                        </div>
                    </div>
                    
                    <!-- Progress LED HUD -->
                    <div class="w-full mt-4">
                        <div class="flex gap-1.5 h-2">
                            <div v-for="seg in 10" :key="seg" 
                                 :class="cn('flex-1 rounded-sm transition-all duration-700', 
                                 (seg * 10) <= (quizzes[key].filter(q => isQuizCompleted(q.id)).length / quizzes[key].length * 100) ? 'bg-primary shadow-[0_0_8px_#06b6d4]' : 'bg-white/5')">
                            </div>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Liste des Quiz pour le niveau actif -->
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="quiz in quizzes[activeLevel]" 
                    :key="quiz.id"
                    class="hud-glass-card rounded-[2.5rem] p-8 border-2 border-white/5 flex flex-col justify-between group transition-all duration-700 h-[380px] relative overflow-hidden"
                >
                    <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-8">
                            <div :class="[
                                'px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-[0.3em] border backdrop-blur-md',
                                isQuizCompleted(quiz.id) ? 'bg-green-500/10 border-green-500/40 text-green-400' : 'bg-primary/10 border-primary/40 text-primary'
                            ]">
                                {{ isQuizCompleted(quiz.id) ? 'DÉCRYPTÉ' : 'SÉCURISÉ' }}
                            </div>
                            <div class="flex gap-1.5">
                                <Star v-for="i in 3" :key="i" class="h-4 w-4 drop-shadow-[0_0_8px_rgba(234,179,8,0.5)]" :class="isQuizCompleted(quiz.id) ? 'text-amber-400 fill-amber-400' : 'text-white/10'" />
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-12 w-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-primary shrink-0 group-hover:border-primary/40 transition-all">
                                <Brain class="h-6 w-6" />
                            </div>
                            <h3 class="text-xl font-display font-black text-white uppercase italic tracking-tighter group-hover:text-primary transition-colors leading-tight">{{ quiz.title }}</h3>
                        </div>
                        <p class="text-white/40 text-xs font-bold uppercase tracking-widest leading-relaxed line-clamp-3">{{ quiz.description }}</p>
                    </div>

                    <div class="mt-8 flex items-center justify-between pt-6 border-t border-white/5 relative z-10">
                        <div class="flex flex-col gap-1">
                            <span class="text-[8px] text-white/20 uppercase font-black tracking-[0.3em]">RÉCOMPENSE_XP</span>
                            <div class="flex items-center gap-2">
                                <Zap class="h-4 w-4 text-primary animate-pulse" />
                                <span class="text-lg font-display font-black text-white italic tracking-tighter">+{{ quiz.xp_reward }}</span>
                            </div>
                        </div>
                        
                        <HUDButton 
                            :href="route('player.quiz', quiz.id)"
                            variant="primary"
                            class="h-11 px-8 rounded-xl"
                        >
                            <div class="flex items-center gap-2">
                                <Play v-if="!isQuizCompleted(quiz.id)" class="h-4 w-4 fill-current" />
                                <CheckCircle2 v-else class="h-4 w-4" />
                                <span>{{ isQuizCompleted(quiz.id) ? 'REJOUER' : 'LANCER' }}</span>
                            </div>
                        </HUDButton>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="quizzes[activeLevel].length === 0" class="col-span-full py-24 text-center hud-glass-card rounded-[3rem] border-2 border-dashed border-white/10 max-w-4xl mx-auto">
                    <div class="h-20 w-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-8">
                        <Brain class="h-10 w-10 text-white/20" />
                    </div>
                    <div class="text-white font-display text-3xl font-black uppercase italic tracking-tighter">AUCUN_DÉFI_DISPONIBLE</div>
                    <p class="text-white/40 text-xs font-bold uppercase tracking-[0.2em] mt-4">Revenez plus tard pour de nouveaux flux de données !</p>
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.shadow-neon {
    box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
}
</style>
