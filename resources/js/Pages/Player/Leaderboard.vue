<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head } from '@inertiajs/vue3';
import { Trophy, Crown, Medal, Flame } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const tab = ref('journalier');

const props = defineProps({
    topPlayersGlobal: Array,
    topPlayersDaily: Array
});

// Configuration des rangs du podium
const podiumConfig = {
    0: { label: '1ST', color: 'from-yellow-400 to-amber-600', text: 'text-yellow-500', shadow: 'shadow-yellow-500/20', icon: Crown },
    1: { label: '2ND', color: 'from-slate-300 to-slate-500', text: 'text-slate-400', shadow: 'shadow-slate-400/20', icon: Medal },
    2: { label: '3RD', color: 'from-orange-400 to-orange-700', text: 'text-orange-500', shadow: 'shadow-orange-600/20', icon: Medal }
};

const players = computed(() => {
    const topPlayers = tab.value === 'journalier' ? props.topPlayersDaily : props.topPlayersGlobal;

    if (!topPlayers || topPlayers.length === 0) {
        return [
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
            { name: "En attente...", xp: 0, country: "🇧🇯", streak: 0 },
        ];
    }
    
    return topPlayers.map(p => ({
        name: p.name,
        xp: p.xp || 0,
        country: "🇧🇯",
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
        <h1 class="font-display text-3xl md:text-5xl mt-3 text-foreground uppercase italic font-black">
            Panthéon des <span class="text-electric neon-text">Légendes</span>
        </h1>
        <p class="mt-2 text-muted-foreground font-medium tracking-tight">Affrontez les explorateurs du District.</p>
      </div>

      <div class="mt-8 mx-auto flex glass rounded-xl p-1 mb-12 w-full sm:w-auto max-w-xs">
        <button
          v-for="t in ['journalier', 'global']"
          :key="t"
          @click="tab = t"
          :class="cn(
            'px-6 h-10 rounded-lg text-[10px] font-display font-black uppercase tracking-[0.2em] transition-all flex-1',
            tab === t ? 'bg-primary text-primary-foreground shadow-neon' : 'text-muted-foreground hover:text-foreground'
          )"
        >
          {{ t }}
        </button>
      </div>

      <div class="mt-16 grid grid-cols-3 gap-3 md:gap-8 max-w-3xl mx-auto items-end mb-16">
        <template v-for="pos in [1, 0, 2]" :key="pos">
          <div
            v-if="players[pos]"
            :class="cn(
              'relative bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-[2rem] p-4 md:p-8 text-center hover-game group border-white/20 transition-all duration-700 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]',
              pos === 0 ? 'h-64 md:h-80 border-yellow-500/40 shadow-[0_0_40px_rgba(234,179,8,0.15)]' : 'h-52 md:h-64',
              pos === 1 ? 'border-slate-400/20' : '',
              pos === 2 ? 'border-orange-600/20' : ''
            )"
          >
            <div :class="cn(
                'absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full font-display font-black text-[9px] tracking-[0.2em] border border-white/20 shadow-xl z-20 text-white bg-gradient-to-r',
                podiumConfig[pos].color
            )">
                {{ podiumConfig[pos].label }}
            </div>

            <div v-if="pos === 0" class="absolute -top-12 left-1/2 -translate-x-1/2 h-12 w-12 bg-gradient-to-b from-yellow-300 to-yellow-600 rounded-2xl shadow-neon flex items-center justify-center border border-white/30 z-10 animate-float-game">
              <Crown class="h-7 w-7 text-white" />
            </div>

            <div :class="cn(
                'mx-auto rounded-full p-1 transition-transform duration-500 group-hover:scale-110 shadow-lg',
                pos === 0 ? 'h-20 w-20 md:h-32 md:w-32' : 'h-16 w-16 md:h-24 md:w-24',
                'bg-gradient-to-br',
                podiumConfig[pos].color
            )">
                <div class="w-full h-full rounded-full bg-[#0a0a0c] flex items-center justify-center font-display font-black text-white text-xl md:text-4xl italic">
                    {{ players[pos].name.charAt(0) }}
                </div>
            </div>

            <div class="mt-6">
                <div class="font-display text-[10px] md:text-sm truncate w-full text-foreground font-black uppercase tracking-widest">
                    {{ players[pos].name }}
                </div>
                <div :class="cn('text-[9px] md:text-[11px] font-bold mt-1 opacity-60', podiumConfig[pos].text)">
                    {{ players[pos].country }}
                </div>
                <div :class="cn(
                    'font-display text-sm md:text-2xl mt-2 font-black italic tracking-tighter',
                    pos === 0 ? 'text-yellow-400 neon-text' : 'text-primary'
                )">
                    {{ players[pos].xp.toLocaleString() }}
                    <span class="text-[10px] opacity-50 not-italic uppercase ml-1">XP</span>
                </div>
            </div>
          </div>
        </template>
      </div>

      <div class="rounded-[2.5rem] bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl divide-y divide-white/10 overflow-hidden border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
        <div v-for="(p, i) in players.slice(3)" :key="p.name"
             class="flex items-center gap-6 p-5 hover:bg-primary/5 transition-all group relative">

          <div class="w-10 font-display text-muted-foreground text-center font-black text-xs italic">
            #{{ i + 4 }}
          </div>

          <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-white/10 to-white/5 p-0.5 shadow-inner group-hover:scale-110 transition-transform shrink-0 border border-white/10">
              <div class="w-full h-full rounded-2xl bg-gaming-dark flex items-center justify-center font-display font-black text-white/40 text-sm">
                {{ p.name.charAt(0) }}
              </div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="font-display font-black text-sm text-foreground uppercase tracking-wider flex items-center gap-3">
                {{ p.name }}
                <span class="text-[10px] text-muted-foreground font-bold opacity-40">{{ p.country }}</span>
            </div>
            <div class="text-[9px] text-muted-foreground flex items-center gap-1.5 font-black uppercase tracking-[0.1em] mt-1">
              <Flame class="h-3 w-3 text-orange-500 shadow-neon-sm" />
              <span class="text-orange-500/80">{{ p.streak }} DAY STREAK</span>
            </div>
          </div>

          <div class="text-right">
              <div class="font-display text-primary font-black text-lg italic leading-none">
                {{ p.xp.toLocaleString() }}
              </div>
              <div class="text-[8px] font-black text-muted-foreground uppercase tracking-widest mt-1">Total XP</div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>

<style scoped>
.animate-float-game {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translate(-50%, 0px); }
    50% { transform: translate(-50%, -10px); }
}

.neon-text {
    text-shadow: 0 0 10px currentColor;
}
</style>
