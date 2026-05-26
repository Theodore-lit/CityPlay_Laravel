<script setup>
/**
 * Mission Lobby - Gestion de l'attente des joueurs avant le lancement d'une mission.
 * Permet l'invitation de joueurs et le lancement synchronisé.
 *
 * Auteur: Kamal
 */
import { ref, computed, onMounted } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Send, Play, ChevronLeft, UserPlus, MapPin, Loader } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    lobbySessionId: {
        type: String,
        required: true,
    },
    location: {
        type: Object,
        default: () => ({ id: null, name: '', description: '' }),
    },
    enigma: {
        type: Object,
        default: () => ({ id: null, difficulty: 'Normal' }),
    },
    city: {
        type: Object,
        default: () => ({ id: null, name: '' }),
    },
    players: {
        type: Array,
        default: () => [],
    },
});

const showInviteModal = ref(false);
const loading = ref(false);
const invitedUsers = ref([]);

/**
 * Filtre les utilisateurs disponibles pour ne pas afficher ceux déjà présents kamal
 */
const availableUsers = computed(() => {
    return invitedUsers.value.filter(u => !props.players.some(p => p.id === u.id));
});

/**
 * Charge la liste des joueurs disponibles (qui n'ont pas encore fait la mission) kamal
 */
const loadAvailableUsers = async () => {
    try {
        loading.value = 'searching';

        // Correction : On passe les IDs en paramètres d'URL car le GET n'accepte pas de body kamal
        const url = new URL(route('api.available-players'));
        if (props.city?.id) url.searchParams.append('city_id', props.city.id);
        if (props.location?.id) url.searchParams.append('location_id', props.location.id);

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const data = await response.json();
        invitedUsers.value = data.availableUsers || [];
        showInviteModal.value = true;
    } catch (error) {
        console.error('Erreur lors du chargement des joueurs disponibles:', error);
    } finally {
        loading.value = false;
    }
};

/**
 * Envoie une invitation à un joueur spécifique kamal
 */
const invitePlayer = (userId) => {
    loading.value = userId;
    router.post(route('player.mission-lobby.invite', props.lobbySessionId), {
        user_id: userId,
    }, {
        onFinish: () => {
            loading.value = false;
        }
    });
};

/**
 * Démarre officiellement la mission pour tous les joueurs du lobby kamal
 */
const startMission = () => {
    loading.value = true;
    router.post(route('player.mission-lobby.start', props.lobbySessionId), {}, {
        onFinish: () => {
            loading.value = false;
        }
    });
};

/**
 * Retourne à la configuration de l'aventure kamal
 */
const goBack = () => {
    if (props.city?.id) {
        router.get(route('player.adventure.setup', props.city.id));
    }
};

onMounted(() => {
    console.log('MissionLobby mounted with props:', {
        location: props.location,
        city: props.city,
        players: props.players,
        enigma: props.enigma,
    });
});
</script>

<template>
    <Head :title="`Mission Lobby${city?.name ? ' — ' + city.name : ''}`" />

    <SiteLayout>
        <div class="mx-auto max-w-4xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="flex items-center gap-6 mb-12">
                <button @click="goBack" class="h-12 w-12 rounded-2xl bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] grid place-items-center text-primary hover:scale-110 transition-all">
                    <ChevronLeft class="h-6 w-6" />
                </button>
                <div>
                    <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1">🎮 MISSION LOBBY</div>
                    <h1 class="font-display text-4xl text-foreground uppercase italic font-black">

                        <span class="text-white/40">{{ city?.name || '' }}</span>
                    </h1>
                </div>
            </div>

            <div v-if="location" class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-8 mb-12 shadow-[0_8px_32_0_rgba(0,0,0,0.3)]">
                <div class="flex items-center gap-3 mb-6">
                    <MapPin class="h-6 w-6 text-primary" />
                    <div>
                        <p class="text-xs text-white/60 uppercase font-black tracking-widest">Lieu de la mission</p>
                        <p class="text-2xl font-black text-foreground">À découvrir</p>
                    </div>
                </div>
                <p class="text-white/70 mb-6">{{ location.description }}</p>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white/10 rounded-xl p-4">
                        <p class="text-xs text-white/60 uppercase font-black tracking-widest mb-2">Difficulté</p>
                        <p class="text-lg font-black text-yellow-400">{{ enigma?.difficulty || 'Normal' }}</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <p class="text-xs text-white/60 uppercase font-black tracking-widest mb-2">Récompenses XP</p>
                        <p class="text-lg font-black text-green-400">500 XP</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <p class="text-xs text-white/60 uppercase font-black tracking-widest mb-2">Bonus Vainqueur</p>
                        <p class="text-lg font-black text-sky-400">+10 💎</p>
                    </div>
                </div>
            </div>

            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display text-2xl font-black text-foreground flex items-center gap-2">
                        <Users class="h-6 w-6 text-primary" />
                        Joueurs ({{ players?.length || 0 }})
                    </h2>
                    <button
                        @click="loadAvailableUsers"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-primary to-purple-500 text-black font-black uppercase text-xs tracking-wider hover:shadow-lg hover:shadow-primary/50 transition-all"
                    >
                        <UserPlus class="h-4 w-4" />
                        Inviter
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="player in players"
                        :key="player.id"
                        class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-6 shadow-lg"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-primary/40 to-purple-500/40 flex items-center justify-center">
                                <span class="text-2xl font-black text-primary">{{ player.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-black text-foreground">{{ player.name }}</p>
                                <p class="text-xs text-white/60">{{ player.xp?.toLocaleString() || 0 }} XP</p>
                                <div class="mt-2 inline-block bg-green-500/20 border border-green-500/30 rounded px-2 py-1">
                                    <p class="text-[10px] text-green-400 font-black uppercase">Prêt</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/10 border border-dashed border-white/30 rounded-xl p-6 flex items-center justify-center min-h-[140px]">
                        <button
                            @click="loadAvailableUsers"
                            class="text-center text-white/60 hover:text-white transition-colors"
                        >
                            <UserPlus class="h-8 w-8 mx-auto mb-2 opacity-50" />
                            <p class="text-xs font-black uppercase tracking-wider">+ Ajouter un joueur</p>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button
                    @click="goBack"
                    class="flex-1 px-6 py-4 rounded-xl border border-white/20 text-white font-black uppercase text-sm tracking-wider hover:bg-white/10 transition-all"
                >
                    Annuler
                </button>
                <button
                    @click="startMission"
                    :disabled="loading"
                    class="flex-1 px-6 py-4 rounded-xl bg-gradient-to-r from-primary to-purple-500 text-black font-black uppercase text-sm tracking-wider hover:shadow-lg hover:shadow-primary/50 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <Loader v-if="loading === true" class="h-4 w-4 animate-spin" />
                    <Play v-else class="h-4 w-4" />
                    {{ loading === true ? 'Lancement...' : 'Démarrer la mission' }}
                </button>
            </div>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showInviteModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div v-if="showInviteModal" class="bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-2xl border border-white/30 rounded-2xl p-8 max-w-md w-full shadow-2xl">
                        <h2 class="font-display text-2xl font-black text-foreground mb-6">Inviter des joueurs</h2>

                        <div v-if="availableUsers.length === 0" class="text-center py-8">
                            <p class="text-white/70 mb-2">Aucun joueur disponible</p>
                            <p class="text-xs text-white/50">Tous les joueurs ont déjà été invités ou ont complété cette mission</p>
                        </div>

                        <div v-else class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                            <button
                                v-for="user in availableUsers"
                                :key="user.id"
                                @click="invitePlayer(user.id)"
                                :disabled="loading === user.id"
                                class="w-full flex items-center justify-between p-4 bg-white/10 hover:bg-white/20 rounded-xl border border-white/20 transition-all disabled:opacity-50"
                            >
                                <div class="text-left flex-1">
                                    <p class="font-black text-foreground">{{ user.name }}</p>
                                    <p class="text-xs text-white/60">{{ user.xp?.toLocaleString() || 0 }} XP</p>
                                </div>
                                <Loader v-if="loading === user.id" class="h-4 w-4 animate-spin text-primary" />
                                <Send v-else class="h-4 w-4 text-primary" />
                            </button>
                        </div>

                        <button
                            @click="showInviteModal = false"
                            class="w-full px-4 py-3 rounded-xl border border-white/20 text-white font-black uppercase text-sm tracking-wider hover:bg-white/10 transition-all"
                        >
                            Fermer
                        </button>
                    </div>
                </Transition>
            </div>
        </Transition>

        <MobileTabBar />
    </SiteLayout>
</template>
