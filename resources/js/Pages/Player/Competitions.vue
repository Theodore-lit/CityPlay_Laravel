<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Trophy, Target, Calendar, Award, ChevronLeft, MapPin, Zap, Clock, Info, Heart, Gem, ArrowRight
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    competitions: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'short'
    });
};

const getDaysLeft = (dateString) => {
    const end = new Date(dateString);
    const now = new Date();
    const diffTime = end - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
};

// Style "eau de roche"
const cardClass = "group relative overflow-hidden rounded-[2.5rem] bg-white/5 backdrop-blur-2xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-8 transition-all duration-500 hover:border-primary/40 hover:scale-[1.01]";
</script>

<template>
    <Head title="Compétitions Actives — CityPlay" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="flex items-center gap-6 mb-12">
                <Link :href="route('player.hub')" class="h-12 w-12 rounded-2xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] grid place-items-center text-primary hover:scale-110 transition-all">
                    <ChevronLeft class="h-6 w-6" />
                </Link>
                <div>
                    <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Global Arena</div>
                    <h1 class="font-display text-4xl text-foreground uppercase italic font-black">Live <span class="text-white/40">Events</span></h1>
                </div>
            </div>

            <div v-if="competitions.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <Link v-for="comp in competitions" :key="comp.id"
                     :href="route('player.competitions.show', comp.id)"
                     :class="cardClass">
                    
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2 text-[10px] font-black text-primary uppercase tracking-widest">
                            <Clock class="h-3.5 w-3.5" /> {{ getDaysLeft(comp.end_date) }} jours restants
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-primary/10 grid place-items-center text-primary">
                            <Trophy v-if="comp.type === 'ranking'" class="h-5 w-5" />
                            <Target v-else class="h-5 w-5" />
                        </div>
                    </div>

                    <h2 class="font-display text-2xl text-foreground uppercase italic font-black tracking-tight mb-2 group-hover:text-primary transition-colors">
                        {{ comp.title }}
                    </h2>
                    
                    <div class="flex items-center gap-2 mb-4 text-[10px] font-bold text-muted-foreground uppercase">
                        <MapPin class="h-3 w-3" /> {{ comp.city_event?.city?.name || 'District' }}
                    </div>

                    <p class="text-xs text-muted-foreground leading-relaxed line-clamp-2 mb-8">{{ comp.description }}</p>

                    <!-- PROGRESS BAR (Seulement pour type fixed) -->
                    <div v-if="comp.type === 'fixed'" class="space-y-3 mb-8">
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-black text-foreground/40 uppercase tracking-widest">Progression</span>
                            <span class="text-xs font-black text-primary">{{ comp.progress || 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden border border-white/5 p-[1px]">
                            <div class="h-full bg-gradient-to-r from-primary to-primary-foreground rounded-full transition-all duration-1000 shadow-neon" 
                                 :style="{ width: `${comp.progress || 0}%` }" />
                        </div>
                        <div class="flex items-center gap-2 text-[9px] font-bold text-muted-foreground">
                            <Target class="h-3 w-3" /> Objectif: {{ comp.goal_amount }} {{ comp.objective_type.toUpperCase() }}
                        </div>
                    </div>

                    <!-- RANKING BADGE (Seulement pour type ranking) -->
                    <div v-else class="mb-8">
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/5 border border-white/5 group-hover:border-primary/20 transition-colors">
                            <div class="h-10 w-10 rounded-xl bg-amber-500/10 grid place-items-center text-amber-500">
                                <Award class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Défi de Classement</p>
                                <p class="text-xs font-bold text-foreground">Top {{ comp.ranking_limit }} Récompensé</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-white/5">
                        <div class="flex items-center gap-2">
                            <Zap v-if="comp.objective_type === 'xp'" class="h-4 w-4 text-primary" />
                            <Heart v-else-if="comp.objective_type === 'hearts'" class="h-4 w-4 text-red-500" />
                            <Gem v-else class="h-4 w-4 text-cyan-400" />
                            <span class="text-[10px] font-black text-foreground/60 uppercase tracking-tighter">{{ comp.objective_type.toUpperCase() }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-primary uppercase tracking-[0.2em]">
                            Accéder <ArrowRight class="h-3 w-3" />
                        </div>
                    </div>

                    <!-- Ambient Glow -->
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-primary/5 blur-3xl group-hover:bg-primary/10 transition-colors" />
                </Link>
            </div>

            <div v-else class="text-center py-20 bg-white/5 backdrop-blur-xl rounded-[3rem] border border-dashed border-white/10 shadow-inner">
                <Trophy class="h-12 w-12 mx-auto text-muted-foreground/20 mb-4" />
                <p class="text-muted-foreground uppercase font-black tracking-widest">Aucune compétition active pour le moment.</p>
                <p class="text-[10px] text-muted-foreground/60 mt-2">Revenez bientôt pour de nouveaux défis !</p>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
