<script setup>
import SiteLayout from "@/Layouts/SiteLayout.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import AppImage from "@/Components/AppImage.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    MapPin,
    ArrowRight,
    Star,
    Clock,
    Zap,
    Bike,
    Car,
    Filter,
    RefreshCw,
    Trophy,
    Medal,
    CheckCircle2,
    Lock,
    BookOpen,
} from "lucide-vue-next";
import { ref, onMounted, computed, nextTick } from "vue";
import gsap from "gsap";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    city: Object,
    locations: Array,
    config: Object,
    completedSession: Object,
});
console.log(props.locations)

const isLoading = ref(true);
const canPlay = ref(true);
const searchQuery = ref("");
const showHistoryModal = ref(false);
const selectedLocation = ref(null);

const filteredLocations = computed(() => {
    if (!searchQuery.value) return props.locations;
    return props.locations.filter(
        (loc) =>
            loc.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            loc.description
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()),
    );
});

onMounted(() => {
    // Petit effet de chargement GSAP
    gsap.to(".loading-bar", {
        width: "100%",
        duration: 1.5,
        ease: "power2.inOut",
        onComplete: () => {
            isLoading.value = false;

            // Animation des cartes et stats
            nextTick(() => {
                gsap.from(".enigma-card", {
                    y: 30,
                    opacity: 0,
                    stagger: 0.1,
                    duration: 0.8,
                    ease: "back.out(1.7)",
                });

                if (!canPlay.value) {
                    gsap.from(".stats-block", {
                        scale: 0.9,
                        opacity: 0,
                        duration: 1,
                        ease: "power3.out",
                        delay: 0.5,
                    });
                }
            });
        },
    });
});

const selectEnigma = (location, enigma) => {
    if (!canPlay.value) return;
    router.post(route("player.adventure.launch", props.city.id), {
        location_id: location.id,
        enigma_id: enigma.id,
        difficulty: props.config.difficulty,
    });
};

const goBack = () => {
    router.get(route("player.adventure.setup", props.city.id));
};

const openHistory = (location) => {
    selectedLocation.value = location;
    showHistoryModal.value = true;
};

const getDifficultyColor = (diff) => {
    if (diff === "easy") return "text-success border-success/30 bg-success/10";
    if (diff === "medium")
        return "text-warning border-warning/30 bg-warning/10";
    return "text-destructive border-destructive/30 bg-destructive/10";
};

const formatTime = (seconds) => {
    if (!seconds) return "0m 00s";
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs.toString().padStart(2, "0")}s`;
};
</script>

<template>
    <Head title="Lobby Exploration — CityPlay" />

    <SiteLayout>
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12 min-h-screen"
        >
            <!-- LOADER GSAP -->
            <div
                v-if="isLoading"
                class="fixed inset-0 z-50 bg-gaming-dark flex flex-col items-center justify-center p-6"
            >
                <div
                    class="w-64 h-1 bg-white/10 rounded-full overflow-hidden mb-4"
                >
                    <div
                        class="loading-bar h-full bg-electric shadow-neon w-0"
                    ></div>
                </div>
                <div
                    class="text-[10px] text-electric uppercase tracking-[0.4em] font-black animate-pulse"
                >
                    Scan des fréquences locales...
                </div>
            </div>

            <div v-else>
                <!-- HEADER -->
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10"
                >
                    <div>
                        <div
                            class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-1"
                        >
                            Missions Disponibles
                        </div>
                        <h1
                            class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight"
                        >
                            {{ city.name.split(",")[0] }}
                            <span class="text-electric">EXPLORER</span>
                        </h1>
                        <div class="flex items-center gap-4 mt-2">
                            <span
                                class="text-xs text-muted-foreground flex items-center gap-1.5 bg-white/5 px-3 py-1 rounded-full border border-white/10"
                            >
                                <MapPin class="h-3 w-3 text-electric" />
                                {{ city.name }}
                            </span>
                            <span
                                :class="[
                                    'text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border',
                                    getDifficultyColor(config.difficulty),
                                ]"
                            >
                                {{ config.difficulty }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <!-- BARRE DE RECHERCHE -->
                        <div class="relative w-full md:w-64">
                            <Filter
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Filtrer les missions..."
                                class="w-full pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs focus:border-electric/50 focus:ring-0 transition-all text-white"
                            />
                        </div>

                        <div v-if="canPlay" class="flex gap-3 w-full md:w-auto">
                            <button
                                @click="goBack"
                                class="px-4 py-2 rounded-xl glass border-white/10 text-xs font-bold flex items-center gap-2 hover:border-electric/50 transition-all"
                            >
                                <RefreshCw class="h-4 w-4" /> REJOUER /
                                PARAMÈTRES
                            </button>
                        </div>
                        <div v-else class="flex gap-3">
                            <button
                                @click="goBack"
                                class="px-4 py-2 rounded-xl glass border-white/10 text-xs font-bold flex items-center gap-2 hover:border-electric/50 transition-all"
                            >
                                <RefreshCw class="h-4 w-4" /> RETOUR PARAMÈTRES
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STATS BLOCK (Si session terminée) -->
                <div
                    v-if="!canPlay && completedSession"
                    class="stats-block mb-10 bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-[2.5rem] border border-white/20 p-8 md:p-12 relative overflow-hidden shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
                >
                    <div
                        class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-electric/10 blur-3xl"
                    />

                    <div
                        class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-10"
                    >
                        <div class="text-center md:text-left">
                            <div
                                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-success/20 border border-success/40 text-success text-[10px] font-black uppercase tracking-widest mb-4"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5" /> Mission
                                Terminée
                            </div>
                            <h2
                                class="font-display text-4xl text-white uppercase mb-2"
                            >
                                Historique d'Exploration
                            </h2>
                            <p
                                class="text-muted-foreground text-sm max-w-md italic"
                            >
                                "Vous avez percé tous les secrets de
                                {{ city.name }}. Vos exploits sont gravés
                                dans l'histoire de la cité."
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 gap-4 w-full md:w-auto"
                        >
                            <div
                                class="p-6 rounded-3xl bg-gradient-to-br from-white/15 to-white/5 border border-white/20 text-center group hover:border-electric/30 transition-all backdrop-blur-xl shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
                            >
                                <Trophy
                                    class="h-6 w-6 text-warning mx-auto mb-2"
                                />
                                <div
                                    class="text-[8px] text-muted-foreground uppercase font-black mb-1"
                                >
                                    Score Final
                                </div>
                                <div
                                    class="text-xl font-display text-white group-hover:text-electric"
                                >
                                    {{ completedSession.final_score }} PX
                                </div>
                            </div>
                            <div
                                class="p-6 rounded-3xl bg-gradient-to-br from-white/15 to-white/5 border border-white/20 text-center group hover:border-electric/30 transition-all backdrop-blur-xl shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
                            >
                                <Clock
                                    class="h-6 w-6 text-electric mx-auto mb-2"
                                />
                                <div
                                    class="text-[8px] text-muted-foreground uppercase font-black mb-1"
                                >
                                    Temps Passé
                                </div>
                                <div
                                    class="text-xl font-display text-white group-hover:text-electric"
                                >
                                    {{
                                        formatTime(
                                            completedSession.total_time,
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="p-6 rounded-3xl bg-gradient-to-br from-white/15 to-white/5 border border-white/20 text-center group hover:border-electric/30 transition-all backdrop-blur-xl shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] col-span-2 sm:col-span-1"
                            >
                                <Medal
                                    class="h-6 w-6 text-success mx-auto mb-2"
                                />
                                <div
                                    class="text-[8px] text-muted-foreground uppercase font-black mb-1"
                                >
                                    Rang atteint
                                </div>
                                <div
                                    class="text-xl font-display text-white group-hover:text-electric"
                                >
                                    EXPLORATEUR
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LISTE DES ENIGMES -->
                <div
                    v-if="filteredLocations.length > 0"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="(location,index) in filteredLocations"
                        :key="location.id"
                        :class="[
                            'enigma-card group relative overflow-hidden rounded-2xl border backdrop-blur-xl transition-all duration-500',
                            location.user_progress?.[0]?.is_discovered 
                                ? 'bg-gradient-to-br from-amber-500/15 to-amber-500/5 border-amber-500/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]' 
                                : 'bg-gradient-to-br from-white/15 to-white/5 border-white/20 hover:border-electric/40 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]'
                        ]"
                    >
                        <!-- Image de fond -->
                        <div
                            :class="[
                                'absolute inset-0 transition-all duration-500',
                                location.user_progress?.[0]?.is_discovered ? 'opacity-100 group-hover:opacity-90' : 'opacity-50 group-hover:opacity-70'
                            ]"
                        >
                            <AppImage
                                :src="
                                    location.enigmas[0]?.image_url
                                        || location.enigmas[0]?.image_path || location.cover_image
                                "
                                fallback="/images/placeholders/location.jpg"
                                class="w-full h-full object-cover group-hover:scale-110"
                            />
                        </div>

                        <!-- Gradient plus doux pour meilleure visibilité -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-gaming-dark/40 to-transparent"
                        />

                        <div class="relative p-6 h-full flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div
                                    :class="[
                                        'h-10 w-10 rounded-xl border flex items-center justify-center shadow-neon-sm relative',
                                        location.user_progress?.[0]?.is_discovered 
                                            ? 'bg-amber-500/10 border-amber-500/30 text-amber-500' 
                                            : 'bg-electric/10 border-electric/20 text-electric'
                                    ]"
                                >
                                    <Zap
                                        v-if="config.transport === 'moto'"
                                        class="h-5 w-5"
                                    />
                                    <Car
                                        v-else-if="config.transport === 'car'"
                                        class="h-5 w-5"
                                    />
                                    <Bike v-else class="h-5 w-5" />
                                </div>
                                <div
                                    v-if="location.user_progress?.[0]?.stars"
                                    class="flex items-center gap-1.5 text-warning bg-black/60 px-2 py-1 rounded-lg backdrop-blur-sm border border-white/10"
                                >
                                    <Star
                                        v-for="i in 3"
                                        :key="i"
                                        :class="['h-3 w-3', i <= location.user_progress[0].stars ? 'fill-current' : 'opacity-20']"
                                    />
                                    <span class="text-[10px] font-black ml-1"
                                        >{{
                                            location.user_progress[0].stars
                                        }}★</span
                                    >
                                </div>
                                <div
                                    v-else
                                    class="flex items-center gap-1 text-warning bg-black/40 px-2 py-1 rounded-lg backdrop-blur-sm"
                                >
                                    <Star class="h-4 w-4 fill-current" />
                                    <span class="text-xs font-black"
                                        >{{location.enigmas[0]?.reward_coins}} PX</span
                                    >
                                </div>
                            </div>

                            <h3
                            
                                :class="[
                                    'font-display text-xl group-hover:text-electric transition-colors mb-2 uppercase tracking-wide',
                                    location.user_progress?.[0]?.is_discovered ? 'text-amber-500' : 'text-white'
                                ]"
                            >
                                Mission :
                                {{
                                    location.user_progress?.[0]
                                                ?.is_discovered ? location.name :
                                    location.enigmas[0]?.title || index + 1
                                }}
                            </h3>

                            <p
                                class="text-xs text-white/70 line-clamp-2 mb-6 flex-grow font-medium"
                            >
                                {{
                                    location.enigmas[0]?.content?.substring(
                                        0,
                                        100,
                                    )
                                }}...
                            </p>

                            <div
                                class="flex items-center justify-between pt-4 border-t border-white/10 mt-auto"
                            >
                                <div class="flex flex-col gap-1">
                                    <div
                                        class="flex items-center gap-2 text-[10px] text-white/50 font-bold uppercase tracking-widest"
                                    >
                                        <Clock class="h-3.5 w-3.5" /> 15-30 MIN
                                    </div>
                                    <div
                                        v-if="
                                            location.user_progress?.[0]
                                                ?.is_discovered
                                        "
                                        class="text-[9px] text-amber-500 font-black uppercase tracking-tighter flex items-center gap-1"
                                    >
                                        <CheckCircle2 class="h-3 w-3" />
                                        SÉCURISÉ
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        v-if="location.user_progress?.[0]?.is_discovered"
                                        @click="openHistory(location)"
                                        class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all"
                                        title="Lire l'histoire"
                                    >
                                        <BookOpen class="h-4 w-4" />
                                    </button>
                                    
                                    <button
                                        @click="
                                            selectEnigma(
                                                location,
                                                location.enigmas[0],
                                            )
                                        "
                                            :disabled="location.user_progress?.[0]
                                                    ?.is_discovered"
                                        :class="[
                                            'px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-neon ',
                                            location.user_progress?.[0]
                                                ?.is_discovered
                                                ? 'bg-amber-500/20 text-amber-500 border border-amber-500/30'
                                                : 'bg-electric text-white hover:scale-105 active:scale-95 transition-all',
                                        ]"
                                    >
                                        {{
                                            location.user_progress?.[0]
                                                ?.is_discovered
                                                ? "Découvert"
                                                : "DÉMARRER"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-20 bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-[2rem] border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
                >
                    <div
                        class="h-20 w-20 rounded-full bg-white/5 flex items-center justify-center mb-6"
                    >
                        <Filter class="h-10 w-10 text-muted-foreground/30" />
                    </div>
                    <h3 class="font-display text-2xl text-white mb-2 uppercase">
                        Aucune mission trouvée
                    </h3>
                    <p
                        class="text-muted-foreground text-sm max-w-md text-center px-6"
                    >
                        Nous n'avons trouvé aucune énigme correspondant à vos
                        critères de distance ({{ config.transport }}) et de
                        difficulté ({{ config.difficulty }}) dans cette ville.
                    </p>
                    <NeonButton @click="goBack" class="mt-8">
                        MODIFIER LES FILTRES
                    </NeonButton>
                </div>
            </div>
        </div>

        <!-- MODAL HISTOIRE -->
        <Modal :show="showHistoryModal" @close="showHistoryModal = false">
            <div class="p-8 bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl border border-white/20 rounded-[2.5rem] max-w-2xl mx-auto relative overflow-hidden shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
                <!-- Background Glow -->
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-electric/10 blur-3xl" />
                
                <div class="relative z-10 space-y-6">
                    <!-- Image du lieu -->
                    <div class="relative h-48 md:h-64 w-full rounded-3xl overflow-hidden border border-white/20 shadow-2xl">
                        <AppImage
                            :src="selectedLocation?.location_images?.[0]?.image_path || selectedLocation?.cover_image"
                            fallback="/images/placeholders/location.jpg"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-transparent to-transparent"></div>
                    </div>

                    <div class="flex items-center gap-4 mb-2">
                        <div class="h-14 w-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 shadow-neon-sm">
                            <BookOpen class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="text-[10px] text-amber-500 font-black uppercase tracking-[0.3em]">Chroniques de la Cité</div>
                            <h2 class="font-display text-3xl text-white uppercase italic font-black">{{ selectedLocation?.name }}</h2>
                        </div>
                    </div>

                    <div class="space-y-4 text-white/80 leading-relaxed text-sm">
                        <p class="font-bold text-amber-500/80 italic">" {{ selectedLocation?.description }} "</p>
                        <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent my-6"></div>
                        <div class="bg-white/5 p-6 rounded-3xl border border-white/10 prose prose-invert max-w-none">
                            {{ selectedLocation?.history }}
                        </div>
                    </div>

                    <div class="pt-4">
                        <button 
                            @click="showHistoryModal = false"
                            class="w-full py-4 rounded-2xl bg-amber-500 text-black font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all shadow-neon"
                        >
                            FERMER LES ARCHIVES
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.neon-text {
    text-shadow:
        0 0 10px rgba(0, 112, 255, 0.5),
        0 0 20px rgba(0, 112, 255, 0.3);
}
.shadow-neon-sm {
    box-shadow: 0 0 15px rgba(0, 112, 255, 0.2);
}
.glass-strong {
    background: rgba(10, 10, 18, 0.95);
    backdrop-filter: blur(40px);
}
.shadow-neon-success {
    box-shadow: 0 0 25px rgba(245, 158, 11, 0.2);
}
</style>
