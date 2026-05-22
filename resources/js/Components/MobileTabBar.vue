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
  { to: 'player.rewards.index', icon: Gift, label: 'Récompenses' },
];

const isActive = (routeName) => route().current(routeName);
</script>

<template>
  <nav class="md:hidden fixed bottom-3 inset-x-3 z-40 glass-strong rounded-2xl px-2 py-2 flex justify-between shadow-elevated">
    <template v-for="t in tabs" :key="t.to">
      <Link
        :href="route(t.to)"
        :class="cn(
          'flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition',
          isActive(t.to) ? 'bg-electric/15 text-electric' : 'text-muted-foreground'
        )"
      >
        <component 
          :is="t.icon" 
          :class="cn('h-5 w-5', isActive(t.to) && 'drop-shadow-[0_0_8px_oklch(0.78_0.18_232/0.8)]')" 
        />
        <span class="text-[10px] font-medium">{{ t.label }}</span>
      </Link>
    </template>
  </nav>
</template>
