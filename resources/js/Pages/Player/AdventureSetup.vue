<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { User, Users, MapPin, ArrowRight, ShieldCheck, Zap, Terminal } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
    teams: Array,
});

const selectedMode = ref(null); // 'solo' or 'team'

const startAdventure = () => {
    if (selectedMode.value === 'solo') {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                router.post(route('player.adventure.solo', props.city.id), {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            }, () => {
                router.post(route('player.adventure.solo', props.city.id));
            });
        } else {
            router.post(route('player.adventure.solo', props.city.id));
        }
    } else if (selectedMode.value === 'team') {
        router.get(route('teams.index'));
    }
};
</script>

<template>
  <Head title="Configuration Aventure — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 pb-28 md:pb-12">

      <!-- HEADER DIRECTEMENT INSPIRÉ -->
      <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-2 animate-pulse">Configuration de Mission</div>
        <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">VOTRE <span class="text-electric">STRATÉGIE</span></h1>

        <p class="mt-4 text-muted-foreground text-xs md:text-sm flex items-center justify-center gap-2 bg-white/5 border border-white/10 w-fit mx-auto px-4 py-1.5 rounded-2xl backdrop-blur-md">
            <MapPin class="h-4 w-4 text-electric animate-bounce" /> Destination : <span class="text-white font-semibold">{{ city.name }}</span>
        </p>
      </div>

      <!-- GRILLE DES CONFIGURATIONS AVEC STRUGTURE GLASS-STRONG -->
      <div class="grid gap-6 md:grid-cols-2 max-w-4xl mx-auto">

        <!-- CARTE MODE SOLO -->
        <div
          @click="selectedMode = 'solo'"
          :class="[
            'group relative overflow-hidden rounded-[2rem] glass-strong p-6 md:p-8 cursor-pointer hover-game flex flex-col justify-between border-2 transition-all duration-500',
            selectedMode === 'solo'
              ? 'border-electric shadow-neon bg-electric/10 scale-[1.02]'
              : 'border-white/20'
          ]"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-electric/20 blur-[60px] group-hover:bg-electric/30 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

          <div class="relative">
            <div class="flex items-center gap-4">
              <!-- Effet dégradé premium dynamique -->
              <div :class="[
                'h-14 w-14 rounded-2xl p-0.5 transition-transform duration-500 shrink-0',
                selectedMode === 'solo' ? 'bg-electric scale-110 shadow-neon' : 'bg-gradient-premium shadow-neon group-hover:scale-110'
              ]">
                <div class="w-full h-full rounded-[0.9rem] bg-white/10 backdrop-blur-md flex items-center justify-center">
                  <User class="h-7 w-7 text-white" />
                </div>
              </div>
              <div>
                <h2 class="font-display text-xl md:text-2xl text-foreground uppercase tracking-tight">MODE <span class="text-electric">SOLO</span></h2>
                <div class="flex items-center gap-2 mt-0.5">
                  <Zap class="h-3 w-3 text-electric animate-pulse" />
                  <span class="text-[9px] text-muted-foreground font-black uppercase tracking-widest">Infiltration Autonome</span>
                </div>
              </div>
            </div>

            <p class="mt-4 text-muted-foreground text-xs md:text-sm leading-relaxed">
              Explorez la ville à votre propre rythme. Idéal pour les loups solitaires et les explorateurs contemplatifs basant leurs recherches sur nos capteurs de proximité.
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-white/10 flex flex-col gap-3">
            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                <ShieldCheck class="h-3.5 w-3.5 text-electric" /> Progression individuelle
              </li>
              <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                <Terminal class="h-3.5 w-3.5 text-electric" /> Capteurs de proximité activés
              </li>
            </ul>
          </div>
        </div>

        <!-- CARTE MODE ÉQUIPE -->
        <div
          @click="selectedMode = 'team'"
          :class="[
            'group relative overflow-hidden rounded-[2rem] glass-strong p-6 md:p-8 cursor-pointer hover-game flex flex-col justify-between border-2 transition-all duration-500',
            selectedMode === 'team'
              ? 'border-secondary shadow-purple bg-secondary/10 scale-[1.02]'
              : 'border-white/20'
          ]"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-secondary/20 blur-[60px] group-hover:bg-secondary/30 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

          <div class="relative">
            <div class="flex items-center gap-4">
              <!-- Effet dégradé premium dynamique -->
              <div :class="[
                'h-14 w-14 rounded-2xl p-0.5 transition-transform duration-500 shrink-0',
                selectedMode === 'team' ? 'bg-secondary scale-110 shadow-purple' : 'bg-gradient-accent shadow-purple group-hover:scale-110'
              ]">
                <div class="w-full h-full rounded-[0.9rem] bg-white/10 backdrop-blur-md flex items-center justify-center">
                  <Users class="h-7 w-7 text-white" />
                </div>
              </div>
              <div>
                <h2 class="font-display text-xl md:text-2xl text-foreground uppercase tracking-tight">MODE <span class="text-secondary">ÉQUIPE</span></h2>
                <div class="px-2 py-0.5 rounded bg-secondary/10 text-secondary text-[8px] font-black tracking-widest border border-secondary/20 inline-block mt-0.5">
                  RECRUTEMENT EN COURS
                </div>
              </div>
            </div>

            <p class="mt-4 text-muted-foreground text-xs md:text-sm leading-relaxed">
              Collaborez avec vos amis pour résoudre les mystères plus rapidement. Synchronisez vos terminaux et triangulez les secrets en groupe.
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-white/10 flex flex-col gap-3">
            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                <ShieldCheck class="h-3.5 w-3.5 text-secondary" /> Score partagé & canaux sécurisés
              </li>
              <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                <ShieldCheck class="h-3.5 w-3.5 text-secondary" /> Clé d'accès cryptée unique
              </li>
            </ul>
          </div>
        </div>

      </div>

      <!-- SECTION COMMANDE CENTRALISÉE EN DESSOUS DES DEUX CARTES -->
      <div class="mt-12 flex flex-col items-center justify-center gap-3">
        <NeonButton
            @click="startAdventure"
            :disabled="!selectedMode"
            size="xl"
            class="rounded-2xl px-12 group transition-transform active:scale-95"
            :variant="selectedMode === 'team' ? 'purple' : 'default'"
        >
            {{ selectedMode === 'team' ? 'CONFIGURER MON ÉQUIPE' : 'DÉMARRER L\'AVENTURE' }}
            <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
        </NeonButton>
        <p v-if="!selectedMode" class="text-[10px] font-mono uppercase tracking-widest text-muted-foreground/60 animate-pulse">
          En attente de sélection de la stratégie d'exploration...
        </p>
      </div>

    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
