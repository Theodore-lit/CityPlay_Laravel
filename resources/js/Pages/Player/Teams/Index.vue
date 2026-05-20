<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Users, UserPlus, Plus, Shield, ArrowRight, Info, Activity, Network, Target, Crown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    myTeams: Array,
    allTeams: Array,
});

const createForm = useForm({
    name: '',
});

const joinForm = useForm({
    invite_code: '',
});

const createTeam = () => {
    createForm.post(route('teams.store'), {
        onSuccess: () => createForm.reset(),
    });
};

const joinTeam = () => {
    joinForm.post(route('teams.join'), {
        onSuccess: () => joinForm.reset(),
    });
};
</script>

<template>
    <Head title="Unités Tactiques — CityPlay" />

    <SiteLayout isHUD>
        <HUDHeader />

        <div class="mx-auto max-w-7xl px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Alertes HUD Style -->
            <Transition name="fade">
                <div v-if="$page.props.flash?.success" class="mb-10 p-5 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400 text-center font-black tracking-[0.3em] uppercase text-[10px] animate-fade-up shadow-[0_0_20px_rgba(34,197,94,0.1)]">
                    {{ $page.props.flash?.success }}
                </div>
            </Transition>
            <Transition name="fade">
                <div v-if="$page.props.flash?.error" class="mb-10 p-5 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-center font-black tracking-[0.3em] uppercase text-[10px] animate-fade-up shadow-[0_0_20px_rgba(239,68,68,0.1)]">
                    {{ $page.props.flash?.error }}
                </div>
            </Transition>

            <!-- Header HUD -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div>
                    <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4">TACTICAL_UNITS_CONTROL // MULTI_LINK</div>
                    <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
                        UNITÉS <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">TACTIQUES</span>
                    </h1>
                    <p class="mt-6 text-white/40 text-xs font-bold uppercase tracking-widest max-w-xl leading-relaxed">
                        Établissez des connexions neurales avec d'autres explorateurs pour synchroniser vos missions et maximiser l'extraction de données.
                    </p>
                </div>
                <div class="px-6 py-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl flex items-center gap-4">
                    <Network class="h-4 w-4 text-primary animate-pulse" />
                    <span class="text-[10px] font-black text-white/60 uppercase tracking-widest">{{ allTeams.length }} RÉSEAUX_ACTIFS</span>
                </div>
            </div>

            <div class="grid gap-10 lg:grid-cols-3">
                <!-- ACTIONS HUD -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- CRÉER ÉQUIPE -->
                    <div class="neon-border-box p-8 overflow-hidden group">
                        <div class="neon-corner top-0 left-0 border-r-0 border-b-0 scale-75" />
                        <div class="neon-corner top-0 right-0 border-l-0 border-b-0 scale-75" />
                        
                        <h2 class="font-display text-xl text-white font-black uppercase italic tracking-tighter mb-8 flex items-center gap-3">
                            <Plus class="h-5 w-5 text-primary" /> CRÉER_RÉSEAU
                        </h2>
                        
                        <form @submit.prevent="createTeam" class="space-y-6">
                            <div class="space-y-3">
                                <label for="team_name" class="text-[9px] font-black text-white/20 uppercase tracking-[0.4em] ml-2">NOM_DE_L_UNITÉ</label>
                                <input
                                    id="team_name"
                                    v-model="createForm.name"
                                    type="text"
                                    class="w-full h-12 bg-white/5 border-2 border-white/10 rounded-xl px-5 text-white font-black tracking-widest outline-none focus:border-primary transition-all uppercase placeholder:text-white/10 text-[11px]"
                                    placeholder="IDENTIFIANT_TACTIQUE..."
                                    required
                                />
                            </div>
                            <HUDButton type="submit" variant="primary" class="w-full h-12 rounded-xl" :disabled="createForm.processing">
                                INITIALISER_CRÉATION
                            </HUDButton>
                        </form>
                    </div>

                    <!-- REJOINDRE ÉQUIPE -->
                    <div class="neon-border-box magenta p-8 overflow-hidden group">
                        <div class="neon-corner magenta top-0 left-0 border-r-0 border-b-0 scale-75" />
                        <div class="neon-corner magenta top-0 right-0 border-l-0 border-b-0 scale-75" />
                        
                        <h2 class="font-display text-xl text-white font-black uppercase italic tracking-tighter mb-8 flex items-center gap-3">
                            <UserPlus class="h-5 w-5 text-magenta-500" /> REJOINDRE_FLUX
                        </h2>
                        
                        <form @submit.prevent="joinTeam" class="space-y-6">
                            <div class="space-y-3">
                                <label for="invite_code" class="text-[9px] font-black text-white/20 uppercase tracking-[0.4em] ml-2">CODE_D_ACCÈS</label>
                                <input
                                    id="invite_code"
                                    v-model="joinForm.invite_code"
                                    type="text"
                                    class="w-full h-12 bg-white/5 border-2 border-white/10 rounded-xl px-5 text-white font-black tracking-widest outline-none focus:border-magenta-500 transition-all uppercase placeholder:text-white/10 text-[11px]"
                                    placeholder="INVITE_KEY_XXXXXX..."
                                    required
                                />
                            </div>
                            <HUDButton type="submit" variant="magenta" class="w-full h-12 rounded-xl" :disabled="joinForm.processing">
                                TENTER_SYNCHRONISATION
                            </HUDButton>
                        </form>
                    </div>
                </div>

                <!-- MY TEAMS & DISCOVERY HUD -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- MES ÉQUIPES -->
                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <Users class="h-5 w-5 text-primary" />
                            <h2 class="font-display text-2xl font-black uppercase italic tracking-tighter text-white">UNITÉS_DÉPLOYÉES</h2>
                            <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                        </div>
                        
                        <div v-if="myTeams.length > 0" class="grid gap-6 md:grid-cols-2">
                            <Link v-for="team in myTeams" :key="team.id" :href="route('teams.show', team.id)" 
                                  class="hud-glass-card group block rounded-[2.5rem] p-8 border-2 border-white/5 hover:border-primary/40 transition-all duration-700 relative overflow-hidden">
                                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                                
                                <div class="flex justify-between items-start relative z-10">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="h-1 w-1 rounded-full bg-primary animate-ping" />
                                            <span class="text-[8px] text-primary font-black uppercase tracking-[0.3em]">LINK_ACTIVE</span>
                                        </div>
                                        <h3 class="font-display text-2xl text-white group-hover:text-primary transition-colors font-black uppercase italic tracking-tighter leading-tight">{{ team.name }}</h3>
                                        <div class="text-[10px] text-white/40 mt-4 flex items-center gap-3 font-bold uppercase tracking-widest">
                                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/5 border border-white/10">
                                                <Users class="h-3.5 w-3.5" /> {{ team.members_count }} / {{ team.member_limit }}
                                            </div>
                                            <span class="text-[9px]">ID: #{{ team.invite_code }}</span>
                                        </div>
                                    </div>
                                    <div class="h-14 w-14 rounded-2xl bg-primary/10 border-2 border-primary/30 grid place-items-center text-primary shadow-[0_0_20px_rgba(6,182,212,0.2)] group-hover:scale-110 transition-transform">
                                        <ArrowRight class="h-7 w-7 group-hover:translate-x-1 transition-transform" />
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex items-center gap-3 relative z-10">
                                    <div v-if="team.creator_id === $page.props.auth.user.id" class="flex items-center gap-2 px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-500 text-[8px] font-black uppercase tracking-[0.2em] shadow-[0_0_10px_rgba(245,158,11,0.2)]">
                                        <Crown class="h-3 w-3" /> COMMANDANT
                                    </div>
                                    <div class="flex -space-x-2">
                                        <div v-for="i in Math.min(team.members_count, 4)" :key="i" class="h-6 w-6 rounded-full border border-zinc-950 bg-zinc-900 grid place-items-center text-[8px] font-black text-white/40">
                                            {{ i }}
                                        </div>
                                        <div v-if="team.members_count > 4" class="h-6 w-6 rounded-full border border-zinc-950 bg-zinc-800 grid place-items-center text-[8px] font-black text-white/40">
                                            +{{ team.members_count - 4 }}
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="p-12 text-center hud-glass-card rounded-[3rem] border-2 border-dashed border-white/5 text-white/20">
                            <Info class="h-10 w-10 mx-auto mb-4 opacity-40" />
                            <div class="text-sm font-black uppercase tracking-widest">AUCUNE_UNITÉ_RÉPERTORIÉE</div>
                        </div>
                    </section>

                    <!-- DÉCOUVRIR DES ÉQUIPES -->
                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <Activity class="h-5 w-5 text-magenta-500" />
                            <h2 class="font-display text-2xl font-black uppercase italic tracking-tighter text-white">UNITÉS_POPULAIRES</h2>
                            <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                        </div>
                        
                        <div class="grid gap-6 md:grid-cols-2">
                            <div v-for="team in allTeams" :key="team.id" 
                                 class="hud-glass-card rounded-2xl p-6 border border-white/5 relative overflow-hidden group">
                                <div class="flex justify-between items-center relative z-10">
                                    <div>
                                        <h3 class="font-display text-lg text-white font-black uppercase italic tracking-tight group-hover:text-primary transition-colors">{{ team.name }}</h3>
                                        <div class="text-[9px] text-white/40 mt-1 font-bold uppercase tracking-widest flex items-center gap-2">
                                            <Users class="h-3 w-3" /> {{ team.members_count }} MEMBRES_SYNCHRONISÉS
                                        </div>
                                    </div>
                                    <div v-if="team.members_count < team.member_limit" class="px-3 py-1 rounded-lg bg-primary/10 border border-primary/20 text-primary text-[8px] font-black uppercase tracking-widest animate-pulse">
                                        ACCÈS_LIBRE
                                    </div>
                                    <div v-else class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-white/20 text-[8px] font-black uppercase tracking-widest">
                                        RÉSEAU_PLEIN
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
