<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, ArrowRight, Star, Clock, Zap, Bike, Car, Filter, RefreshCw } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    city: Object,
    locations: Array,
    config: Object,
});

const isLoading = ref(true);

onMounted(() => {
    // Petit effet de chargement GSAP
    gsap.to(".loading-bar", {
        width: "100%",
        duration: 1.5,
        ease: "power2.inOut",
        onComplete: () => {
            isLoading.value = false;
            gsap.from(".enigma-card", {
                y: 30,
                opacity: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: "back.out(1.7)"
            });
        }
    });
});

const selectEnigma = (location, enigma) => {
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
                <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">LOBBY <span class="text-electric">EXPLORER</span></h1>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-xs text-muted-foreground flex items-center gap-1.5 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                        <MapPin class="h-3 w-3 text-electric" /> {{ city.name }}
                    </span>
                    <span :class="['text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full border', getDifficultyColor(config.difficulty)]">
                        {{ config.difficulty }}
                    </span>
                </div>
            </div>

            <div class="flex gap-3">
                <button @click="goBack" class="px-4 py-2 rounded-xl glass border-white/10 text-xs font-bold flex items-center gap-2 hover:border-electric/50 transition-all">
                    <RefreshCw class="h-4 w-4" /> REJOUER / PARAMÈTRES
                </button>
            </div>
        </div>

        <!-- LISTE DES ENIGMES -->
        <div v-if="locations.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="location in locations"
                :key="location.id"
                class="enigma-card group relative overflow-hidden rounded-2xl glass-strong border border-white/10 hover:border-electric/40 transition-all duration-500"
            >
                <!-- Image de fond floue -->
                <div class="absolute inset-0 opacity-20 group-hover:opacity-40 transition-opacity">
                    <img :src="location.image_path || '/images/placeholders/location.jpg'" class="w-full h-full object-cover" />
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
                        {{ location.enigmas[0]?.content.substring(0, 100) }}...
                    </p>

                    <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                        <div class="flex items-center gap-2 text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                            <Clock class="h-3.5 w-3.5" /> 15-30 MIN
                        </div>
                        <button
                            @click="selectEnigma(location, location.enigmas[0])"
                            class="px-5 py-2 rounded-xl bg-electric text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-neon hover:scale-105 active:scale-95 transition-all"
                        >
                            DÉMARRER
                        </button>
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
