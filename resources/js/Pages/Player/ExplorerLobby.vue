<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, ArrowRight, Star, Clock, Zap, Bike, Car, Filter, RefreshCw, Trophy, Medal, CheckCircle2 } from 'lucide-vue-next';
import { ref, onMounted, computed, nextTick } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    city: Object,
    locations: Array,
    config: Object,
    completedSession: Object,
});

const isLoading = ref(true);
const canPlay = ref(true); // Toujours autorisé à rejouer par défaut selon la nouvelle directive

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
                    ease: "back.out(1.7)"
                });

                if (!canPlay.value) {
                    gsap.from(".stats-block", {
                        scale: 0.9,
                        opacity: 0,
                        duration: 1,
                        ease: "power3.out",
                        delay: 0.5
                    });
                }
            });
        }
    });
});

const selectEnigma = (location, enigma) => {
    if (!canPlay.value) return;
    router.post(route('player.adventure.launch', props.city.id), {
        location_id: location.id,
        enigma_id: enigma.id,
        difficulty: props.config.difficulty
    });
};

const goBack = () => {
    router.get(route('player.adventure.setup', props.city.id));
};

const getDifficultyColor = (diff) => {
    if (diff === 'easy') return 'text-success border-success/30 bg-success/10';
    if (diff === 'medium') return 'text-warning border-warning/30 bg-warning/10';
    return 'text-destructive border-destructive/30 bg-destructive/10';
};

const formatTime = (seconds) => {
    if (!seconds) return "0m 00s";
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs.toString().padStart(2, '0')}s`;
};
</script>

<template>
  <Head title="Lobby Exploration — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12 min-h-screen">
      <!-- LOADER GSAP -->
      <div v-if="isLoading" class="fixed inset-0 z-50 bg-gaming-dark flex flex-col items-center justify-center p-6">
          <div class="w-64 h-1 bg-white/10 rounded-full overflow-hidden mb-4">
              <div class="loading-bar h-full bg-electric shadow-neon w-0"></div>
          </div>
          <div class="text-[10px] text-electric uppercase tracking-[0.4em] font-black animate-pulse">Scan des fréquences locales...</div>
      </div>

      <div v-else>
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <div class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-1">Missions Disponibles</div>
                <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">{{city.name.split(',')[0]}} <span class="text-electric">EXPLORER</span></h1>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-xs text-muted-foreground flex items-center gap-1.5 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                        <MapPin class="h-3 w-3 text-electric" /> {{ city.name }}
                    </span>
                    <span :class="['text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border', getDifficultyColor(config.difficulty)]">
                        {{ config.difficulty }}
                    </span>
                </div>
            </div>

                <div v-if="canPlay" class="flex gap-3">
                    <button @click="goBack" class="px-4 py-2 rounded-xl glass border-white/10 text-xs font-bold flex items-center gap-2 hover:border-electric/50 transition-all">
                        <RefreshCw class="h-4 w-4" /> REJOUER / PARAMÈTRES
                    </button>
                </div>
                <div v-else class="flex gap-3">
                    <button @click="goBack" class="px-4 py-2 rounded-xl glass border-white/10 text-xs font-bold flex items-center gap-2 hover:border-electric/50 transition-all">
                        <RefreshCw class="h-4 w-4" /> RETOUR PARAMÈTRES
                    </button>
                </div>
        </div>

        <!-- STATS BLOCK (Si session terminée) -->
        <div v-if="!canPlay && completedSession" class="stats-block mb-10 glass-strong rounded-[2.5rem] border border-electric/30 p-8 md:p-12 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-electric/10 blur-3xl" />
            
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-10">
                <div class="text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-success/20 border border-success/40 text-success text-[10px] font-black uppercase tracking-widest mb-4">
                        <CheckCircle2 class="h-3.5 w-3.5" /> Mission Terminée
                    </div>
                    <h2 class="font-display text-4xl text-white uppercase mb-2">Historique d'Exploration</h2>
                    <p class="text-muted-foreground text-sm max-w-md italic">"Vous avez percé tous les secrets de {{ city.name }}. Vos exploits sont gravés dans l'histoire de la cité."</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 w-full md:w-auto">
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 text-center group hover:border-electric/30 transition-all">
                        <Trophy class="h-6 w-6 text-warning mx-auto mb-2" />
                        <div class="text-[8px] text-muted-foreground uppercase font-black mb-1">Score Final</div>
                        <div class="text-xl font-display text-white group-hover:text-electric">{{ completedSession.final_score }} PX</div>
                    </div>
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 text-center group hover:border-electric/30 transition-all">
                        <Clock class="h-6 w-6 text-electric mx-auto mb-2" />
                        <div class="text-[8px] text-muted-foreground uppercase font-black mb-1">Temps Passé</div>
                        <div class="text-xl font-display text-white group-hover:text-electric">{{ formatTime(completedSession.total_time) }}</div>
                    </div>
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 text-center group hover:border-electric/30 transition-all col-span-2 sm:col-span-1">
                        <Medal class="h-6 w-6 text-success mx-auto mb-2" />
                        <div class="text-[8px] text-muted-foreground uppercase font-black mb-1">Rang atteint</div>
                        <div class="text-xl font-display text-white group-hover:text-electric">EXPLORATEUR</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTE DES ENIGMES -->
        <div v-if="locations.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="location in locations"
                :key="location.id"
                class="enigma-card group relative overflow-hidden rounded-2xl glass-strong border border-white/10 transition-all duration-500"
                :class="canPlay ? 'hover:border-electric/40' : 'opacity-60 grayscale-[0.5]'"
            >
                <!-- Image de fond floue -->
                <div class="absolute inset-0 opacity-20 group-hover:opacity-40 transition-opacity">
                    <AppImage
                      :src="location.cover_image || location.image_urls?.[0] || location.images?.[0]"
                      fallback="/images/placeholders/location.jpg"
                      class="w-full h-full object-cover"
                    />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-gaming-dark/80 to-transparent" />

                <div class="relative p-6 h-full flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-10 w-10 rounded-xl bg-electric/10 border border-electric/20 flex items-center justify-center text-electric shadow-neon-sm">
                            <Zap v-if="config.transport === 'moto'" class="h-5 w-5" />
                            <Car v-else-if="config.transport === 'car'" class="h-5 w-5" />
                            <Bike v-else class="h-5 w-5" />
                        </div>
                        <div class="flex items-center gap-1 text-warning">
                            <Star class="h-4 w-4 fill-current" />
                            <span class="text-xs font-black">150 PX</span>
                        </div>
                    </div>

                    <h3 class="font-display text-xl text-white group-hover:text-electric transition-colors mb-2 uppercase tracking-wide">
                        Mission : {{ location.enigmas[0]?.title || 'Enigme Mystère' }}
                    </h3>

                    <p class="text-xs text-muted-foreground line-clamp-2 mb-6 flex-grow">
                        {{ location.enigmas[0]?.content?.substring(0, 100) }}...
                    </p>

                    <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                        <div class="flex items-center gap-2 text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                            <Clock class="h-3.5 w-3.5" /> 15-30 MIN
                        </div>
                        
                        <button
                            v-if="canPlay"
                            @click="selectEnigma(location, location.enigmas[0])"
                            class="px-5 py-2 rounded-xl bg-electric text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-neon hover:scale-105 active:scale-95 transition-all"
                        >
                            DÉMARRER
                        </button>
                        <div v-else class="px-5 py-2 rounded-xl bg-success/20 border border-success/40 text-success text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2">
                            <CheckCircle2 class="h-3 w-3" /> TERMINÉE
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-20 glass rounded-[2rem] border border-white/5">
            <div class="h-20 w-20 rounded-full bg-white/5 flex items-center justify-center mb-6">
                <Filter class="h-10 w-10 text-muted-foreground/30" />
            </div>
            <h3 class="font-display text-2xl text-white mb-2 uppercase">Aucune mission trouvée</h3>
            <p class="text-muted-foreground text-sm max-w-md text-center px-6">
                Nous n'avons trouvé aucune énigme correspondant à vos critères de distance ({{ config.transport }}) et de difficulté ({{ config.difficulty }}) dans cette ville.
            </p>
            <NeonButton @click="goBack" class="mt-8">
                MODIFIER LES FILTRES
            </NeonButton>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>

<style scoped>
.neon-text {
    text-shadow: 0 0 10px rgba(0, 112, 255, 0.5), 0 0 20px rgba(0, 112, 255, 0.3);
}
.shadow-neon-sm {
    box-shadow: 0 0 15px rgba(0, 112, 255, 0.2);
}
.glass-strong {
    background: rgba(15, 15, 25, 0.8);
    backdrop-filter: blur(20px);
}
</style>
