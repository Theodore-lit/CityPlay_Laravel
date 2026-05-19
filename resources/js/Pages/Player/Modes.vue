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
          class="group relative overflow-hidden rounded-[2.5rem] glass-strong p-8 md:p-10 cursor-pointer border-white/5 hover-game flex flex-col justify-between transition-all duration-500 hover:border-primary/30"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/10 blur-[60px] group-hover:bg-primary/20 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
          
          <div class="relative">
            <div class="flex items-center gap-5">
                <div class="h-16 w-16 rounded-2xl bg-gradient-adventure p-0.5 shadow-2xl shadow-primary/20 group-hover:scale-110 transition-transform duration-500 shrink-0">
                    <div class="w-full h-full rounded-[0.9rem] bg-gaming-darker flex items-center justify-center">
                        <Brain class="h-8 w-8 text-primary animate-pulse-soft" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-2xl text-white uppercase italic font-black tracking-tight">Mode <span class="text-primary">Quiz</span></h2>
                    <div class="flex items-center gap-2 mt-1">
                        <Zap class="h-3 w-3 text-secondary" />
                        <span class="text-[9px] text-muted-foreground font-black uppercase tracking-widest">Récompense XP Max</span>
                    </div>
                </div>
            </div>
            
            <p class="mt-6 text-muted-foreground text-sm leading-relaxed">
              Défiez votre esprit sur l'âme du Bénin. Histoire, culture et traditions : débloquez de nouveaux horizons et gagnez des récompenses exclusives.
            </p>
          </div>

          <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between">
            <span class="text-[10px] text-muted-foreground font-black uppercase tracking-widest">30+ Défis actifs</span>
            <NeonButton size="sm" class="rounded-xl px-8 bg-gradient-adventure border-none text-[10px] font-black uppercase tracking-widest">
              DÉMARRER
            </NeonButton>
          </div>
        </div>

        <!-- MODE AVENTURE -->
        <div 
          @click="selectMode('aventure')"
          class="group relative overflow-hidden rounded-[2.5rem] glass-strong p-8 md:p-10 cursor-pointer border-white/5 hover-game flex flex-col justify-between transition-all duration-500 hover:border-accent/30"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-accent/10 blur-[60px] group-hover:bg-accent/20 transition-colors" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
          
          <div class="relative">
            <div class="flex items-center gap-5">
                <div class="h-16 w-16 rounded-2xl bg-gradient-accent p-0.5 shadow-2xl shadow-accent/20 group-hover:scale-110 transition-transform duration-500 shrink-0">
                    <div class="w-full h-full rounded-[0.9rem] bg-gaming-darker flex items-center justify-center">
                        <Compass class="h-8 w-8 text-accent animate-float-game" />
                    </div>
                </div>
                <div>
                    <h2 class="font-display text-2xl text-white uppercase italic font-black tracking-tight">Mode <span class="text-accent">Aventure</span></h2>
                    <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded bg-accent/10 text-accent text-[8px] font-black tracking-widest border border-accent/20">
                        IMMERSION GPS
                    </div>
                </div>
            </div>
            
            <p class="mt-6 text-muted-foreground text-sm leading-relaxed">
              Missions réelles basées sur votre position. Explorez les palais, découvrez des secrets cachés et vivez l'histoire en temps réel.
            </p>
          </div>

          <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between">
              <div class="flex gap-2">
                  <div class="h-10 w-10 rounded-xl glass border-white/10 grid place-items-center text-accent shadow-sm" title="GPS Requis">
                      <MapPin class="h-5 w-5" />
                  </div>
                  <div class="h-10 w-10 rounded-xl glass border-white/10 grid place-items-center text-primary shadow-sm" title="Réalité Augmentée">
                      <Sparkles class="h-5 w-5" />
                  </div>
              </div>
              <NeonButton variant="purple" size="sm" class="rounded-xl px-8 shadow-2xl shadow-accent/20 text-[10px] font-black uppercase tracking-widest">
                EXPLORER
              </NeonButton>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
