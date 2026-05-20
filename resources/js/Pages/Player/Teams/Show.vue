<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { Users, MapPin, Play, Trophy, Shield, Calendar, ArrowLeft, Copy, Check, Share2, Link as LinkIcon, Activity, Crown, Network, Target } from 'lucide-vue-next';
import { ref } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    team: Object,
    availableCities: Array,
});

const startQuestForm = useForm({});
const copied = ref(false);
const gameLinkCopied = ref(false);

const copyInviteCode = () => {
    navigator.clipboard.writeText(props.team.invite_code);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

const copyGameLink = (cityId) => {
    const url = `${window.location.origin}/teams/${props.team.id}/join-game/${cityId}`;
    navigator.clipboard.writeText(url);
    gameLinkCopied.value = cityId;
    setTimeout(() => gameLinkCopied.value = false, 2000);
};

const startQuest = (cityId) => {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            router.post(route('teams.start-quest', { team: props.team.id, city: cityId }), {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });
        }, () => {
            router.post(route('teams.start-quest', { team: props.team.id, city: cityId }));
        });
    } else {
        router.post(route('teams.start-quest', { team: props.team.id, city: cityId }));
    }
};
</script>

<template>
    <Head :title="`Unité ${team.name} — CityPlay`" />

    <SiteLayout isHUD>
        <HUDHeader />

        <div class="mx-auto max-w-7xl px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Alertes HUD Style -->
            <Transition name="fade">
                <div v-if="$page.props.flash?.success" class="mb-10 p-5 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400 text-center font-black tracking-[0.3em] uppercase text-[10px] animate-fade-up shadow-[0_0_20px_rgba(34,197,94,0.1)]">
                    {{ $page.props.flash?.success }}
                </div>
            </Transition>

            <div class="mb-12">
                <Link :href="route('teams.index')" class="text-white/40 hover:text-primary text-[10px] font-black uppercase tracking-[0.3em] flex items-center gap-2 transition-all hover:translate-x-[-4px]">
                    <ArrowLeft class="h-4 w-4" /> RETOUR_RÉSEAUX
                </Link>
            </div>

            <div class="grid gap-10 lg:grid-cols-3">
                <!-- TEAM INFO HUD -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="neon-border-box p-8 overflow-hidden group">
                        <div class="neon-corner top-0 left-0 border-r-0 border-b-0 scale-75" />
                        <div class="neon-corner top-0 right-0 border-l-0 border-b-0 scale-75" />
                        
                        <div class="relative z-10">
                            <div class="h-20 w-20 rounded-2xl bg-primary/10 border-2 border-primary/40 grid place-items-center shadow-[0_0_30px_rgba(6,182,212,0.2)] mb-8 relative overflow-hidden">
                                <div class="absolute inset-0 bg-primary/5 animate-pulse" />
                                <Users class="h-10 w-10 text-primary drop-shadow-[0_0_8px_#06b6d4]" />
                            </div>
                            
                            <div class="text-[9px] text-primary font-black uppercase tracking-[0.4em] mb-2 flex items-center gap-2">
                                <Activity class="h-3 w-3 animate-pulse" /> LINK_ESTABLISHED
                            </div>
                            <h1 class="font-display text-3xl md:text-4xl text-white font-black uppercase italic tracking-tighter leading-tight mb-8">{{ team.name }}</h1>
                            
                            <div class="p-5 rounded-2xl bg-white/5 border-2 border-white/10 group-hover:border-primary/30 transition-all">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-[8px] text-white/40 uppercase font-black tracking-widest mb-1">ACCESS_KEY</div>
                                        <div class="text-primary font-display text-xl tracking-[0.2em] font-black italic">{{ team.invite_code }}</div>
                                    </div>
                                    <button @click="copyInviteCode" class="h-12 w-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary hover:bg-primary/20 transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
                                        <Check v-if="copied" class="h-5 w-5" />
                                        <Copy v-else class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-10 space-y-6">
                                <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-white/60">
                                    <Shield class="h-4 w-4 text-primary" />
                                    <span>LEADER: <span class="text-white ml-2">{{ team.members.find(m => m.pivot.role === 'leader')?.name }}</span></span>
                                </div>
                                <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-white/60">
                                    <Users class="h-4 w-4 text-primary" />
                                    <span>CAPACITÉ: <span class="text-white ml-2">{{ team.members.length }} / {{ team.member_limit }} NODES</span></span>
                                </div>
                                <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-white/60">
                                    <Calendar class="h-4 w-4 text-primary" />
                                    <span>SYNCHRONISÉ: <span class="text-white ml-2">{{ new Date(team.created_at).toLocaleDateString() }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MEMBRES HUD -->
                    <div class="hud-glass-card rounded-[2.5rem] p-8 border-2 border-white/5">
                        <div class="flex items-center gap-3 mb-8">
                            <Network class="h-5 w-5 text-primary" />
                            <h2 class="font-display text-xl text-white font-black uppercase italic tracking-tighter">UNIT_NODES</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="member in team.members" :key="member.id" 
                                 class="flex items-center justify-between p-4 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-primary/20 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-zinc-900 border border-white/10 flex items-center justify-center text-[10px] font-black text-primary group-hover:border-primary/40 transition-all relative overflow-hidden">
                                        <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover:opacity-100 transition-opacity" />
                                        {{ member.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white uppercase italic tracking-tight group-hover:text-primary transition-colors">{{ member.name }}</div>
                                        <div class="text-[8px] text-white/30 uppercase font-bold tracking-widest mt-1">{{ member.pivot.role }}</div>
                                    </div>
                                </div>
                                <div class="text-[10px] font-display font-black text-primary italic tracking-tighter">{{ member.xp }} XP</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS & HISTORY HUD -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- LANCER UNE QUÊTE HUD -->
                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <Play class="h-5 w-5 text-primary" />
                            <h2 class="font-display text-2xl font-black uppercase italic tracking-tighter text-white">INITIALISER_MISSION_GROUPE</h2>
                            <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                        </div>

                        <div class="grid gap-8 md:grid-cols-2">
                            <div v-for="city in availableCities" :key="city.id" 
                                 class="hud-glass-card rounded-[2.5rem] overflow-hidden border-2 border-white/5 group relative">
                                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                                
                                <div class="p-8 relative z-10">
                                    <div class="flex justify-between items-start mb-6">
                                        <h3 class="font-display text-2xl text-white font-black uppercase italic tracking-tighter group-hover:text-primary transition-colors">{{ city.name }}</h3>
                                        <div class="h-12 w-12 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-primary group-hover:border-primary/40 transition-all">
                                            <MapPin class="h-6 w-6" />
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-white/40 font-bold uppercase tracking-widest leading-relaxed line-clamp-2 mb-10">{{ city.description }}</p>
                                    
                                    <div class="flex gap-4">
                                        <HUDButton @click="startQuest(city.id)" variant="primary" class="flex-1 h-12 rounded-xl" :disabled="startQuestForm.processing">
                                            <div class="flex items-center gap-2">
                                                <Target class="h-4 w-4" />
                                                <span>DÉPLOYER_UNITÉ</span>
                                            </div>
                                        </HUDButton>
                                        <button 
                                            @click="copyGameLink(city.id)" 
                                            class="h-12 w-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:border-primary/50 hover:text-primary transition-all relative overflow-hidden group/link"
                                            title="Lien de partage de la partie"
                                        >
                                            <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover/link:opacity-100 transition-opacity" />
                                            <Check v-if="gameLinkCopied === city.id" class="h-5 w-5 text-green-400 relative z-10" />
                                            <LinkIcon v-else class="h-5 w-5 relative z-10" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- HISTORIQUE HUD -->
                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <Trophy class="h-5 w-5 text-magenta-500" />
                            <h2 class="font-display text-2xl font-black uppercase italic tracking-tighter text-white">LOG_D_ARCHIVES_TACTIQUES</h2>
                            <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                        </div>

                        <div v-if="team.game_sessions?.length > 0" class="space-y-4">
                            <div v-for="session in team.game_sessions" :key="session.id" 
                                 class="neon-border-box p-6 rounded-[2rem] flex items-center justify-between group overflow-hidden">
                                <div class="flex items-center gap-6 relative z-10">
                                    <div class="h-14 w-14 rounded-2xl bg-magenta-500/10 border-2 border-magenta-500/30 grid place-items-center text-magenta-500 shadow-[0_0_20px_rgba(217,70,239,0.2)]">
                                        <MapPin class="h-7 w-7" />
                                    </div>
                                    <div>
                                        <div class="text-xl font-display font-black text-white uppercase italic tracking-tighter group-hover:text-magenta-500 transition-colors">{{ session.city?.name }}</div>
                                        <div class="text-[9px] text-white/40 font-bold uppercase tracking-widest mt-1">
                                            {{ new Date(session.created_at).toLocaleDateString() }} // STATUS: {{ session.status.toUpperCase() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right relative z-10">
                                    <div class="text-2xl font-display text-primary font-black italic tracking-tighter">+{{ session.total_score || 0 }} PX</div>
                                    <div class="text-[8px] text-white/20 uppercase font-black tracking-widest mt-1">TOTAL_EXTRACTION</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-16 text-center hud-glass-card rounded-[3rem] border-2 border-dashed border-white/5 text-white/20 italic">
                            <div class="text-sm font-black uppercase tracking-[0.4em]">AUCUN_ENREGISTREMENT_TROUVÉ</div>
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
