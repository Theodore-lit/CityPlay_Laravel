<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { MapPin, Zap, Heart } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const page = usePage();
const user = page.props.auth.user;

const navLinks = [
  { name: 'DASHBOARD', to: 'dashboard', active: page.component === 'Player/Dashboard' },
  { name: 'DESTINATIONS', to: 'player.cities', active: page.component === 'Player/Cities' },
  { name: 'CLASSEMENT', to: 'player.leaderboard', active: page.component === 'Player/Leaderboard' },
  { name: 'RÉCOMPENSES', to: 'player.rewards', active: page.component === 'Player/Rewards' },
];
</script>

<template>
<header class="border-b border-white/10 px-8 py-4 flex items-center justify-between backdrop-blur-xl sticky top-0 z-50 bg-zinc-950/40">
    <div class="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent" />
    
    <div class="flex items-center gap-10">
      <Link :href="route('dashboard')" class="flex items-center gap-3 group">
        <div class="h-9 w-9 rounded-xl bg-primary border-2 border-white/20 grid place-items-center shadow-[0_0_20px_rgba(6,182,212,0.4)] group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
          <MapPin class="h-5 w-5 text-black" />
        </div>
        <div class="flex flex-col">
            <span class="font-display font-black tracking-tighter text-xl italic text-white uppercase leading-none">CITY<span class="text-primary">PLAY</span></span>
            <span class="text-[7px] font-black text-primary/60 tracking-[0.4em] uppercase mt-0.5">SECTOR_V_04</span>
        </div>
      </Link>

      <nav class="hidden lg:flex items-center gap-2">
        <Link v-for="link in navLinks" :key="link.name" :href="route(link.to)" 
              :class="cn('hud-nav-item text-[10px] px-4 py-1.5 font-black', link.active && 'active')">
          {{ link.name }}
        </Link>
      </nav>
    </div>

    <div class="flex items-center gap-6">
      <div class="flex items-center gap-4">
        <div class="glass px-3 py-1.5 rounded-xl border-white/10 flex items-center gap-2 bg-white/5">
          <Zap class="h-3.5 w-3.5 text-primary drop-shadow-[0_0_8px_#06b6d4]" />
          <span class="text-xs font-black text-white italic tracking-tighter">{{ user.xp || 0 }}</span>
        </div>
        <div class="glass px-3 py-1.5 rounded-xl border-white/10 flex items-center gap-2 bg-white/5">
          <Heart class="h-3.5 w-3.5 text-red-500 drop-shadow-[0_0_8px_#ef4444]" />
          <span class="text-xs font-black text-white italic tracking-tighter">{{ user.hearts || 0 }}</span>
        </div>
      </div>

      <div class="flex items-center gap-4 border-l border-white/10 pl-6">
        <div class="text-right hidden sm:block">
          <div class="text-[10px] font-black uppercase tracking-widest leading-none text-white">{{ user.name }}</div>
          <div class="text-[7px] text-primary font-black uppercase tracking-[0.3em] mt-1.5">AUTH_LEVEL_01</div>
        </div>
        <div class="h-10 w-10 rounded-xl border-2 border-primary/40 p-0.5 shadow-[0_0_15px_rgba(6,182,212,0.2)] group cursor-pointer">
          <div class="h-full w-full rounded-lg bg-zinc-900 grid place-items-center font-black text-xs text-white italic border border-white/10 group-hover:border-primary/60 transition-all">
            {{ user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
          </div>
        </div>
      </div>
    </div>
  </header>
</template>