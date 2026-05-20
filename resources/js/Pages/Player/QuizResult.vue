<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Heart, Star, Trophy, Frown, ArrowRight, Zap, Clock, Info, Activity } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    quiz: Object,
    city: Object,
    result: Object,
});

const page = usePage();

const handleRetry = () => {
    router.visit(route('player.quiz', props.quiz.id));
};

const backUrl = props.city
    ? route('player.game', props.city.id)
    : route('player.cities');
</script>

<template>
    <Head :title="`Rapport de Mission - ${quiz?.title || 'Quiz'}`" />

    <SiteLayout isHUD>
        <HUDHeader />

        <div class="mx-auto max-w-4xl px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="text-center animate-fade-up">
                <div class="mb-12">
                    <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4">MISSION_DEBRIEFING // DATA_SYNC_COMPLETE</div>
                    <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
                        RAPPORT DE <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">MISSION</span>
                    </h1>
                </div>

                <div class="h-32 w-32 rounded-[2.5rem] bg-primary/10 border-2 border-primary/40 mx-auto grid place-items-center shadow-[0_0_40px_rgba(6,182,212,0.2)] mb-8 animate-bounce relative overflow-hidden">
                    <div class="absolute inset-0 bg-primary/5 animate-ping rounded-[2.5rem]" />
                    <Trophy v-if="(result?.stars ?? 0) >= 2" class="h-14 w-14 text-primary relative z-10 drop-shadow-[0_0_12px_#06b6d4]" />
                    <Frown v-else class="h-14 w-14 text-white/40 relative z-10" />
                </div>

                <h2 class="font-display text-4xl md:text-5xl text-white mb-4 uppercase italic font-black tracking-tighter">
                    {{ (result?.stars ?? 0) >= 2 ? 'SYNCHRONISATION_RÉUSSIE' : 'ÉCHEC_DE_SYNCHRONISATION' }}
                </h2>
                <p class="text-white/40 text-sm font-bold uppercase tracking-[0.4em] mb-12 max-w-2xl mx-auto">
                    {{ (result?.stars ?? 0) >= 2 ? 'L\'extraction des données culturelles a été effectuée avec succès. Votre profil a été mis à jour.' : 'Le flux de données a été corrompu. Une nouvelle tentative de synchronisation est recommandée.' }}
                </p>

                <div v-if="!result?.failed" class="flex justify-center gap-6 mb-16">
                    <Star
                        v-for="s in 3"
                        :key="s"
                        :class="[
                            'h-16 w-16 transition-all duration-1000 drop-shadow-[0_0_15px_rgba(234,179,8,0.5)]',
                            s <= (result?.stars ?? 0) ? 'text-amber-400 fill-amber-400 scale-110' : 'text-white/5'
                        ]"
                        :style="{ transitionDelay: `${s * 300}ms` }"
                    />
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16 max-w-4xl mx-auto">
                    <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-primary/40 transition-all">
                        <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">DATA_NODES</div>
                        <div class="text-3xl font-display text-white font-black italic tracking-tighter">{{ result?.score }} / {{ result?.total }}</div>
                    </div>
                    <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-primary/40 transition-all">
                        <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">XP_EXTRACTION</div>
                        <div class="text-3xl font-display text-primary font-black italic tracking-tighter">+{{ result?.xp_earned ?? 0 }}</div>
                    </div>
                    <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-magenta-500/40 transition-all">
                        <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">HINTS_USED</div>
                        <div class="text-3xl font-display text-magenta-500 font-black italic tracking-tighter">{{ result?.hints_used ?? 0 }}</div>
                    </div>
                    <div class="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10 group hover:border-cyan-400/40 transition-all">
                        <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-3">REMAINING_TIME</div>
                        <div class="text-3xl font-display text-cyan-400 font-black italic tracking-tighter">{{ result?.time_left ?? 0 }}S</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <button
                        @click="handleRetry"
                        class="flex-1 max-w-xs h-16 rounded-2xl border-2 border-white/10 text-white font-black uppercase tracking-[0.3em] text-[10px] hover:bg-white/5 transition-all"
                    >
                        RÉINITIALISER_SEQUENCE
                    </button>
                    <HUDButton :href="backUrl" variant="primary" class="h-16 px-10 rounded-2xl flex-1 max-w-xs">
                        <div class="flex items-center gap-3">
                            <span>{{ city ? 'RETOUR_AU_LOBBY' : 'RETOUR_SECTEURS' }}</span>
                            <ArrowRight class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </HUDButton>
                </div>
            </div>
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
