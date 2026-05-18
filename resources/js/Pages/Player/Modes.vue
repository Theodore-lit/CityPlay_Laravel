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
      <div v-if="$page.props.flash?.success" class="mb-8 p-4 rounded-2xl bg-success/20 border border-success/40 text-success text-center font-bold animate-fade-up">
        {{ $page.props.flash?.success }}
      </div>

      <div class="text-center max-w-2xl mx-auto">
        <div class="text-xs text-electric uppercase tracking-widest font-bold">Choisissez votre voie</div>
        <h1 class="font-display text-3xl md:text-5xl mt-2">Comment allez-vous <span class="text-electric neon-text">explorer ?</span></h1>
        <p class="mt-3 text-muted-foreground">Deux modes uniques. Une aventure inoubliable.</p>
      </div>

      <div class="mt-12 grid gap-8 md:grid-cols-2">
        <!-- MODE QUIZ -->
        <div
          @click="selectMode('quiz')"
          class="group relative overflow-hidden rounded-3xl glass-strong p-8 md:p-10 border border-electric/10 cursor-pointer hover:border-electric/40 transition-all"
        >
          <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-electric/20 blur-3xl" />
          <div class="absolute inset-0 grid-bg opacity-10" />

          <div class="relative">
            <div class="h-16 w-16 rounded-2xl bg-white/5 border border-electric/20 grid place-items-center shadow-neon">
              <Brain class="h-8 w-8 text-electric neon-text" />
            </div>
            <h2 class="mt-6 font-display text-3xl text-foreground">Mode Quiz & Devinette</h2>
            <p class="mt-3 text-muted-foreground text-sm leading-relaxed">
              Testez vos connaissances sur le Bénin. Histoire, culture, géographie : débloquez des lieux en répondant correctement aux énigmes.
            </p>

            <div class="mt-8 flex items-center justify-between">
              <span class="text-xs text-muted-foreground">30+ Quiz par ville • XP & Récompenses</span>
              <NeonButton size="sm">Sélectionner</NeonButton>
            </div>
          </div>
        </div>

        <!-- MODE AVENTURE -->
        <div
          @click="selectMode('aventure')"
          class="group relative overflow-hidden rounded-3xl glass-strong p-8 md:p-10 border border-purple-neon/10 cursor-pointer hover:border-purple-neon/40 transition-all"
        >
          <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-purple-neon/20 blur-3xl" />
          <div class="absolute inset-0 grid-bg opacity-10" />

          <div class="relative flex flex-col h-full">
            <div class="flex justify-between items-start">
                <div class="h-16 w-16 rounded-2xl bg-white/5 border border-purple-neon/20 grid place-items-center shadow-purple">
                    <Compass class="h-8 w-8 text-purple-neon neon-text-purple" />
                </div>
                <div class="px-3 py-1 rounded-full bg-purple-neon/20 text-purple-neon text-[10px] font-display font-black tracking-widest border border-purple-neon/40 animate-pulse">
                    EN DIRECT
                </div>
            </div>

            <h2 class="mt-6 font-display text-3xl text-foreground">Mode Aventure</h2>
            <p class="mt-3 text-muted-foreground text-sm leading-relaxed">
              Vivez l'histoire sur le terrain. Missions GPS réelles, scan de QR codes historiques et énigmes immersives basées sur votre position.
            </p>

            <div class="mt-8 flex items-center justify-between">
                <div class="flex gap-2">
                    <div class="px-3 py-1 rounded-xl bg-white/5 border border-white/10 text-[10px] text-foreground">GPS ACTIF</div>
                    <div class="px-3 py-1 rounded-xl bg-white/5 border border-white/10 text-[10px] text-foreground">CARTE</div>
                </div>
                <NeonButton variant="purple" size="sm">Sélectionner</NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
