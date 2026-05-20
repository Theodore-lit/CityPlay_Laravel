<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head } from '@inertiajs/vue3';
import { Trophy, Crown, Medal, Flame } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const tab = ref('hebdomadaire');

const props = defineProps({
    topPlayers: Array
});

const players = computed(() => {
    if (!props.topPlayers || props.topPlayers.length === 0) {
        return [
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
        ];
    }
    return props.topPlayers.map(p => ({
        name: p.name,
        xp: p.xp || 0,
        country: "🇧🇯", // Default or from user field if exists
        streak: p.streak || 0
    }));
});
</script>

<template>
  <Head title="Classement — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="text-center">
        <Trophy class="h-10 w-10 mx-auto text-electric animate-pulse-glow rounded-lg p-1" />
        <h1 class="font-display text-3xl md:text-5xl mt-3 text-foreground">Panthéon des <span class="text-electric neon-text">Légendes</span></h1>
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

      <div class="mt-12 grid grid-cols-3 gap-3 md:gap-6 max-w-2xl mx-auto items-end mb-12">
        <template v-for="pos in [1, 0, 2]" :key="pos">
          <div 
            v-if="players[pos]" 
            :class="cn(
              'relative glass-strong rounded-[1.5rem] p-3 md:p-6 text-center hover-game group border-white/20',
              pos === 0 ? 'h-56 md:h-72 border-primary shadow-neon' : 'h-48 md:h-60'
            )"
          >
            <div v-if="pos === 0" class="absolute -top-6 left-1/2 -translate-x-1/2 h-10 w-10 bg-gradient-premium rounded-xl shadow-neon flex items-center justify-center border border-white/30 z-10 animate-float-game">
              <Trophy class="h-6 w-6 text-white" />
            </div>
            
            <div :class="cn(
                'mx-auto rounded-full bg-gradient-premium p-1 shadow-lg group-hover:scale-110 transition-transform',
                pos === 0 ? 'h-16 w-16 md:h-24 md:w-24' : 'h-12 w-12 md:h-20 md:w-20'
            )">
                <div class="w-full h-full rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center font-display font-black text-white text-xl md:text-3xl">
                    {{ players[pos].name.charAt(0) }}
                </div>
            </div>

            <div class="font-display text-[10px] md:text-sm mt-3 truncate w-full text-foreground font-black uppercase tracking-tight">{{ players[pos].name }}</div>
            <div class="text-[8px] md:text-[10px] text-muted-foreground uppercase tracking-widest font-bold">{{ players[pos].country }}</div>
            <div class="font-display text-xs md:text-xl text-primary mt-1 font-black">{{ players[pos].xp.toLocaleString() }} <span class="text-[10px] opacity-50">XP</span></div>
          </div>
        </template>
      </div>

      <div class="rounded-[2rem] glass-strong divide-y divide-white/5 overflow-hidden border-white/20">
        <div v-for="(p, i) in players.slice(3)" :key="p.name" class="flex items-center gap-4 p-4 hover:bg-primary/5 transition group">
          <div class="w-8 font-display text-muted-foreground text-center font-black text-xs">#{{ i + 4 }}</div>
          <div class="h-10 w-10 rounded-xl bg-gradient-premium p-0.5 shadow-sm group-hover:scale-110 transition-transform shrink-0">
              <div class="w-full h-full rounded-[0.5rem] bg-white/10 backdrop-blur-md flex items-center justify-center font-display font-black text-white text-xs">
                {{ p.name.charAt(0) }}
              </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-display font-black text-xs text-foreground uppercase tracking-tight">{{ p.name }} <span class="text-[10px] text-muted-foreground font-normal ml-2">{{ p.country }}</span></div>
            <div class="text-[9px] text-muted-foreground flex items-center gap-1 font-bold uppercase tracking-widest mt-0.5">
              <Flame class="h-3 w-3 text-warning" />{{ p.streak }}j série
            </div>
          </div>
          <div class="font-display text-primary font-black text-sm">{{ p.xp.toLocaleString() }} <span class="text-[10px] opacity-40">XP</span></div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
