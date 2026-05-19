<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Users, UserPlus, Plus, Shield, ArrowRight, Info, MapPin, Zap, Link as LinkIcon, Check, Copy } from 'lucide-vue-next';

const props = defineProps({
    myTeams: Array,
    allTeams: Array,
    city: Object, // Added: the city for which we are managing teams
});

const createForm = useForm({
    name: '',
});

const joinForm = useForm({
    invite_code: '',
});

const copied = ref(null);

const copyInviteLink = (code) => {
    const url = `${window.location.origin}/teams/join?code=${code}`;
    navigator.clipboard.writeText(url);
    copied.value = code;
    setTimeout(() => copied.value = null, 2000);
};

const startTeamQuest = (teamId) => {
    if (props.city) {
        router.post(route('teams.start-quest', { team: teamId, city: props.city.id }));
    }
};

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
    <Head title="Équipes — CityPlay" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 pb-28 md:pb-12">
            <div v-if="$page.props.flash?.success" class="mb-8 p-4 rounded-2xl bg-success/20 border border-success/40 text-success text-center font-bold animate-fade-up">
                {{ $page.props.flash?.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-8 p-4 rounded-2xl bg-destructive/20 border border-destructive/40 text-destructive text-center font-bold animate-fade-up">
                {{ $page.props.flash?.error }}
            </div>

            <div class="text-center max-w-2xl mx-auto mb-12">
                <div v-if="city" class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-black text-primary uppercase tracking-[0.3em] mb-4">
                    <MapPin class="h-3 w-3" /> Mission : {{ city.name }}
                </div>
                <div v-else class="text-xs text-electric uppercase tracking-widest font-bold">L'union fait la force</div>
                <h1 class="font-display text-3xl md:text-5xl mt-2">
                    {{ city ? 'DÉPLOIEZ VOTRE' : 'Quêtes en' }} <span class="text-electric neon-text">{{ city ? 'ESCOUADE' : 'Équipe' }}</span>
                </h1>
                <p class="mt-3 text-muted-foreground">
                    {{ city ? `Sélectionnez l'une de vos équipes pour lancer l'incursion à ${city.name}.` : "Collaborez avec d'autres joueurs pour résoudre des énigmes plus complexes et gagner plus de récompenses." }}
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- ACTIONS -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-strong rounded-3xl p-6 border border-white/10">
                        <h2 class="font-display text-xl text-white mb-4 flex items-center gap-2">
                            <Plus class="h-5 w-5 text-electric" /> Créer une équipe
                        </h2>
                        <form @submit.prevent="createTeam" class="space-y-4">
                            <div>
                                <InputLabel for="team_name" value="Nom de l'équipe" class="text-white/70" />
                                <TextInput
                                    id="team_name"
                                    v-model="createForm.name"
                                    type="text"
                                    class="mt-1 block w-full bg-white/5 border-white/10 text-white focus:border-electric focus:ring-electric"
                                    placeholder="Ex: Les Lions du Dahomey"
                                    required
                                />
                            </div>
                            <NeonButton type="submit" class="w-full" :disabled="createForm.processing">
                                Créer l'équipe
                            </NeonButton>
                        </form>
                    </div>

                    <div class="glass-strong rounded-3xl p-6 border border-white/10">
                        <h2 class="font-display text-xl text-white mb-4 flex items-center gap-2">
                            <UserPlus class="h-5 w-5 text-purple-neon" /> Rejoindre une équipe
                        </h2>
                        <form @submit.prevent="joinTeam" class="space-y-4">
                            <div>
                                <InputLabel for="invite_code" value="Code d'invitation" class="text-white/70" />
                                <TextInput
                                    id="invite_code"
                                    v-model="joinForm.invite_code"
                                    type="text"
                                    class="mt-1 block w-full bg-white/5 border-white/10 text-white focus:border-purple-neon focus:ring-purple-neon uppercase"
                                    placeholder="Ex: ABCDEF"
                                    required
                                />
                            </div>
                            <NeonButton type="submit" variant="purple" class="w-full" :disabled="joinForm.processing">
                                Rejoindre
                            </NeonButton>
                        </form>
                    </div>
                </div>

                <!-- MY TEAMS & DISCOVERY -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- MES ÉQUIPES -->
                    <section>
                        <h2 class="font-display text-2xl text-white mb-4 flex items-center gap-2">
                            <Users class="h-6 w-6 text-electric" /> Mes Équipes
                        </h2>
                        <div v-if="myTeams.length > 0" class="grid gap-6">
                            <div v-for="team in myTeams" :key="team.id" class="group relative glass-strong rounded-[2rem] p-6 border border-white/10 hover:border-electric/40 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-display text-xl text-white uppercase italic font-black">{{ team.name }}</h3>
                                        <span v-if="team.creator_id === $page.props.auth.user.id" class="px-2 py-0.5 rounded-lg bg-electric/10 text-electric text-[8px] font-black border border-electric/20 uppercase tracking-widest">Leader</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest flex items-center gap-1.5">
                                            <Users class="h-3.5 w-3.5" /> {{ team.members_count }} / {{ team.member_limit }} membres
                                        </div>
                                        <div class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest flex items-center gap-1.5">
                                            <Shield class="h-3.5 w-3.5" /> Code: <span class="text-white font-mono">{{ team.invite_code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button 
                                        @click="copyInviteLink(team.invite_code)"
                                        class="h-12 w-12 rounded-xl glass border-white/5 flex items-center justify-center text-white/60 hover:text-white transition-all"
                                        title="Copier le lien d'invitation"
                                    >
                                        <Check v-if="copied === team.invite_code" class="h-5 w-5 text-success" />
                                        <LinkIcon v-else class="h-5 w-5" />
                                    </button>
                                    <Link :href="route('teams.show', team.id)" class="h-12 px-6 rounded-xl glass border-white/5 flex items-center justify-center text-white text-[10px] font-black uppercase tracking-widest hover:bg-white/5 transition-all">
                                        Gérer
                                    </Link>
                                    <button 
                                        v-if="city" 
                                        @click="startTeamQuest(team.id)"
                                        class="h-12 px-8 rounded-xl bg-electric text-black flex items-center justify-center gap-2 text-[10px] font-black uppercase tracking-widest hover:shadow-neon transition-all"
                                    >
                                        <Zap class="h-4 w-4" /> Déployer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-8 text-center glass-strong rounded-3xl border border-white/10 text-muted-foreground">
                            <Info class="h-8 w-8 mx-auto mb-3 opacity-20" />
                            Vous ne faites partie d'aucune équipe pour le moment.
                        </div>
                    </section>

                    <!-- DÉCOUVRIR DES ÉQUIPES -->
                    <section>
                        <h2 class="font-display text-2xl text-white mb-4">Équipes Populaires</h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-for="team in allTeams" :key="team.id" class="glass-strong rounded-2xl p-5 border border-white/10">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-bold text-white">{{ team.name }}</h3>
                                        <div class="text-xs text-muted-foreground mt-1">
                                            {{ team.members_count }} membres
                                        </div>
                                    </div>
                                    <div v-if="team.members_count < team.member_limit" class="text-xs text-electric font-bold">Places disponibles</div>
                                    <div v-else class="text-xs text-muted-foreground">Complet</div>
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
