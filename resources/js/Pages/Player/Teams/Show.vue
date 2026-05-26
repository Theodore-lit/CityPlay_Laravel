<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { Users, MapPin, Play, Trophy, Shield, Calendar, ArrowLeft, Copy, Check, Share2, Link as LinkIcon } from 'lucide-vue-next';
import { ref } from 'vue';

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
            }, {
                onSuccess: () => {
                    // La notification sera gérée côté serveur via flash ou broadcast
                }
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
    <Head :title="`Équipe ${team.name} — CityPlay`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 pb-28 md:pb-12">
            <div v-if="$page.props.flash?.success" class="mb-8 p-4 rounded-2xl bg-success/20 border border-success/40 text-success text-center font-bold animate-fade-up">
                {{ $page.props.flash?.success }}
            </div>

            <div class="mb-8">
                <Link :href="route('teams.index')" class="text-muted-foreground hover:text-white flex items-center gap-2 transition-colors">
                    <ArrowLeft class="h-4 w-4" /> Retour aux équipes
                </Link>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- TEAM INFO -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-3xl p-8 border border-white/20 relative overflow-hidden shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                        <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-electric/10 blur-3xl" />

                        <div class="relative">
                            <div class="h-20 w-20 rounded-2xl bg-gradient-electric grid place-items-center shadow-neon mb-6">
                                <Users class="h-10 w-10 text-electric-foreground" />
                            </div>
                            <h1 class="font-display text-3xl text-white">{{ team.name }}</h1>

                            <div class="mt-4 flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/10">
                                <div>
                                    <div class="text-[10px] text-muted-foreground uppercase font-black">Code d'invitation</div>
                                    <div class="text-electric font-mono tracking-widest font-bold">{{ team.invite_code }}</div>
                                </div>
                                <button @click="copyInviteCode" class="h-10 w-10 rounded-lg bg-electric/10 border border-electric/20 flex items-center justify-center text-electric hover:bg-electric/20 transition-all">
                                    <Check v-if="copied" class="h-5 w-5" />
                                    <Copy v-else class="h-5 w-5" />
                                </button>
                            </div>

                            <div class="mt-8 space-y-4">
                                <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                    <Shield class="h-4 w-4 text-electric" />
                                    <span>Leader: <span class="text-white">{{ team.members.find(m => m.pivot.role === 'leader')?.name }}</span></span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                    <Users class="h-4 w-4 text-electric" />
                                    <span>{{ team.members.length }} / {{ team.member_limit }} Membres</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                    <Calendar class="h-4 w-4 text-electric" />
                                    <span>Créée le {{ new Date(team.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MEMBRES -->
                    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-3xl p-6 border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                        <h2 class="font-display text-xl text-white mb-4">Membres</h2>
                        <div class="space-y-3">
                            <div v-for="member in team.members" :key="member.id" class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-electric/20 flex items-center justify-center text-[10px] font-bold text-electric border border-electric/30">
                                        {{ member.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">{{ member.name }}</div>
                                        <div class="text-[10px] text-muted-foreground uppercase">{{ member.pivot.role }}</div>
                                    </div>
                                </div>
                                <div class="text-[10px] font-mono text-electric">{{ member.xp }} XP</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS & HISTORY -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- LANCER UNE QUÊTE -->
                    <section>
                        <h2 class="font-display text-2xl text-white mb-6 flex items-center gap-2">
                            <Play class="h-6 w-6 text-electric" /> Lancer une Quête en Équipe
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-for="city in availableCities" :key="city.id" class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl overflow-hidden border border-white/20 group shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="font-display text-xl text-white">{{ city.name }}</h3>
                                        <MapPin class="h-5 w-5 text-electric" />
                                    </div>
                                    <p class="text-sm text-muted-foreground line-clamp-2 mb-6">{{ city.description }}</p>

                                    <div class="flex gap-2">
                                        <NeonButton @click="startQuest(city.id)" class="flex-1" :disabled="startQuestForm.processing">
                                            Démarrer l'Aventure
                                        </NeonButton>
                                        <button
                                            @click="copyGameLink(city.id)"
                                            class="h-12 w-12 rounded-xl glass border-white/10 flex items-center justify-center text-white hover:border-electric/50 transition-all"
                                            title="Lien de partage de la partie"
                                        >
                                            <Check v-if="gameLinkCopied === city.id" class="h-5 w-5 text-success" />
                                            <LinkIcon v-else class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- HISTORIQUE -->
                    <section>
                        <h2 class="font-display text-2xl text-white mb-6 flex items-center gap-2">
                            <Trophy class="h-6 w-6 text-purple-neon" /> Historique de l'Équipe
                        </h2>
                        <div v-if="team.game_sessions?.length > 0" class="space-y-4">
                            <div v-for="session in team.game_sessions" :key="session.id" class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl p-5 border border-white/20 flex items-center justify-between shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-purple-neon/10 border border-purple-neon/20 grid place-items-center text-purple-neon">
                                        <MapPin class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ session.city?.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ new Date(session.created_at).toLocaleDateString() }} • {{ session.status }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-electric font-bold">+{{ session.total_score || 0 }} XP</div>
                                    <div class="text-[10px] text-muted-foreground uppercase">Score Total</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-12 text-center bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-3xl border border-white/20 text-muted-foreground italic shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                            Aucune quête terminée par cette équipe pour le moment.
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
