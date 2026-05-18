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
        <h1 class="font-display text-3xl md:text-5xl mt-3 text-foreground">Coffre aux <span class="text-electric neon-text">Trophées</span></h1>
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
          <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div v-for="b in cat.badges" :key="b.name" 
                 :class="cn(
                    'relative glass-strong rounded-[1.5rem] p-4 text-center border-white/20 transition-all duration-500 hover-game overflow-hidden group',
                    b.unlocked ? 'opacity-100' : 'opacity-40 grayscale'
                 )">
                <div v-if="b.unlocked" class="absolute -top-10 -right-10 h-24 w-24 rounded-full bg-primary/10 blur-2xl group-hover:bg-primary/20 transition-colors" />
                
                <div class="relative z-10">
                  <div :class="cn(
                    'h-12 w-12 mx-auto rounded-xl grid place-items-center border transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-sm',
                    b.unlocked ? rareColors[b.rare] : 'border-white/10 bg-white/5',
                    b.unlocked && b.rare === 'légendaire' ? 'animate-pulse-soft' : ''
                  )">
                    <component :is="b.icon" class="h-6 w-6" />
                  </div>
                  <div class="mt-3 font-display text-[10px] font-black text-foreground uppercase tracking-tight truncate">{{ b.name }}</div>
                  <div class="mt-1 text-[8px] uppercase tracking-[0.2em] text-muted-foreground font-black">{{ b.rare }}</div>
                  
                  <div class="mt-3 pt-3 border-t border-white/5">
                      <p class="text-[9px] text-muted-foreground leading-tight line-clamp-2 italic font-medium">{{ b.desc }}</p>
                  </div>
                </div>

                <div v-if="!b.unlocked" class="absolute inset-0 bg-background/60 backdrop-blur-[2px] grid place-items-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <Lock class="h-6 w-6 text-muted-foreground/40" />
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
