<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
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

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-6xl px-6 py-10 pb-28 md:pb-12 relative z-10">
      <div class="flex flex-col items-center text-center mb-12">
        <div class="flex items-center gap-2 mb-4">
            <div class="h-px w-12 bg-purple-500/30" />
            <Sparkles class="h-6 w-6 text-purple-500 drop-shadow-[0_0_10px_#d946ef]" />
            <div class="h-px w-12 bg-purple-500/30" />
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white mb-2">
            COFFRE AUX <span class="text-purple-500 drop-shadow-[0_0_15px_#d946ef]">TROPHEES</span>
        </h1>
        <div class="text-[10px] font-black tracking-[0.5em] text-white/60 uppercase">DATA_COLLECTION_STATUS // 12 / 48 BADGES</div>
      </div>

      <div class="mt-8 grid grid-cols-3 gap-4 max-w-xl mx-auto mb-16">
        <div v-for="s in [
          { v: 12, l: 'DÉBLOQUÉS', c: 'text-primary' },
          { v: 3, l: 'LÉGENDAIRES', c: 'text-amber-500' },
          { v: 36, l: 'RESTANTS', c: 'text-white/30' }
        ]" :key="s.l" class="hud-glass-card rounded-2xl p-6 text-center border border-white/5 relative group overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div :class="cn('font-display text-3xl md:text-4xl font-black italic drop-shadow-[0_0_10px_rgba(255,255,255,0.1)]', s.c)">{{ s.v }}</div>
          <div class="text-[8px] font-black uppercase tracking-[0.3em] text-white/60 mt-2">{{ s.l }}</div>
        </div>
      </div>

      <div class="mt-12 space-y-20">
        <div v-for="cat in cats" :key="cat.title" class="relative">
          <div class="flex items-center gap-4 mb-8">
            <h2 :class="cn('font-display text-2xl font-black uppercase italic tracking-tighter flex items-center gap-3', cat.color === 'text-warning' ? 'text-amber-500' : 'text-primary')">
              <div class="h-2 w-2 rounded-full bg-current animate-pulse" />
              {{ cat.title.toUpperCase() }}
            </h2>
            <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent" />
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <div v-for="b in cat.badges" :key="b.name" 
                 :class="cn(
                    'relative hud-glass-card rounded-[2.5rem] p-6 text-center border-2 transition-all duration-700 overflow-hidden group',
                    b.unlocked ? 'border-white/10 hover:border-primary/40' : 'border-white/5 opacity-40 grayscale hover:opacity-60'
                 )">
                
                <!-- Background Glow -->
                <div v-if="b.unlocked" class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-primary/10 blur-3xl group-hover:bg-primary/20 transition-all duration-700" />
                
                <div class="relative z-10 flex flex-col items-center">
                  <div :class="cn(
                    'h-16 w-16 rounded-2xl grid place-items-center border-2 transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-xl relative',
                    b.unlocked ? (b.rare === 'légendaire' ? 'border-amber-500 bg-amber-500/10 text-amber-500 shadow-amber-500/20' : 'border-primary bg-primary/10 text-primary shadow-primary/20') : 'border-white/10 bg-white/5 text-white/20'
                  )">
                    <div class="absolute inset-0 bg-current opacity-10 rounded-xl" />
                    <component :is="b.icon" class="h-8 w-8 relative z-10 drop-shadow-[0_0_8px_rgba(0,0,0,0.5)]" />
                  </div>
                  
                  <div class="mt-5 font-display text-[11px] font-black text-white uppercase italic tracking-tight leading-tight min-h-[2rem] flex items-center justify-center">{{ b.name }}</div>
                  <div :class="cn('mt-1 text-[7px] font-black uppercase tracking-[0.4em]', b.unlocked ? 'text-primary' : 'text-white/30')">{{ b.rare }}</div>
                  
                  <div class="mt-4 pt-4 border-t border-white/5 w-full">
                      <p class="text-[9px] text-white/60 leading-relaxed font-bold uppercase tracking-widest line-clamp-2">{{ b.desc }}</p>
                  </div>
                </div>

                <!-- LOCK OVERLAY -->
                <div v-if="!b.unlocked" class="absolute inset-0 bg-zinc-950/40 backdrop-blur-[1px] grid place-items-center opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="h-10 w-10 rounded-full border border-white/20 flex items-center justify-center bg-black/60">
                        <Lock class="h-4 w-4 text-white/40" />
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
