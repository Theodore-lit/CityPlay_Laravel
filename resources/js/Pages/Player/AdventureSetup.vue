<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { User, Users, MapPin, ArrowRight, ShieldCheck, Zap, Terminal, Bike, Info, Car, Gauge, Target, Navigation } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    city: Object,
    teams: Array,
});

const selectedMode = ref('solo'); // 'solo' par défaut
const transportMode = ref('bike'); // 'bike', 'moto', 'car'
const difficulty = ref('medium'); // 'easy', 'medium', 'hard'
const isScanning = ref(false);

const startAdventure = () => {
    if (selectedMode.value === 'solo') {
        isScanning.value = true;

        // Animation GSAP pour le scan
        gsap.to(".scan-bar", {
            width: "100%",
            duration: 2,
            ease: "power1.inOut",
            onComplete: () => {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        router.get(route('player.adventure.solo', props.city.id), {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                            transport: transportMode.value,
                            difficulty: difficulty.value
                        });
                    }, () => {
                        isScanning.value = false;
                        alert("La géolocalisation est requise pour le mode exploration.");
                    });
                } else {
                    isScanning.value = false;
                    alert("Votre navigateur ne supporte pas la géolocalisation.");
                }
            }
        });
    } else if (selectedMode.value === 'team') {
        router.get(route('teams.index'));
    }
};

onMounted(() => {
    gsap.from(".setup-section", {
        opacity: 0,
        y: 20,
        stagger: 0.2,
        duration: 0.8,
        ease: "power2.out"
    });
});
</script>

<template>
  <Head title="Configuration Mission — CityPlay" />

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-7xl px-6 py-8 pb-28 md:pb-12 relative z-10">
      <!-- LOADER SCAN GSAP -->
      <div v-if="isScanning" class="fixed inset-0 z-[100] bg-zinc-950/95 backdrop-blur-2xl flex flex-col items-center justify-center p-6">
          <div class="relative w-80 h-80 mb-12">
              <div class="absolute inset-0 rounded-full border-2 border-primary/20 animate-ping" />
              <div class="absolute inset-4 rounded-full border border-primary/10 grid place-items-center">
                  <div class="h-48 w-48 rounded-full border-2 border-primary/40 animate-spin [animation-duration:10s] relative">
                      <div class="absolute top-0 left-1/2 -translate-x-1/2 h-4 w-4 bg-primary rounded-full shadow-[0_0_20px_#06b6d4]" />
                  </div>
                  <Navigation class="absolute h-16 w-16 text-primary animate-pulse" />
              </div>
          </div>
          <div class="w-80 h-1.5 bg-white/5 rounded-full overflow-hidden mb-6 border border-white/10">
              <div class="scan-bar h-full bg-primary shadow-[0_0_20px_#06b6d4] w-0" />
          </div>
          <div class="text-[12px] text-primary font-black uppercase tracking-[0.6em] animate-pulse">TRIANGULATION_GPS_EN_COURS...</div>
      </div>

      <!-- HEADER -->
      <div class="setup-section flex flex-col items-center text-center mb-16">
        <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4">NAV_SYSTEM_BOOT</div>
        <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white mb-6">
          CONFIGURER LA <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">MISSION</span>
        </h1>
        <div class="px-6 py-2 rounded-2xl bg-primary/5 border border-primary/20 backdrop-blur-xl flex items-center gap-3">
            <MapPin class="h-4 w-4 text-primary animate-bounce" />
            <span class="text-[11px] font-black text-white uppercase tracking-widest">{{ city.name }} // SECTEUR_ACTIF</span>
        </div>
      </div>

      <div class="max-w-4xl mx-auto space-y-12">
        <!-- MODE DE JEU -->
        <section class="setup-section space-y-6">
            <div class="flex items-center gap-4 mb-4">
                <Users class="h-5 w-5 text-primary" />
                <h2 class="font-display text-2xl font-black uppercase italic tracking-tight text-white">FORMATION_TACTIQUE</h2>
                <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
            </div>
            <div class="grid grid-cols-2 gap-6">
                <button @click="selectedMode = 'solo'" :class="cn('p-8 rounded-[2rem] border-2 transition-all text-left relative overflow-hidden group', selectedMode === 'solo' ? 'bg-primary/10 border-primary shadow-[0_0_30px_rgba(6,182,212,0.15)]' : 'hud-glass-card border-white/5 hover:border-white/20')">
                    <div class="relative z-10">
                        <User :class="cn('h-8 w-8 mb-4 transition-colors', selectedMode === 'solo' ? 'text-primary' : 'text-white/40')" />
                        <div class="font-black text-lg text-white uppercase italic">MODE_SOLO</div>
                        <div class="text-[10px] text-white/40 font-black uppercase tracking-[0.2em] mt-1">EXPLORATION_INDIVIDUELLE</div>
                    </div>
                </button>
                <button @click="selectedMode = 'team'" :class="cn('p-8 rounded-[2rem] border-2 transition-all text-left relative overflow-hidden group', selectedMode === 'team' ? 'bg-purple-500/10 border-purple-500 shadow-[0_0_30px_rgba(217,70,239,0.15)]' : 'hud-glass-card border-white/5 hover:border-white/20')">
                    <div class="relative z-10">
                        <Users :class="cn('h-8 w-8 mb-4 transition-colors', selectedMode === 'team' ? 'text-purple-500' : 'text-white/40')" />
                        <div class="font-black text-lg text-white uppercase italic">UNITÉ_TACTIQUE</div>
                        <div class="text-[10px] text-white/40 font-black uppercase tracking-[0.2em] mt-1">COOPÉRATION_MULTI_LINK</div>
                    </div>
                </button>
            </div>
        </section>

        <template v-if="selectedMode === 'solo'">
            <!-- MOYEN DE DÉPLACEMENT -->
            <section class="setup-section space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <Bike class="h-5 w-5 text-primary" />
                    <h2 class="font-display text-2xl font-black uppercase italic tracking-tight text-white">VECTEUR_DÉPLACEMENT</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <button v-for="m in [
                        { id: 'bike', icon: Bike, label: 'PIED / VÉLO', dist: '< 8 KM' },
                        { id: 'moto', icon: Zap, label: 'MOTO', dist: '< 20 KM' },
                        { id: 'car', icon: Car, label: 'VOITURE', dist: '< 100 KM' }
                    ]" :key="m.id" @click="transportMode = m.id" 
                    :class="cn('p-6 rounded-[2rem] border-2 transition-all flex flex-col items-center gap-3 group', transportMode === m.id ? 'bg-primary/10 border-primary shadow-[0_0_20px_rgba(6,182,212,0.1)]' : 'hud-glass-card border-white/5 hover:border-white/20')">
                        <component :is="m.icon" :class="cn('h-8 w-8 transition-colors', transportMode === m.id ? 'text-primary' : 'text-white/40')" />
                        <span class="text-[10px] font-black uppercase tracking-widest text-white">{{ m.label }}</span>
                        <span class="text-[8px] text-white/30 font-black tracking-widest">{{ m.dist }}</span>
                    </button>
                </div>
            </section>

            <!-- NIVEAU DE DIFFICULTÉ -->
            <section class="setup-section space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <Gauge class="h-5 w-5 text-primary" />
                    <h2 class="font-display text-2xl font-black uppercase italic tracking-tight text-white">NIVEAU_TACTIQUE</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <button v-for="d in [
                        { id: 'easy', label: 'FACILE', color: 'border-green-500/40 text-green-400 bg-green-500/5' },
                        { id: 'medium', label: 'MOYEN', color: 'border-amber-500/40 text-amber-400 bg-amber-500/5' },
                        { id: 'hard', label: 'DIFFICILE', color: 'border-red-500/40 text-red-400 bg-red-500/5' }
                    ]" :key="d.id" @click="difficulty = d.id" 
                    :class="cn('p-6 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-[0.4em]', difficulty === d.id ? d.color + ' shadow-[0_0_20px_rgba(255,255,255,0.05)]' : 'hud-glass-card border-white/5 text-white/40 hover:text-white')">
                        {{ d.label }}
                    </button>
                </div>
            </section>
        </template>

        <!-- ACTION BUTTON -->
        <div class="setup-section pt-12">
            <HUDButton
                @click="startAdventure"
                variant="primary"
                class="w-full h-20 rounded-[2.5rem] text-xl"
            >
                <div class="flex items-center gap-4">
                    {{ selectedMode === 'team' ? 'CONFIGURER_EQUIPE' : 'DEMARRER_RECHERCHE' }}
                    <ArrowRight class="h-6 w-6 group-hover:translate-x-2 transition-transform" />
                </div>
            </HUDButton>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
