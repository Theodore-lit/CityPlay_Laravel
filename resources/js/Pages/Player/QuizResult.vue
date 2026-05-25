<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Heart, Star, Trophy, Frown } from 'lucide-vue-next';

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
    <Head :title="`Résultats - ${quiz?.title || 'Quiz'}`" />

    <SiteLayout hideFooter>
        <div class="mx-auto max-w-3xl px-4 py-12 text-center animate-fade-up">
            <div class="mb-8">
                <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">{{ quiz?.category }}</p>
                <h1 class="font-display text-xl text-white">{{ quiz?.title }}</h1>
            </div>

            <div class="h-24 w-24 rounded-3xl bg-gradient-electric mx-auto grid place-items-center shadow-neon mb-6 animate-bounce">
                <Trophy v-if="(result?.stars ?? 0) >= 2" class="h-12 w-12 text-white" />
                <Frown v-else class="h-12 w-12 text-white" />
            </div>

            <h2 class="font-display text-4xl text-white mb-2 uppercase">
                {{ (result?.stars ?? 0) >= 2 ? 'FÉLICITATIONS !' : 'COURAGE !' }}
            </h2>
            <p class="text-muted-foreground text-sm mb-10">
                {{ (result?.stars ?? 0) >= 2 ? 'Vous avez brillamment surmonté les épreuves.' : 'Continuez à vous entraîner pour décrocher toutes les étoiles.' }}
            </p>

            <div v-if="!result?.failed" class="flex justify-center gap-3 mb-10">
                <Star
                    v-for="s in 3"
                    :key="s"
                    :class="[
                        'h-12 w-12 transition-all duration-700',
                        s <= (result?.stars ?? 0) ? 'text-yellow-400 fill-yellow-400 scale-110' : 'text-white/5'
                    ]"
                    :style="{ transitionDelay: `${s * 200}ms` }"
                />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase mb-1">Score</div>
                    <div class="text-2xl font-display text-white">{{ result?.score }}/{{ result?.total }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase mb-1">XP GAGNÉS</div>
                    <div class="text-2xl font-display text-electric">+{{ result?.xp_earned ?? 0 }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase mb-1">INDICES</div>
                    <div class="text-2xl font-display text-warning">{{ result?.hints_used ?? 0 }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase mb-1">TEMPS RESTANT</div>
                    <div class="text-2xl font-display text-cyan-neon">{{ result?.time_left ?? 0 }}s</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button
                    @click="handleRetry"
                    class="w-full sm:w-auto px-8 py-3 rounded-xl border-2 border-white/10 text-white font-bold hover:bg-white/5 transition-all flex items-center justify-center gap-2 group"
                >
                    Réessayer
                </button>
                <Link :href="backUrl">
                    <NeonButton variant="outline" class="w-full sm:w-auto">
                        {{ city ? 'Retour au lobby' : 'Revenir aux villes' }}
                    </NeonButton>
                </Link>
            </div>
        </div>
    </SiteLayout>
</template>
