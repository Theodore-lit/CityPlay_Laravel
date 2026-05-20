<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, ArrowRight, Star, Clock, Zap, Bike, Car, Filter, RefreshCw } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import gsap from 'gsap';
import { cn } from '@/lib/utils';

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

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-7xl px-6 py-8 pb-28 md:pb-12 min-h-screen relative z-10">
      <!-- LOADER GSAP -->
      <div v-if="isLoading" class="fixed inset-0 z-[100] bg-zinc-950/95 backdrop-blur-2xl flex flex-col items-center justify-center p-6">
          <div class="w-80 h-1.5 bg-white/5 rounded-full overflow-hidden mb-6 border border-white/10">
              <div class="loading-bar h-full bg-primary shadow-[0_0_20px_#06b6d4] w-0" />
          </div>
          <div class="text-[12px] text-primary font-black uppercase tracking-[0.6em] animate-pulse">SCAN_FREQUENCES_LOCALES...</div>
      </div>

      <div v-else>
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
            <div>
                <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4">MISSIONS_AVAILABLE // SECTOR_LOBBY</div>
                <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white mb-6">
                    LOBBY <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">EXPLORER</span>
                </h1>
                <div class="flex items-center gap-4">
                    <div class="px-4 py-1.5 rounded-xl bg-primary/5 border border-primary/20 backdrop-blur-xl flex items-center gap-2">
                        <MapPin class="h-3.5 w-3.5 text-primary" />
                        <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ city.name }}</span>
                    </div>
                    <div :class="cn('px-4 py-1.5 rounded-xl border-2 text-[10px] font-black uppercase tracking-widest', getDifficultyColor(config.difficulty))">
                        {{ config.difficulty.toUpperCase() }}
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <HUDButton @click="goBack" variant="primary" class="h-12 px-6">
                    <div class="flex items-center gap-2">
                        <RefreshCw class="h-4 w-4" />
                        <span>REJOUER // PARAMÈTRES</span>
                    </div>
                </HUDButton>
            </div>
        </div>

        <!-- LISTE DES ENIGMES -->
        <div v-if="locations.length > 0" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="location in locations"
                :key="location.id"
                class="enigma-card hud-glass-card group relative overflow-hidden rounded-[2.5rem] border-2 border-white/5 hover:border-primary/40 transition-all duration-700 h-[480px] flex flex-col"
            >
                <!-- Image de fond floue -->
                <div class="absolute inset-0 opacity-20 group-hover:opacity-40 transition-all duration-1000">
                    <img :src="location.image_path || '/images/placeholders/location.jpg'" class="w-full h-full object-cover city-hud-img" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/80 to-transparent" />
                <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />

                <div class="relative p-8 h-full flex flex-col z-10">
                    <div class="flex justify-between items-start mb-8">
                        <div class="h-14 w-14 rounded-2xl bg-primary/10 border-2 border-primary/30 flex items-center justify-center text-primary shadow-[0_0_20px_rgba(6,182,212,0.2)] group-hover:scale-110 transition-transform">
                            <Zap v-if="config.transport === 'moto'" class="h-7 w-7" />
                            <Car v-else-if="config.transport === 'car'" class="h-7 w-7" />
                            <Bike v-else class="h-7 w-7" />
                        </div>
                        <div class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center gap-2 text-amber-500">
                            <Star class="h-3.5 w-3.5 fill-current" />
                            <span class="text-[10px] font-black tracking-widest">150 PX</span>
                        </div>
                    </div>

                    <h3 class="font-display text-2xl text-white group-hover:text-primary transition-colors mb-4 uppercase italic font-black tracking-tighter leading-tight">
                        {{ location.enigmas[0]?.title || 'MISSION_MYSTERE' }}
                    </h3>

                    <p class="text-[11px] text-white/40 font-bold uppercase tracking-widest leading-relaxed line-clamp-3 mb-8 flex-grow">
                        {{ location.enigmas[0]?.content.substring(0, 150) }}...
                    </p>

                    <div class="flex items-center justify-between pt-8 border-t border-white/5 mt-auto">
                        <div class="flex flex-col gap-1">
                            <span class="text-[8px] text-white/20 font-black tracking-[0.3em] uppercase">ESTIMATED_TIME</span>
                            <div class="flex items-center gap-2 text-[10px] text-white/60 font-black uppercase tracking-widest">
                                <Clock class="h-3.5 w-3.5 text-primary" /> 15-30 MIN
                            </div>
                        </div>
                        <HUDButton
                            @click="selectEnigma(location, location.enigmas[0])"
                            variant="primary"
                            class="h-11 px-8"
                        >
                            DÉMARRER
                        </HUDButton>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-24 hud-glass-card rounded-[3rem] border-2 border-white/5 max-w-4xl mx-auto">
            <div class="h-24 w-24 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-8 relative">
                <div class="absolute inset-0 rounded-full border border-primary/20 animate-ping" />
                <Filter class="h-10 w-10 text-white/20" />
            </div>
            <h3 class="font-display text-3xl text-white font-black uppercase italic tracking-tighter mb-4">AUCUNE_MISSION_TROUVÉE</h3>
            <p class="text-white/40 text-xs font-bold uppercase tracking-[0.2em] max-w-md text-center px-10 leading-loose">
                Le système n'a trouvé aucune énigme correspondant à vos critères de distance ({{ config.transport.toUpperCase() }}) et de difficulté ({{ config.difficulty.toUpperCase() }}) dans ce secteur.
            </p>
            <HUDButton @click="goBack" variant="primary" class="mt-12 h-14 px-12">
                MODIFIER LES FILTRES
            </HUDButton>
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
