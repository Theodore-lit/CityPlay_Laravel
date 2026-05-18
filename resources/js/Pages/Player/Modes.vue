<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Brain, Compass, ArrowRight, Zap, MapPin, Clock, Trophy, Users } from 'lucide-vue-next';

const props = defineProps({
    quizzes: Array
});

const selectMode = (mode) => {
    router.post(route('player.select-mode'), { mode });
};
</script>

<template>
  <Head title="Modes de Jeu — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 pb-28 md:pb-12">
      <div v-if="$page.props.flash?.success" class="mb-8 p-4 rounded-3xl bg-success/10 border border-success/20 text-success text-center font-display font-black tracking-widest uppercase text-xs animate-fade-up">
        {{ $page.props.flash?.success }}
      </div>

      <div class="text-center max-w-2xl mx-auto">
        <div class="text-[10px] text-primary uppercase tracking-[0.3em] font-black mb-2 animate-pulse">Choisissez votre destinée</div>
        <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">VOTRE <span class="text-primary">MISSION</span></h1>
      </div>

      <div class="mt-12 grid gap-6 md:grid-cols-2 max-w-4xl mx-auto">
        <!-- MODE QUIZ -->
        <div 
          @click="selectMode('quiz')"
          class="group relative overflow-hidden rounded-[2rem] glass-strong p-6 md:p-8 cursor-pointer border-white/20 hover-game flex flex-col justify-between"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/20 blur-[60px] group-hover:bg-primary/30 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
          
          <div class="relative">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-premium p-0.5 shadow-neon group-hover:scale-110 transition-transform duration-500 shrink-0">
                    <div class="w-full h-full rounded-[0.9rem] bg-white/10 backdrop-blur-md flex items-center justify-center">
                        <Brain class="h-7 w-7 text-white animate-pulse-soft" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-xl md:text-2xl text-foreground uppercase tracking-tight">Mode <span class="text-primary">Quiz</span></h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <Zap class="h-3 w-3 text-secondary" />
                        <span class="text-[9px] text-muted-foreground font-black uppercase tracking-widest">Récompense XP Max</span>
                    </div>
                </div>
            </div>
            
            <p class="mt-4 text-muted-foreground text-xs md:text-sm leading-relaxed line-clamp-2">
              Défiez votre esprit sur l'âme du Bénin. Histoire, culture et traditions : débloquez de nouveaux horizons.
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
            <span class="text-[10px] text-muted-foreground font-bold italic">30+ Défis actifs</span>
            <NeonButton size="sm" class="rounded-xl px-6 shadow-neon text-[10px]">
              DÉMARRER
            </NeonButton>
          </div>
        </div>

        <!-- MODE AVENTURE -->
        <div 
          @click="selectMode('aventure')"
          class="group relative overflow-hidden rounded-[2rem] glass-strong p-6 md:p-8 cursor-pointer border-white/20 hover-game flex flex-col justify-between"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-accent/20 blur-[60px] group-hover:bg-accent/30 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
          
          <div class="relative">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-accent p-0.5 shadow-purple group-hover:scale-110 transition-transform duration-500 shrink-0">
                    <div class="w-full h-full rounded-[0.9rem] bg-white/10 backdrop-blur-md flex items-center justify-center">
                        <Compass class="h-7 w-7 text-white animate-float-game" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-xl md:text-2xl text-foreground uppercase tracking-tight">Mode <span class="text-accent">Aventure</span></h2>
                    <div class="px-2 py-0.5 rounded bg-accent/10 text-accent text-[8px] font-black tracking-widest border border-accent/20 inline-block">
                        EN DIRECT
                    </div>
                </div>
            </div>
            
            <p class="mt-4 text-muted-foreground text-xs md:text-sm leading-relaxed line-clamp-2">
              Missions GPS réelles et secrets historiques basés sur votre position exacte.
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
              <div class="flex gap-2">
                  <div class="h-8 w-8 rounded-lg bg-white/50 border border-white/40 grid place-items-center text-accent shadow-sm" title="GPS Requis">
                      <Navigation class="h-4 w-4" />
                  </div>
                  <div class="h-8 w-8 rounded-lg bg-white/50 border border-white/40 grid place-items-center text-secondary shadow-sm" title="Carte Interactive">
                      <MapPin class="h-4 w-4" />
                  </div>
              </div>
              <NeonButton variant="purple" size="sm" class="rounded-xl px-6 shadow-purple text-[10px]">
                EXPLORER
              </NeonButton>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
