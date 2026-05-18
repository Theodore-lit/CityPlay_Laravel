<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Users, UserPlus, Plus, Shield, ArrowRight, Info } from 'lucide-vue-next';

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
                <div class="text-xs text-electric uppercase tracking-widest font-bold">L'union fait la force</div>
                <h1 class="font-display text-3xl md:text-5xl mt-2">Quêtes en <span class="text-electric neon-text">Équipe</span></h1>
                <p class="mt-3 text-muted-foreground">Collaborez avec d'autres joueurs pour résoudre des énigmes plus complexes et gagner plus de récompenses.</p>
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
                        <div v-if="myTeams.length > 0" class="grid gap-4 md:grid-cols-2">
                            <Link v-for="team in myTeams" :key="team.id" :href="route('teams.show', team.id)" class="group block glass-strong rounded-2xl p-5 border border-white/10 hover:border-electric/40 transition-all">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-lg text-white group-hover:text-electric transition-colors">{{ team.name }}</h3>
                                        <div class="text-xs text-muted-foreground mt-1 flex items-center gap-2">
                                            <Users class="h-3 w-3" /> {{ team.members_count }} / {{ team.member_limit }} membres
                                        </div>
                                    </div>
                                    <div class="h-10 w-10 rounded-xl bg-electric/10 border border-electric/20 grid place-items-center text-electric">
                                        <ArrowRight class="h-5 w-5" />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center gap-2">
                                    <span v-if="team.creator_id === $page.props.auth.user.id" class="px-2 py-0.5 rounded-full bg-electric/20 text-electric text-[10px] font-bold border border-electric/30">LEADER</span>
                                    <span class="text-[10px] text-muted-foreground uppercase tracking-tighter">Code: {{ team.invite_code }}</span>
                                </div>
                            </Link>
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
