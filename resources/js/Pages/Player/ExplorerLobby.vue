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
} from "lucide-vue-next";
import { ref, onMounted, computed, nextTick } from "vue";
import gsap from "gsap";

const props = defineProps({
    city: Object,
    locations: Array,
    config: Object,
    completedSession: Object,
});

const isLoading = ref(true);
const canPlay = ref(true);
const searchQuery = ref("");

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

            <div v-else class="relative">
                <!-- BACKGROUND DECORATIONS -->
                <div class="absolute -top-20 -left-20 w-64 h-64 bg-electric/10 rounded-full blur-[100px] pointer-events-none animate-pulse"></div>
                <div class="absolute top-1/2 -right-20 w-80 h-80 bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

                <!-- HEADER -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12 relative z-10">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-electric/10 border border-electric/20 text-[9px] text-electric uppercase tracking-[0.3em] font-black">
                            <Zap class="h-3 w-3" /> Missions Disponibles
                        </div>
                        <h1 class="font-display text-4xl md:text-6xl neon-text uppercase tracking-tighter leading-none">
                            {{city.name.split(',')[0]}} <span class="text-white/20">EXPLORER</span>
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 mt-4">
                            <span class="text-xs text-white/60 flex items-center gap-2 bg-white/5 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/10 shadow-xl">
                                <MapPin class="h-3.5 w-3.5 text-electric" /> {{ city.name }}
                            </span>
                            <span :class="['text-[10px] font-black uppercase tracking-[0.2em] px-4 py-2 rounded-2xl border shadow-xl backdrop-blur-md', getDifficultyColor(config.difficulty)]">
                                Niveau : {{ config.difficulty }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-center w-full md:w-auto">
                        <!-- BARRE DE RECHERCHE -->
                        <div class="relative w-full md:w-72 group">
                            <div class="absolute inset-0 bg-electric/5 rounded-2xl blur-xl opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                            <Filter class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground group-focus-within:text-electric transition-colors" />
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Rechercher une mission..." 
                                class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-2xl text-sm focus:border-electric/50 focus:ring-0 transition-all text-white placeholder:text-white/20 backdrop-blur-xl"
                            />
                        </div>

                        <div v-if="canPlay" class="flex gap-3 w-full md:w-auto">
                            <button @click="goBack" class="flex-1 md:flex-none px-6 py-3.5 rounded-2xl glass-strong border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-3 hover:border-electric/50 hover:bg-white/10 transition-all active:scale-95 shadow-xl">
                                <RefreshCw class="h-4 w-4 text-electric" /> CONFIGURATION
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STATS BLOCK (Si session terminée) -->
                <div v-if="!canPlay && completedSession" class="stats-block mb-12 glass-strong rounded-[3rem] border border-electric/30 p-10 md:p-14 relative overflow-hidden group shadow-2xl">
                    <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-electric/15 blur-[100px] group-hover:bg-electric/25 transition-colors duration-1000" />
                    <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-primary/10 blur-[80px]" />
                    
                    <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">
                        <div class="text-center lg:text-left space-y-4">
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-success/20 border border-success/40 text-success text-[10px] font-black uppercase tracking-[0.3em] mb-2 shadow-lg shadow-success/10">
                                <CheckCircle2 class="h-4 w-4" /> Mission Terminée
                            </div>
                            <h2 class="font-display text-4xl md:text-5xl text-white uppercase italic font-black leading-tight">Historique <br class="hidden md:block"> <span class="text-electric">D'Exploration</span></h2>
                            <p class="text-white/50 text-sm max-w-md italic leading-relaxed">"Vous avez percé tous les secrets de {{ city.name }}. Vos exploits sont gravés dans l'histoire de la cité."</p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 w-full lg:w-auto">
                            <div class="p-8 rounded-[2.5rem] bg-white/5 border border-white/10 text-center group/item hover:border-electric/40 transition-all hover:-translate-y-2 duration-500 backdrop-blur-md">
                                <Trophy class="h-8 w-8 text-warning mx-auto mb-4 drop-shadow-neon" />
                                <div class="text-[9px] text-white/40 uppercase font-black tracking-widest mb-1">Score Final</div>
                                <div class="text-2xl font-display text-white group-item-hover:text-electric">{{ completedSession.final_score }} PX</div>
                            </div>
                            <div class="p-8 rounded-[2.5rem] bg-white/5 border border-white/10 text-center group/item hover:border-electric/40 transition-all hover:-translate-y-2 duration-500 backdrop-blur-md">
                                <Clock class="h-8 w-8 text-electric mx-auto mb-4 drop-shadow-neon" />
                                <div class="text-[9px] text-white/40 uppercase font-black tracking-widest mb-1">Temps Total</div>
                                <div class="text-2xl font-display text-white group-item-hover:text-electric">{{ formatTime(completedSession.total_time) }}</div>
                            </div>
                            <div class="p-8 rounded-[2.5rem] bg-white/5 border border-white/10 text-center group/item hover:border-electric/40 transition-all hover:-translate-y-2 duration-500 backdrop-blur-md col-span-2 sm:col-span-1">
                                <Medal class="h-8 w-8 text-success mx-auto mb-4 drop-shadow-neon" />
                                <div class="text-[9px] text-white/40 uppercase font-black tracking-widest mb-1">Rang atteint</div>
                                <div class="text-2xl font-display text-white group-item-hover:text-electric uppercase tracking-tighter">Élite</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LISTE DES ENIGMES -->
                <div v-if="filteredLocations.length > 0" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 relative z-10">
                    <div
                v-for="location in filteredLocations"
                :key="location.id"
                class="enigma-card group relative h-[450px] overflow-hidden rounded-[2.5rem] glass-strong border border-white/15 transition-all duration-700 hover:border-electric/50 hover:shadow-2xl hover:shadow-electric/30"
            >
                <!-- Image de fond (Visibilité augmentée) -->
                <div class="absolute inset-0 group-hover:scale-105 transition-all duration-1000 opacity-50 group-hover:opacity-80">
                    <AppImage
                      :src="location.location_images?.[0]?.image_path || location.cover_image"
                      fallback="/images/placeholders/location.jpg"
                      class="w-full h-full object-cover"
                    />
                </div>
                
                <!-- Overlay Gradients (Optimisés pour la lisibilité) -->
                <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-gaming-dark/20 to-transparent opacity-95" />
                        <div class="absolute inset-0 bg-gradient-to-b from-electric/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700" />

                        <div class="relative p-8 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-6">
                                    <div class="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl flex items-center justify-center text-electric group-hover:border-electric/50 transition-colors shadow-xl">
                                        <Zap v-if="config.transport === 'moto'" class="h-6 w-6" />
                                        <Car v-else-if="config.transport === 'car'" class="h-6 w-6" />
                                        <Bike v-else class="h-6 w-6" />
                                    </div>
                                    <div v-if="location.user_progress?.[0]?.stars" class="flex items-center gap-1.5 text-warning bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-xl border border-warning/20 shadow-xl">
                                        <Star v-for="i in 3" :key="i" :class="['h-3 w-3', i <= location.user_progress[0].stars ? 'fill-current' : 'opacity-20']" />
                                        <span class="text-[10px] font-black ml-1">{{ location.user_progress[0].stars }}</span>
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-warning bg-black/60 backdrop-blur-md px-4 py-2 rounded-xl border border-warning/20 shadow-xl">
                                        <Star class="h-4 w-4 fill-current animate-pulse" />
                                        <span class="text-[11px] font-black tracking-widest">150 PX</span>
                                    </div>
                                </div>

                                <h3 class="font-display text-2xl text-white group-hover:text-electric transition-all mb-4 uppercase italic font-black tracking-tight leading-tight">
                                    {{ location.enigmas[0]?.title || location.name }}
                                </h3>

                                <p class="text-sm text-white/60 line-clamp-3 mb-6 leading-relaxed font-medium group-hover:text-white/90 transition-colors">
                                    {{ location.enigmas[0]?.content || location.description }}
                                </p>
                            </div>

                            <div class="pt-6 border-t border-white/10 flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-[10px] text-white/40 font-black uppercase tracking-[0.2em]">
                                        <Clock class="h-3.5 w-3.5 text-electric" /> 15-30 MIN
                                    </div>
                                    <div v-if="location.user_progress?.[0]?.is_discovered" class="text-[9px] text-success font-black uppercase tracking-[0.2em] flex items-center gap-1.5 mt-1">
                                        <CheckCircle2 class="h-3.5 w-3.5" /> DÉJÀ EXPLORÉ
                                    </div>
                                </div>
                                
                                <button
                                    @click="selectEnigma(location, location.enigmas[0])"
                                    :class="[
                                        'px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-2xl transition-all hover:-translate-y-1 active:scale-95',
                                        location.user_progress?.[0]?.is_discovered 
                                            ? 'bg-white/5 text-white border border-white/10 hover:bg-electric hover:text-black hover:border-electric' 
                                            : 'bg-electric text-black shadow-electric/20 hover:shadow-electric/40'
                                    ]"
                                >
                                    {{ location.user_progress?.[0]?.is_discovered ? 'REJOUER' : 'DÉMARRER' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center py-20 glass rounded-[2rem] border border-white/5"
                    >
                        <div
                            class="h-20 w-20 rounded-full bg-white/5 flex items-center justify-center mb-6"
                        >
                            <Filter
                                class="h-10 w-10 text-muted-foreground/30"
                            />
                        </div>
                        <h3
                            class="font-display text-2xl text-white mb-2 uppercase"
                        >
                            Aucune mission trouvée
                        </h3>
                        <p
                            class="text-muted-foreground text-sm max-w-md text-center px-6"
                        >
                            Nous n'avons trouvé aucune énigme correspondant à
                            vos critères de distance ({{ config.transport }}) et
                            de difficulté ({{ config.difficulty }}) dans cette
                            ville.
                        </p>
                        <NeonButton @click="goBack" class="mt-8">
                            MODIFIER LES FILTRES
                        </NeonButton>
                    </div>
                </div>
            </div>
        <!-- </div> -->
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
    background: rgba(15, 15, 25, 0.92);
    backdrop-filter: blur(40px);
}
</style>
