<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head } from '@inertiajs/vue3';
import { Trophy, Medal, Crown, MapPin, Zap, TrendingUp, Search, Flame } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { ref, computed } from 'vue';

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

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-5xl px-6 py-10 pb-28 md:pb-12 relative z-10">
      <div class="flex flex-col items-center text-center mb-12">
        <div class="flex items-center gap-2 mb-4">
            <div class="h-px w-12 bg-primary/30" />
            <Trophy class="h-6 w-6 text-primary drop-shadow-[0_0_10px_#06b6d4]" />
            <div class="h-px w-12 bg-primary/30" />
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white mb-2">
            PANTHEON DES <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">LEGENDES</span>
        </h1>
        <div class="text-[10px] font-black tracking-[0.5em] text-white/40 uppercase">DATA_RECOGNITION_MODULE // GLOBAL_RANKING</div>
      </div>

      <div class="flex justify-center mb-12">
        <div class="inline-flex glass p-1.5 rounded-2xl border-white/10 relative">
            <div class="absolute inset-0 bg-primary/5 rounded-2xl blur-xl" />
            <button
              v-for="t in ['hebdomadaire', 'global']"
              :key="t"
              @click="tab = t"
              :class="cn(
                'relative z-10 px-8 h-10 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300',
                tab === t ? 'bg-primary text-black shadow-[0_0_20px_#06b6d4]' : 'text-white/40 hover:text-white'
              )"
            >
              {{ t.toUpperCase() }}
            </button>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-3xl mx-auto items-end mb-16 px-4">
        <template v-for="pos in [1, 0, 2]" :key="pos">
          <div 
            v-if="players[pos]" 
            :class="cn(
              'relative hud-glass-card rounded-[2rem] p-4 md:p-8 text-center border-2 group transition-all duration-700',
              pos === 0 ? 'h-64 md:h-80 border-primary/40 shadow-[0_0_40px_rgba(6,182,212,0.15)] z-20 scale-110' : 'h-52 md:h-64 border-white/5 opacity-80 hover:opacity-100 hover:border-primary/20 z-10'
            )"
          >
            <!-- Position Indicator -->
            <div :class="cn(
                'absolute -top-4 left-1/2 -translate-x-1/2 h-8 w-16 clip-path-tech flex items-center justify-center font-black text-[10px] z-30',
                pos === 0 ? 'bg-primary text-black shadow-[0_0_15px_#06b6d4]' : 'bg-zinc-800 text-white/60 border border-white/10'
            )">
                #{{ pos + 1 }}
            </div>

            <div :class="cn(
                'relative mx-auto rounded-full p-1.5 transition-all duration-500 group-hover:scale-105',
                pos === 0 ? 'h-20 w-20 md:h-28 md:w-28 bg-primary/20 shadow-[0_0_30px_rgba(6,182,212,0.3)]' : 'h-16 w-16 md:h-24 md:w-24 bg-white/5'
            )">
                <div class="absolute inset-0 rounded-full border border-primary/40 animate-spin-slow opacity-50" />
                <div class="w-full h-full rounded-full bg-zinc-900 border-2 border-white/10 flex items-center justify-center font-display font-black text-white text-2xl md:text-4xl italic drop-shadow-[0_0_10px_rgba(255,255,255,0.3)]">
                    {{ players[pos].name.charAt(0) }}
                </div>
            </div>

            <div class="mt-6">
                <div class="font-display text-xs md:text-base text-white font-black uppercase italic tracking-tight truncate">{{ players[pos].name }}</div>
                <div class="flex items-center justify-center gap-2 mt-1">
                    <span class="text-[8px] text-primary/60 font-black tracking-widest">{{ players[pos].country }}</span>
                    <div class="h-1 w-1 rounded-full bg-white/20" />
                    <span class="text-[8px] text-white/60 font-black tracking-widest flex items-center gap-1">
                        <Flame class="h-2 w-2 text-orange-500" /> {{ players[pos].streak }}j
                    </span>
                </div>
                <div class="mt-4 font-display text-lg md:text-2xl text-primary font-black italic tracking-tighter drop-shadow-[0_0_8px_rgba(6,182,212,0.5)]">
                    {{ players[pos].xp.toLocaleString() }}
                    <span class="text-[10px] opacity-60 italic tracking-widest ml-1">XP</span>
                </div>
            </div>
          </div>
        </template>
      </div>

      <!-- RANKING LIST -->
      <div class="neon-border-box p-4 md:p-8 rounded-[2.5rem] overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
        
        <div class="space-y-3">
          <div v-for="(p, i) in players.slice(3)" :key="p.name" 
               class="flex items-center gap-6 p-4 rounded-2xl hover:bg-white/[0.03] transition-all group border border-transparent hover:border-white/5">
            
            <div class="w-8 font-display text-white/40 text-center font-black text-xs group-hover:text-primary transition-colors">
                {{ (i + 4).toString().padStart(2, '0') }}
            </div>
            
            <div class="h-12 w-12 rounded-xl bg-white/5 border border-white/10 p-1 group-hover:border-primary/40 transition-all shrink-0">
                <div class="w-full h-full rounded-lg bg-zinc-900 flex items-center justify-center font-display font-black text-white text-sm italic">
                  {{ p.name.charAt(0) }}
                </div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="font-display font-black text-sm text-white uppercase italic tracking-tight group-hover:text-primary transition-colors">
                {{ p.name }}
              </div>
              <div class="flex items-center gap-3 mt-1">
                <span class="text-[9px] text-white/50 font-black tracking-widest uppercase">{{ p.country }}</span>
                <div class="h-0.5 w-4 bg-white/10" />
                <div class="text-[9px] text-white/60 flex items-center gap-1 font-black uppercase tracking-widest">
                  <Flame class="h-3 w-3 text-orange-500/60" /> {{ p.streak }} JOURS
                </div>
              </div>
            </div>

            <div class="text-right">
                <div class="font-display text-primary font-black text-lg italic tracking-tighter">{{ p.xp.toLocaleString() }}</div>
                <div class="text-[8px] text-white/40 font-black tracking-widest uppercase mt-0.5">PTS_EXPERIENCE</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
