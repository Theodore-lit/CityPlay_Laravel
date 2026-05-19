<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { User, Users, MapPin, ArrowRight, ShieldCheck, Zap, Terminal, Bike, Info, Car, Gauge, Target } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
    teams: Array,
});

const selectedMode = ref('solo'); // 'solo' par défaut
const transportMode = ref('bike'); // 'bike', 'moto', 'car'
const difficulty = ref('medium'); // 'easy', 'medium', 'hard'

const startAdventure = () => {
    if (selectedMode.value === 'solo') {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                router.post(route('player.adventure.solo', props.city.id), {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    transport: transportMode.value,
                    difficulty: difficulty.value
                });
            }, () => {
                // En cas d'erreur ou de refus, on ne peut pas filtrer par distance
                alert("La géolocalisation est requise pour le mode exploration.");
            });
        } else {
            alert("Votre navigateur ne supporte pas la géolocalisation.");
        }
    } else if (selectedMode.value === 'team') {
        router.get(route('teams.index'));
    }
};
</script>

<template>
  <Head title="Configuration Mission — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12">
      <!-- HEADER -->
      <div class="text-center max-w-2xl mx-auto mb-10">
        <div class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-2 animate-pulse">Système de Navigation</div>
        <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">CONFIGURER <span class="text-electric">LA MISSION</span></h1>
        <p class="mt-4 text-muted-foreground text-xs md:text-sm flex items-center justify-center gap-2 bg-white/5 border border-white/10 w-fit mx-auto px-4 py-1.5 rounded-2xl backdrop-blur-md text-foreground">
            <MapPin class="h-4 w-4 text-electric animate-bounce" /> {{ city.name }}
        </p>
      </div>

      <div class="max-w-4xl mx-auto space-y-8">
        <!-- MODE DE JEU -->
        <section class="space-y-4">
            <div class="flex items-center gap-3 mb-2">
                <Users class="h-5 w-5 text-electric" />
                <h2 class="font-display text-xl uppercase tracking-wider">Formation</h2>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <button @click="selectedMode = 'solo'" :class="['p-4 rounded-2xl border-2 transition-all text-left group', selectedMode === 'solo' ? 'bg-electric/10 border-electric shadow-neon' : 'glass border-white/10 hover:border-white/20']">
                    <User class="h-6 w-6 mb-2 text-electric" />
                    <div class="font-bold text-sm">SOLO</div>
                    <div class="text-[10px] text-muted-foreground uppercase tracking-widest">Exploration libre</div>
                </button>
                <button @click="selectedMode = 'team'" :class="['p-4 rounded-2xl border-2 transition-all text-left group', selectedMode === 'team' ? 'bg-secondary/10 border-secondary shadow-purple' : 'glass border-white/10 hover:border-white/20']">
                    <Users class="h-6 w-6 mb-2 text-secondary" />
                    <div class="font-bold text-sm">ÉQUIPE</div>
                    <div class="text-[10px] text-muted-foreground uppercase tracking-widest">Coopération</div>
                </button>
            </div>
        </section>

        <template v-if="selectedMode === 'solo'">
            <!-- MOYEN DE DÉPLACEMENT -->
            <section class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <Bike class="h-5 w-5 text-electric" />
                    <h2 class="font-display text-xl uppercase tracking-wider">Moyen de transport</h2>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="transportMode = 'bike'" :class="['p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2', transportMode === 'bike' ? 'bg-electric/10 border-electric shadow-neon' : 'glass border-white/10']">
                        <Bike class="h-6 w-6" />
                        <span class="text-[10px] font-black uppercase">Pied / Vélo</span>
                        <span class="text-[8px] text-muted-foreground font-bold">&lt; 8 KM</span>
                    </button>
                    <button @click="transportMode = 'moto'" :class="['p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2', transportMode === 'moto' ? 'bg-electric/10 border-electric shadow-neon' : 'glass border-white/10']">
                        <Zap class="h-6 w-6" />
                        <span class="text-[10px] font-black uppercase">Moto</span>
                        <span class="text-[8px] text-muted-foreground font-bold">&lt; 20 KM</span>
                    </button>
                    <button @click="transportMode = 'car'" :class="['p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2', transportMode === 'car' ? 'bg-electric/10 border-electric shadow-neon' : 'glass border-white/10']">
                        <Car class="h-6 w-6" />
                        <span class="text-[10px] font-black uppercase">Voiture</span>
                        <span class="text-[8px] text-muted-foreground font-bold">&lt; 100 KM</span>
                    </button>
                </div>
            </section>

            <!-- NIVEAU DE DIFFICULTÉ -->
            <section class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <Gauge class="h-5 w-5 text-electric" />
                    <h2 class="font-display text-xl uppercase tracking-wider">Niveau Tactique</h2>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="difficulty = 'easy'" :class="['p-4 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-widest', difficulty === 'easy' ? 'bg-success/20 border-success text-success shadow-neon-success' : 'glass border-white/10']">
                        Facile
                    </button>
                    <button @click="difficulty = 'medium'" :class="['p-4 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-widest', difficulty === 'medium' ? 'bg-warning/20 border-warning text-warning shadow-neon-warning' : 'glass border-white/10']">
                        Moyen
                    </button>
                    <button @click="difficulty = 'hard'" :class="['p-4 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-widest', difficulty === 'hard' ? 'bg-destructive/20 border-destructive text-destructive shadow-neon-error' : 'glass border-white/10']">
                        Difficile
                    </button>
                </div>
            </section>
        </template>

        <!-- ACTION BUTTON -->
        <div class="pt-8">
            <NeonButton
                @click="startAdventure"
                size="xl"
                class="w-full rounded-[2rem] group"
            >
                {{ selectedMode === 'team' ? 'CONFIGURER L\'ÉQUIPE' : 'DÉMARRER LA RECHERCHE' }}
                <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
            </NeonButton>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
