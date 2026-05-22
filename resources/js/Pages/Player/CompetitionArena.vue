<script setup>
// kamal
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Trophy, Target, Calendar, Award, ChevronLeft, MapPin, Zap, Clock, Info, Heart, Gem, ArrowRight, TrendingUp, Users, Plus
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { cn } from '@/lib/utils';

import confetti from 'canvas-confetti';

const props = defineProps({
    competition: Object,
    leaderboard: Array,
    userProgress: Number,
    isParticipating: Boolean,
});

const isGoalReached = computed(() => {
    if (props.competition.type !== 'fixed') return false;
    return props.userProgress >= props.competition.goal_amount;
});

// Modal Charger
const showChargeModal = ref(false);

const submitCharge = () => {
    chargeForm.post(route('player.competitions.charge', props.competition.id), {
        onSuccess: () => {
            showChargeModal.value = false;
            // Vérifier si la victoire a été déclenchée (is_winner passe à true dans la réponse)
            // L'animation de victoire est déclenchée ici pour un feedback immédiat
            if (props.isParticipating && !props.competition.participants?.find(p => p.id === usePage().props.auth.user.id)?.pivot?.is_winner) {
                // On peut vérifier via les flash messages ou le changement de state
            }
        },
        preserveScroll: true
    });
};

/**
 * Logique de surveillance de la victoire.
 * Déclenche l'overlay de succès avec GSAP et confettis quand l'objectif est atteint.
 */
const showWinAnimation = ref(false);
// Cette animation est normalement déclenchée par un changement de statut côté serveur
// ou lors du montage si le lot vient d'être gagné.

const triggerWinEffects = () => {
    showWinAnimation.value = true;
    confetti({
        particleCount: 150,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#0070ff', '#00f2ff', '#ffffff']
    });

    // Fermer l'animation après 5 secondes
    setTimeout(() => {
        showWinAnimation.value = false;
    }, 5000);
};

const joinCompetition = () => {
    useForm({}).post(route('player.competitions.join', props.competition.id));
};

// Logic du décompteur
const timeLeft = ref('');
let timerInterval = null;

const updateTimer = () => {
    const end = new Date(props.competition.end_date);
    const now = new Date();
    const diff = end - now;

    if (diff <= 0) {
        timeLeft.value = "TERMINÉ";
        clearInterval(timerInterval);
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    timeLeft.value = `${days}j ${hours}h ${minutes}m ${seconds}s`;
};

const chargeForm = useForm({
    amount: 10,
});

onMounted(() => {
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
});

// Style "eau de roche"
const glassClass = "bg-white/5 backdrop-blur-2xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]";
</script>

<template>
    <Head :title="`Arène : ${competition.title}`" />

    <SiteLayout>
        <div class="mx-auto max-w-5xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 animate-in fade-in slide-in-from-top-4 duration-700">
                <div class="flex items-center gap-6">
                    <Link :href="route('player.competitions')" :class="cn('h-12 w-12 rounded-2xl grid place-items-center text-primary hover:scale-110 transition-all', glassClass)">
                        <ChevronLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Competition Arena</div>
                        <h1 class="font-display text-3xl md:text-4xl text-foreground uppercase italic font-black">{{ competition.title }}</h1>
                        <div class="flex items-center gap-3 mt-2">
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10">
                                <MapPin class="h-3 w-3 text-primary" />
                                <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ competition.city_event?.city?.name }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 border border-primary/20">
                                <Clock class="h-3 w-3 text-primary" />
                                <span class="text-[10px] font-black text-primary uppercase tracking-wider">{{ timeLeft }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!isParticipating">
                    <NeonButton @click="joinCompetition" variant="primary" size="lg" class="shadow-neon px-12">
                        PARTICIPATE NOW
                    </NeonButton>
                </div>
            </div>

            <div v-if="isParticipating" class="grid gap-8 lg:grid-cols-3 animate-in fade-in slide-in-from-bottom-4 duration-1000">
                <!-- Main Stats/Progress -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Progress Section -->
                    <div :class="cn('rounded-[3rem] p-8 md:p-10 relative overflow-hidden', glassClass)">
                        <div class="absolute top-0 right-0 p-8 opacity-5">
                            <TrendingUp class="h-32 w-32" />
                        </div>

                        <div class="relative z-10">
                            <h2 class="font-display text-xl text-foreground uppercase italic font-black mb-8 flex items-center gap-3">
                                <Target class="h-5 w-5 text-primary" />
                                {{ competition.type === 'ranking' ? 'Votre Performance' : 'Progression de l\'objectif' }}
                            </h2>

                            <!-- Case Fixed Goal -->
                            <div v-if="competition.type === 'fixed'" class="space-y-6">
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1">Score Actuel</p>
                                        <p class="text-4xl font-display font-black text-foreground">
                                            {{ userProgress }} <span class="text-lg text-white/40">/ {{ competition.goal_amount }}</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-1">Status</p>
                                        <p class="text-sm font-bold" :class="userProgress >= competition.goal_amount ? 'text-emerald-400' : 'text-amber-400'">
                                            {{ userProgress >= competition.goal_amount ? 'OBJECTIF ATTEINT' : 'EN COURS' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="h-4 w-full bg-white/5 rounded-full overflow-hidden border border-white/5 p-1">
                                    <div class="h-full bg-gradient-to-r from-primary to-primary-foreground rounded-full transition-all duration-1000 shadow-neon"
                                         :style="{ width: `${Math.min(100, (userProgress / competition.goal_amount) * 100)}%` }" />
                                </div>
                            </div>

                            <!-- Case Ranking -->
                            <div v-else class="space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="p-6 rounded-[2rem] bg-white/5 border border-white/5">
                                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Votre Score</p>
                                        <p class="text-3xl font-display font-black text-foreground">{{ userProgress }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <Zap v-if="competition.objective_type === 'xp'" class="h-4 w-4 text-primary" />
                                            <Heart v-else-if="competition.objective_type === 'hearts'" class="h-4 w-4 text-red-500" />
                                            <Gem v-else class="h-4 w-4 text-cyan-400" />
                                            <span class="text-[10px] font-black text-white/60 uppercase">{{ competition.objective_type }}</span>
                                        </div>
                                    </div>
                                    <div class="p-6 rounded-[2rem] bg-white/5 border border-white/5">
                                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Position</p>
                                        <p class="text-3xl font-display font-black text-primary">#{{ leaderboard.find(p => p.id === $page.props.auth.user?.id)?.rank || '?' }}</p>
                                        <p class="text-[10px] font-bold text-white/60 uppercase mt-2">Sur {{ leaderboard.length }} participants</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex justify-center">
                                <NeonButton @click="showChargeModal = true" variant="primary" class="px-10 h-14 shadow-neon" :disabled="isGoalReached">
                                    <Plus v-if="!isGoalReached" class="h-5 w-5 mr-2" />
                                    {{ isGoalReached ? 'OBJECTIF ATTEINT' : 'CHARGER L\'OBJECTIF' }}
                                </NeonButton>
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard Section (Always show for ranking, optional for fixed) -->
                    <div :class="cn('rounded-[3rem] p-8 md:p-10', glassClass)">
                        <h2 class="font-display text-xl text-foreground uppercase italic font-black mb-8 flex items-center gap-3">
                            <Users class="h-5 w-5 text-primary" /> Classement des Participants
                        </h2>

                        <div class="space-y-3">
                            <div v-for="player in leaderboard" :key="player.id"
                                 :class="cn(
                                    'flex items-center justify-between p-4 px-6 rounded-2xl transition-all duration-500',
                                    player.id === $page.props.auth.user?.id ? 'bg-primary/10 border border-primary/30 shadow-neon-sm scale-[1.02]' : 'bg-white/5 border border-white/5 hover:bg-white/10'
                                 )">
                                <div class="flex items-center gap-4">
                                    <div :class="cn(
                                        'h-10 w-10 rounded-xl font-display font-black grid place-items-center text-sm',
                                        player.rank <= competition.ranking_limit ? 'bg-amber-500/20 text-amber-500 border border-amber-500/30' : 'bg-white/5 text-white/40'
                                    )">
                                        #{{ player.rank }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-foreground uppercase tracking-tight">{{ player.name }}</p>
                                        <p v-if="player.id === $page.props.auth.user?.id" class="text-[8px] font-black text-primary uppercase tracking-widest">Vous</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-display font-black text-foreground">{{ player.pivot.current_amount }}</p>
                                    <p class="text-[8px] font-bold text-muted-foreground uppercase">{{ competition.objective_type }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rewards & Rules -->
                <div class="space-y-8">
                    <div :class="cn('rounded-[2.5rem] p-8', glassClass)">
                        <h3 class="font-display text-sm text-primary uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <Award class="h-4 w-4" /> Récompense
                        </h3>
                        <div class="p-6 rounded-[2rem] bg-emerald-500/5 border border-emerald-500/10 text-center">
                            <div class="h-16 w-16 mx-auto rounded-2xl bg-emerald-500/10 grid place-items-center text-emerald-400 mb-4 shadow-emerald">
                                <Award class="h-8 w-8" />
                            </div>
                            <p class="text-sm font-black text-foreground uppercase leading-relaxed">{{ competition.reward_description || 'Pack Mystère' }}</p>
                            <p class="text-[10px] text-muted-foreground mt-2 italic">Délivré automatiquement à la fin du décompte.</p>
                        </div>
                    </div>

                    <div :class="cn('rounded-[2.5rem] p-8', glassClass)">
                        <h3 class="font-display text-sm text-primary uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <Info class="h-4 w-4" /> Détails du Défi
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-white/5">
                                <span class="text-[10px] font-black text-muted-foreground uppercase">Type</span>
                                <span class="text-[10px] font-black text-foreground uppercase">{{ competition.type === 'ranking' ? 'Classement' : 'Objectif Fixe' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-white/5">
                                <span class="text-[10px] font-black text-muted-foreground uppercase">Ressource</span>
                                <span class="text-[10px] font-black text-foreground uppercase">{{ competition.objective_type }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3">
                                <span class="text-[10px] font-black text-muted-foreground uppercase">Gagnants</span>
                                <span class="text-[10px] font-black text-foreground uppercase">
                                    {{ competition.type === 'ranking' ? `Top ${competition.ranking_limit}` : 'Tous les participants' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intro / Details if not participating -->
            <div v-else class="max-w-3xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-1000">
                <div :class="cn('rounded-[3rem] p-10 md:p-16 text-center', glassClass)">
                    <div class="h-24 w-24 mx-auto rounded-[2rem] bg-primary/10 grid place-items-center text-primary mb-8 shadow-neon">
                        <Trophy v-if="competition.type === 'ranking'" class="h-12 w-12" />
                        <Target v-else class="h-12 w-12" />
                    </div>
                    <h2 class="font-display text-2xl md:text-3xl text-foreground uppercase italic font-black mb-6">Briefing de Mission</h2>
                    <p class="text-muted-foreground leading-relaxed mb-10 italic">"{{ competition.description }}"</p>

                    <div class="grid grid-cols-2 gap-6 mb-10">
                        <div class="p-6 rounded-[2rem] bg-white/5 border border-white/5">
                            <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-2">Objectif</p>
                            <p class="text-sm font-bold text-foreground uppercase">
                                {{ competition.type === 'ranking' ? `Atteindre le Top ${competition.ranking_limit}` : `Collecter ${competition.goal_amount} ${competition.objective_type}` }}
                            </p>
                        </div>
                        <div class="p-6 rounded-[2rem] bg-white/5 border border-white/5">
                            <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-2">Temps Restant</p>
                            <p class="text-sm font-bold text-foreground uppercase">{{ timeLeft }}</p>
                        </div>
                    </div>

                    <NeonButton @click="joinCompetition" variant="primary" size="lg" class="w-full h-16 shadow-neon text-lg tracking-[0.2em]">
                        DÉPLOYER LE PROTOCOLE
                    </NeonButton>
                </div>
            </div>
        </div>

        <!-- CHARGE MODAL -->
        <Modal :show="showChargeModal" @close="showChargeModal = false">
            <div class="p-8 bg-gaming-darker border border-primary/20 rounded-[3rem] overflow-hidden relative">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

                <div class="relative z-10">
                    <h2 class="font-display text-2xl text-white mb-2 uppercase italic font-black tracking-tight">Charger l'Objectif</h2>
                    <p class="text-muted-foreground text-xs mb-8 uppercase tracking-widest font-bold">Injectez vos ressources pour progresser</p>

                    <div class="space-y-6">
                        <div class="p-6 rounded-[2rem] bg-white/5 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="cn('h-12 w-12 rounded-xl grid place-items-center',
                                    competition.objective_type === 'xp' ? 'bg-primary/10 text-primary' :
                                    competition.objective_type === 'hearts' ? 'bg-red-500/10 text-red-500' : 'bg-cyan-400/10 text-cyan-400'
                                )">
                                    <Zap v-if="competition.objective_type === 'xp'" class="h-6 w-6" />
                                    <Heart v-else-if="competition.objective_type === 'hearts'" class="h-6 w-6" />
                                    <Gem v-else class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-muted-foreground uppercase">Disponible</p>
                                    <p class="text-xl font-display font-black text-white">
                                        {{ $page.props.auth.user?.[competition.objective_type] || 0 }} {{ competition.objective_type.toUpperCase() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-primary uppercase tracking-[0.2em] ml-2">Montant à injecter</label>
                            <input type="number" v-model="chargeForm.amount" min="1" :max="$page.props.auth.user?.[competition.objective_type]"
                                   class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-6 text-xl font-display font-black text-white outline-none focus:border-primary/50 transition-all" />
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button @click="showChargeModal = false" class="flex-1 h-14 rounded-2xl bg-white/5 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">Annuler</button>
                            <NeonButton @click="submitCharge" variant="primary" class="flex-1 h-14 shadow-neon" :disabled="chargeForm.processing || chargeForm.amount <= 0 || chargeForm.amount > ($page.props.auth.user?.[competition.objective_type] || 0)">
                                CONFIRMER
                            </NeonButton>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <MobileTabBar />

        <!-- WIN ANIMATION OVERLAY -->
        <div v-if="showWinAnimation" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-gaming-dark/60 backdrop-blur-sm animate-in fade-in duration-500">
            <div class="bg-white/10 backdrop-blur-2xl border border-primary/40 rounded-[3.5rem] p-12 text-center max-w-lg shadow-[0_0_50px_rgba(0,112,255,0.3)] animate-in zoom-in-95 duration-500">
                <div class="h-24 w-24 mx-auto bg-primary/20 rounded-3xl grid place-items-center mb-8 shadow-neon">
                    <Trophy class="h-12 w-12 text-primary" />
                </div>
                <h2 class="font-display text-4xl text-white uppercase italic font-black mb-4 tracking-tighter">VICTOIRE !</h2>
                <p class="text-xl text-primary font-black uppercase tracking-widest mb-6">Objectif Atteint</p>
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 mb-8">
                    <p class="text-white text-sm leading-relaxed italic">
                        Félicitations explorateur ! Vous avez complété ce défi avec succès. Votre lot vous attend désormais dans votre coffre.
                    </p>
                </div>
                <Link :href="route('player.rewards.index')" class="inline-block w-full">
                    <NeonButton variant="primary" class="w-full h-16 shadow-neon text-lg tracking-widest">
                        VOIR MES RÉCOMPENSES
                    </NeonButton>
                </Link>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.shadow-emerald { box-shadow: 0 0 30px rgba(16, 185, 129, 0.1); }
.shadow-neon-sm { box-shadow: 0 0 15px rgba(0, 112, 255, 0.2); }
</style>
