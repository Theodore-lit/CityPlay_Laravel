<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, Map, Gamepad2, Trophy, Gift } from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const page = usePage();
const currentPath = computed(() => page.url);

const tabs = [
  { to: 'dashboard', icon: LayoutDashboard, label: 'Dashboard' },
  { to: 'player.cities', icon: Map, label: 'Villes' },
  { to: 'player.modes', icon: Gamepad2, label: 'Jouer' },
  { to: 'player.leaderboard', icon: Trophy, label: 'Classement' },
  { to: 'player.rewards', icon: Gift, label: 'Récompenses' },
];

const isActive = (routeName) => route().current(routeName);
</script>

<template>
  <nav class="md:hidden fixed bottom-6 inset-x-6 z-40 bg-zinc-950/80 backdrop-blur-2xl rounded-[2rem] px-2 py-2 flex justify-between shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden group">
    <!-- Neon Top Line -->
    <div class="absolute top-0 left-1/4 right-1/4 h-px bg-gradient-to-r from-transparent via-primary/50 to-transparent shadow-[0_0_15px_#06b6d4]" />
    
    <template v-for="t in tabs" :key="t.to">
      <Link
        :href="route(t.to)"
        :class="cn(
          'flex-1 flex flex-col items-center gap-1 py-2.5 rounded-2xl transition-all duration-500 relative overflow-hidden',
          isActive(t.to) ? 'text-primary' : 'text-white/40 hover:text-white/60'
        )"
      >
        <!-- Active Background Glow -->
        <div v-if="isActive(t.to)" class="absolute inset-0 bg-primary/10 animate-pulse" />
        <div v-if="isActive(t.to)" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-primary shadow-[0_0_10px_#06b6d4]" />

        <component 
          :is="t.icon" 
          :class="cn('h-5 w-5 transition-transform duration-500', isActive(t.to) ? 'scale-110 drop-shadow-[0_0_8px_#06b6d4]' : 'scale-100')" 
        />
        <span class="text-[9px] font-black uppercase tracking-widest">{{ t.label }}</span>
      </Link>
    </template>
  </nav>
</template>
