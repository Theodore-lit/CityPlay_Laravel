<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Brain, Compass, ArrowRight, Zap, MapPin, Clock, Trophy, Users, Navigation } from 'lucide-vue-next';

const props = defineProps({
    quizzes: Array
});

const selectMode = (mode) => {
    router.post(route('player.select-mode'), { mode });
};
</script>

<template>
  <Head title="Modes de Jeu — CityPlay" />

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-7xl px-6 py-12 pb-28 md:pb-12 relative z-10">
      <div v-if="$page.props.flash?.success" class="mb-10 p-4 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-400 text-center font-black tracking-[0.3em] uppercase text-[10px] animate-fade-up shadow-[0_0_20px_rgba(34,197,94,0.1)]">
        {{ $page.props.flash?.success }}
      </div>

      <div class="flex flex-col items-center text-center mb-16">
        <div class="text-[10px] text-primary font-black uppercase tracking-[0.5em] mb-4 animate-pulse">INIT_DESTINY_SELECTION</div>
        <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
          CHOISISSEZ VOTRE <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">MISSION</span>
        </h1>
      </div>

      <div class="mt-12 grid gap-10 md:grid-cols-2 max-w-5xl mx-auto">
        <!-- MODE QUIZ -->
        <div 
          @click="selectMode('quiz')"
          class="hud-glass-card group relative overflow-hidden rounded-[2.5rem] p-8 md:p-12 cursor-pointer border-2 border-white/5 hover:border-primary/40 flex flex-col justify-between transition-all duration-700 h-[450px]"
        >
          <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-primary/10 blur-[80px] group-hover:bg-primary/20 transition-all duration-700" />
          <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
          
          <div class="relative z-10">
            <div class="flex items-center gap-6">
                <div class="h-20 w-20 rounded-2xl bg-primary/20 border-2 border-primary/40 p-1 shadow-[0_0_30px_rgba(6,182,212,0.2)] group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shrink-0 relative overflow-hidden">
                    <div class="absolute inset-0 bg-primary/10 animate-pulse" />
                    <div class="w-full h-full rounded-xl bg-zinc-900 flex items-center justify-center border border-white/10 relative z-10">
                        <Brain class="h-10 w-10 text-primary drop-shadow-[0_0_8px_#06b6d4]" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-2xl md:text-3xl text-white font-black uppercase italic tracking-tighter">MODE <span class="text-primary">QUIZ</span></h2>
                    <div class="flex items-center gap-2 mt-1">
                        <Zap class="h-3.5 w-3.5 text-primary" />
                        <span class="text-[9px] text-white/40 font-black uppercase tracking-[0.2em]">RÉCOMPENSE XP_MAX</span>
                    </div>
                </div>
            </div>
            
            <p class="mt-8 text-white/60 text-sm md:text-base leading-relaxed font-bold uppercase tracking-widest opacity-80 group-hover:opacity-100 transition-opacity">
              Défiez votre esprit sur l'âme du Bénin. Histoire, culture et traditions : débloquez de nouveaux horizons de données.
            </p>
          </div>

          <div class="mt-10 pt-8 border-t border-white/5 flex items-center justify-between relative z-10">
            <div class="flex flex-col">
                <span class="text-[10px] text-white/40 font-black tracking-widest uppercase">SYSTÈME_ACTIF</span>
                <span class="text-[12px] text-primary font-black italic">30+ DÉFIS_DISPONIBLES</span>
            </div>
            <HUDButton variant="primary" class="rounded-xl px-10">
              DÉMARRER
            </HUDButton>
          </div>
        </div>

        <!-- MODE AVENTURE -->
        <div 
          @click="selectMode('aventure')"
          class="hud-glass-card group relative overflow-hidden rounded-[2.5rem] p-8 md:p-12 cursor-pointer border-2 border-white/5 hover:border-purple-500/40 flex flex-col justify-between transition-all duration-700 h-[450px]"
        >
          <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-purple-500/10 blur-[80px] group-hover:bg-purple-500/20 transition-all duration-700" />
          <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
          
          <div class="relative z-10">
            <div class="flex items-center gap-6">
                <div class="h-20 w-20 rounded-2xl bg-purple-500/20 border-2 border-purple-500/40 p-1 shadow-[0_0_30px_rgba(217,70,239,0.2)] group-hover:scale-110 group-hover:-rotate-3 transition-all duration-500 shrink-0 relative overflow-hidden">
                    <div class="absolute inset-0 bg-purple-500/10 animate-pulse" />
                    <div class="w-full h-full rounded-xl bg-zinc-900 flex items-center justify-center border border-white/10 relative z-10">
                        <Compass class="h-10 w-10 text-purple-500 drop-shadow-[0_0_8px_#d946ef]" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-2xl md:text-3xl text-white font-black uppercase italic tracking-tighter">MODE <span class="text-purple-500">AVENTURE</span></h2>
                    <div class="mt-2 px-3 py-1 rounded bg-purple-500/10 text-purple-500 text-[9px] font-black tracking-[0.3em] border border-purple-500/20 inline-block uppercase animate-pulse">
                        FLUX_LIVE_GPS
                    </div>
                </div>
            </div>
            
            <p class="mt-8 text-white/60 text-sm md:text-base leading-relaxed font-bold uppercase tracking-widest opacity-80 group-hover:opacity-100 transition-opacity">
              Missions GPS réelles et secrets historiques basés sur votre position exacte. Exploration physique du terrain requise.
            </p>
          </div>

          <div class="mt-10 pt-8 border-t border-white/5 flex items-center justify-between relative z-10">
              <div class="flex gap-3">
                  <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-purple-500 group-hover:border-purple-500/40 transition-all" title="GPS Requis">
                      <Navigation class="h-5 w-5" />
                  </div>
                  <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-primary group-hover:border-primary/40 transition-all" title="Carte Interactive">
                      <MapPin class="h-5 w-5" />
                  </div>
              </div>
              <HUDButton variant="magenta" class="rounded-xl px-10">
                EXPLORER
              </HUDButton>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
