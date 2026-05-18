<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Brain, Trophy, Clock, Zap, Star, ChevronLeft } from 'lucide-vue-next';

const props = defineProps({
    city: Object,
    quizzes: Array,
});
</script>

<template>
  <Head :title="`Quiz ${city.name} — CityPlay`" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12">
      <div class="mb-8">
        <Link :href="route('player.cities')" class="text-muted-foreground hover:text-white flex items-center gap-2 transition-colors">
          <ChevronLeft class="h-4 w-4" /> Retour aux villes
        </Link>
      </div>

      <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="text-xs text-electric uppercase tracking-widest font-bold">Mode Quiz & Devinette</div>
        <h1 class="font-display text-3xl md:text-5xl mt-2">Défis de <span class="text-electric neon-text">{{ city.name }}</span></h1>
        <p class="mt-3 text-muted-foreground">Répondez aux énigmes pour débloquer de nouveaux lieux et gagner des récompenses.</p>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div 
          v-for="quiz in quizzes" 
          :key="quiz.id" 
          class="group relative overflow-hidden rounded-3xl glass-strong p-6 border border-white/5 hover:border-electric/30 transition-all flex flex-col"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-2xl bg-electric/10 border border-electric/20 grid place-items-center text-electric group-hover:bg-electric group-hover:text-white transition-all">
              <Brain class="h-6 w-6" />
            </div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] px-2 py-1 rounded bg-white/5 text-muted-foreground uppercase font-bold tracking-tighter">{{ quiz.category }}</span>
            </div>
          </div>

          <h3 class="font-display text-lg text-white mb-2">{{ quiz.title }}</h3>
          <p class="text-xs text-muted-foreground mb-6 flex-1">{{ quiz.description }}</p>

          <div class="space-y-4 pt-4 border-t border-white/5">
            <div class="flex justify-between items-center text-[10px] uppercase font-black tracking-widest">
              <div class="flex items-center gap-1.5 text-muted-foreground">
                <Clock class="h-3 w-3" /> {{ quiz.time_limit }}s
              </div>
              <div class="flex items-center gap-1.5 text-electric">
                <Zap class="h-3 w-3" /> +{{ quiz.xp_reward }} XP
              </div>
            </div>

            <NeonButton :href="route('player.quiz', quiz.id)" class="w-full" size="sm">
              Commencer le Défi
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- PLACEHOLDER FOR 30+ QUIZZES -->
      <div v-if="quizzes.length < 5" class="mt-12 p-12 text-center glass rounded-3xl border border-white/5 opacity-50">
        <Info class="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
        <p class="text-sm text-muted-foreground">Plus de quiz seront débloqués au fur et à mesure de votre progression.</p>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
