<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head } from '@inertiajs/vue3';
import { Trophy, Crown, Medal, Flame } from 'lucide-vue-next';
import { ref } from 'vue';
import { cn } from '@/lib/utils';

const tab = ref('hebdomadaire');

const players = [
  { name: "Kossi M.", xp: 48230, country: "🇧🇯", streak: 64 },
  { name: "Aïcha K.", xp: 42180, country: "🇧🇯", streak: 32 },
  { name: "Léa T.", xp: 39750, country: "🇫🇷", streak: 18 },
  { name: "David O.", xp: 36540, country: "🇳🇬", streak: 21 },
  { name: "Marie A.", xp: 34290, country: "🇧🇯", streak: 11 },
  { name: "Tom R.", xp: 31870, country: "🇺🇸", streak: 9 },
  { name: "Sofia M.", xp: 28430, country: "🇪🇸", streak: 14 },
  { name: "Yann B.", xp: 26120, country: "🇧🇯", streak: 7 },
];
</script>

<template>
  <Head title="Classement — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="text-center">
        <Trophy class="h-10 w-10 mx-auto text-electric animate-pulse-glow rounded-lg p-1" />
        <h1 class="font-display text-3xl md:text-5xl mt-3 text-white">Panthéon des <span class="text-electric neon-text">Légendes</span></h1>
        <p class="mt-2 text-muted-foreground">Affrontez des explorateurs du monde entier.</p>
      </div>

      <div class="mt-8 mx-auto flex glass rounded-xl p-1 mb-8 w-full sm:w-auto">
        <button
          v-for="t in ['hebdomadaire', 'global']"
          :key="t"
          @click="tab = t"
          :class="cn(
            'px-6 h-10 rounded-lg text-sm font-display font-bold uppercase tracking-widest transition flex-1 sm:flex-none',
            tab === t ? 'bg-gradient-electric text-electric-foreground shadow-neon' : 'text-muted-foreground hover:text-foreground'
          )"
        >
          {{ t }}
        </button>
      </div>

      <!-- PODIUM -->
      <div class="grid grid-cols-3 gap-3 md:gap-6 mb-8 items-end">
        <div v-for="pos in [1, 0, 2]" :key="players[pos].name" 
             :class="cn(
               'rounded-2xl glass-strong border-2 p-4 md:p-6 flex flex-col items-center text-center',
               pos === 0 ? 'text-warning border-warning shadow-[0_0_30px_oklch(0.82_0.17_80/0.5)] h-44 md:h-52' : 
               pos === 1 ? 'text-muted-foreground border-muted-foreground/60 h-36 md:h-44' : 
               'text-purple-neon border-purple-neon shadow-purple h-28 md:h-36'
             )">
          <Crown v-if="pos === 0" class="h-6 w-6 mb-2 animate-float" />
          <Medal v-else class="h-5 w-5 mb-2" />
          
          <div class="h-12 w-12 md:h-16 md:w-16 rounded-full bg-gradient-electric grid place-items-center font-display font-black text-electric-foreground text-xl">
            {{ players[pos].name.charAt(0) }}
          </div>
          <div class="font-display text-xs md:text-sm mt-2 truncate w-full text-white">{{ players[pos].name }}</div>
          <div class="text-[10px] text-muted-foreground">{{ players[pos].country }}</div>
          <div class="font-display text-sm md:text-base text-electric mt-1">{{ players[pos].xp.toLocaleString() }}</div>
        </div>
      </div>

      <div class="rounded-2xl glass-strong divide-y divide-electric/10 overflow-hidden">
        <div v-for="(p, i) in players.slice(3)" :key="p.name" class="flex items-center gap-4 p-4 hover:bg-electric/5 transition">
          <div class="w-8 font-display text-muted-foreground text-center">#{{ i + 4 }}</div>
          <div class="h-10 w-10 rounded-lg bg-gradient-electric grid place-items-center font-display font-black text-electric-foreground text-sm">
            {{ p.name.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-bold text-sm text-white">{{ p.name }} <span class="text-xs">{{ p.country }}</span></div>
            <div class="text-xs text-muted-foreground flex items-center gap-1">
              <Flame class="h-3 w-3 text-warning" />{{ p.streak }} jours de série
            </div>
          </div>
          <div class="font-display text-electric font-bold text-sm">{{ p.xp.toLocaleString() }} XP</div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
