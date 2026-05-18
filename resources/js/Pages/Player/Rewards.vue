<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head } from '@inertiajs/vue3';
import { Crown, Sparkles, Flame, Compass, Shield, Star, Lock, Award, Gem, Skull } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const cats = [
  {
    title: "Collection Royale",
    color: "text-warning",
    badges: [
      { icon: Crown, name: "Savant Royal", rare: "légendaire", unlocked: true, desc: "Tous les quiz d'Abomey" },
      { icon: Shield, name: "Gardien", rare: "épique", unlocked: true, desc: "A défendu une série d'énigmes" },
      { icon: Gem, name: "Gardien du Trône", rare: "légendaire", unlocked: false, desc: "Compléter chaque mission royale" },
      { icon: Star, name: "Étoile du Bénin", rare: "rare", unlocked: true, desc: "A atteint le niveau 20" },
    ],
  },
  {
    title: "Série Explorateur",
    color: "text-electric",
    badges: [
      { icon: Compass, name: "Éclaireur", rare: "commun", unlocked: true, desc: "Première aventure" },
      { icon: Flame, name: "Maître de Série", rare: "épique", unlocked: true, desc: "Série de 30 jours" },
      { icon: Skull, name: "Chasseur Nocturne", rare: "rare", unlocked: false, desc: "Résoudre après minuit" },
      { icon: Award, name: "Centurion", rare: "épique", unlocked: false, desc: "Compléter 100 missions" },
    ],
  },
];

const rareColors = {
  légendaire: "border-warning text-warning shadow-[0_0_24px_oklch(0.82_0.17_80/0.5)]",
  épique: "border-purple-neon text-purple-neon shadow-purple",
  rare: "border-electric text-electric shadow-neon",
  commun: "border-muted-foreground/40 text-muted-foreground",
};
</script>

<template>
  <Head title="Récompenses — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="text-center">
        <Sparkles class="h-10 w-10 mx-auto text-purple-neon animate-pulse-glow rounded-lg p-1" />
        <h1 class="font-display text-3xl md:text-5xl mt-3 text-white">Coffre aux <span class="text-electric neon-text">Trophées</span></h1>
        <p class="mt-2 text-muted-foreground">12 sur 48 badges débloqués. La légende grandit.</p>
      </div>

      <div class="mt-8 grid grid-cols-3 gap-4 max-w-xl mx-auto">
        <div v-for="s in [
          { v: 12, l: 'Débloqués', c: 'text-electric' }, 
          { v: 3, l: 'Légendaires', c: 'text-warning' }, 
          { v: 36, l: 'Restants', c: 'text-muted-foreground' }
        ]" :key="s.l" class="rounded-2xl glass p-4 text-center">
          <div :class="cn('font-display text-2xl md:text-3xl', s.c)">{{ s.v }}</div>
          <div class="text-[10px] uppercase tracking-widest text-muted-foreground mt-1">{{ s.l }}</div>
        </div>
      </div>

      <div class="mt-12 space-y-12">
        <div v-for="cat in cats" :key="cat.title">
          <h2 :class="cn('font-display text-xl flex items-center gap-2', cat.color)">
            <Sparkles class="h-4 w-4" />{{ cat.title }}
          </h2>
          <div class="mt-5 grid gap-4 grid-cols-2 md:grid-cols-4">
            <div
              v-for="(b, i) in cat.badges"
              :key="b.name"
              :class="cn(
                'group relative rounded-2xl glass-strong p-5 border-2 hover-lift text-center animate-fade-up',
                b.unlocked ? rareColors[b.rare] : 'border-electric/10 opacity-60'
              )"
              :style="{ animationDelay: `${i * 60}ms` }"
            >
              <div v-if="!b.unlocked" class="absolute inset-0 rounded-2xl bg-gaming-darker/60 backdrop-blur-sm grid place-items-center z-10">
                <Lock class="h-6 w-6 text-muted-foreground" />
              </div>
              
              <div :class="cn(
                'h-16 w-16 mx-auto rounded-2xl grid place-items-center border-2 transition-all',
                b.unlocked ? rareColors[b.rare] : 'border-electric/20',
                b.unlocked && b.rare === 'légendaire' ? 'animate-pulse-glow' : ''
              )">
                <component :is="b.icon" class="h-7 w-7" />
              </div>
              <div class="mt-3 font-display text-sm text-white">{{ b.name }}</div>
              <div class="mt-1 text-[10px] uppercase tracking-widest text-muted-foreground">{{ b.rare }}</div>
              <div class="mt-2 text-xs text-muted-foreground leading-tight">{{ b.desc }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
