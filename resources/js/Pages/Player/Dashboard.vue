<script setup>

import { Head, Link, usePage } from '@inertiajs/vue3';

import {

  Trophy, Zap, MapPin, Award, Flame, ArrowRight, Crown, Sparkles, Heart,

  LayoutDashboard, Map, Gamepad2, Gift, Lock, Moon, Star, ShieldCheck

} from 'lucide-vue-next';

import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import { computed } from 'vue';

import { cn } from '@/lib/utils';

import MobileTabBar from '@/Components/MobileTabBar.vue';



const props = defineProps({
    user: Object,
    cities: Array,
});

const user = computed(() => props.user);

const page = usePage();



const xp = 2840;

const xpMax = 4000;

const pct = (xp / xpMax) * 100;

const segments = 25;

const activeSegments = Math.floor((pct / 100) * segments);



const stats = [

  { icon: Trophy, label: 'MISSIONS', value: '47', color: 'text-primary' },

  { icon: Zap, label: 'TOTAL XP', value: '12,840', color: 'text-cyan-400' },

  { icon: Map, label: 'CITIES', value: '4 / 5', color: 'text-purple-400' },

  { icon: Flame, label: 'STREAK', value: '32j', color: 'text-orange-500' },

];



const achievements = [

  { icon: Crown, name: 'ROYAL SAVANT', type: 'EPIC', color: 'gold', active: true },

  { icon: Moon, name: 'NIGHT HUNTER', type: 'EPIC', color: 'blue', active: true },

  { icon: ShieldCheck, name: 'GOHO GUARD', type: 'RARE', color: 'gray', active: false },

  { icon: Star, name: 'CITY STAR', type: 'RARE', color: 'gray', active: false },

  { icon: Flame, name: 'STREAK MASTER', type: 'EPIC', color: 'gray', active: false },

];



const navLinks = [

  { name: 'DASHBOARD', to: 'dashboard', active: true },

  { name: 'CITIES', to: 'player.cities', active: false },

  { name: 'PLAY', to: 'player.cities', active: false },

  { name: 'LEADERBOARD', to: 'player.leaderboard', active: false },

  { name: 'REWARDS', to: 'player.rewards', active: false },

];

</script>



<template>
  <Head title="Dashboard — CityPlay" />

  <SiteLayout isHUD>
    <HUDHeader />

    <main class="max-w-7xl mx-auto px-6 py-6 pb-24 relative z-10">
      <!-- HUD DECO LINES -->
      <div class="absolute top-0 left-6 right-6 flex justify-between pointer-events-none opacity-20">
        <span class="hud-data-line">TRK_ID: {{ user?.id || 'UNKNOWN' }} // SESSION: 0x{{ Math.random().toString(16).slice(2, 8).toUpperCase() }}</span>
        <span class="hud-data-line">GRID_COORD: 6.3702° N, 2.4407° E</span>
      </div>

      <!-- PROFILE HERO SECTION -->
      <div class="neon-border-box p-6 mb-8 overflow-hidden group">
        <!-- Mechanical Corners -->
        <div class="neon-corner top-0 left-0 border-r-0 border-b-0" />
        <div class="neon-corner top-0 right-0 border-l-0 border-b-0" />
        <div class="neon-corner bottom-0 left-0 border-r-0 border-t-0" />
        <div class="neon-corner bottom-0 right-0 border-l-0 border-t-0" />

        <div class="absolute inset-0 bg-gradient-to-r from-primary/10 via-transparent to-purple-500/10 opacity-50 pointer-events-none" />
        
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8 relative z-10">
          <div class="flex items-center gap-8 flex-1 w-full">
            <!-- TECH HEXAGON AVATAR -->
            <div class="relative shrink-0 tech-hexagon-container group/avatar">
              <!-- Double Border Effect -->
              <div class="absolute inset-0 border border-primary/40 clip-path-hex -rotate-6 scale-110 animate-pulse" />
              <div class="absolute inset-0 border border-purple-500/30 clip-path-hex rotate-12 scale-105" />
              
              <div class="tech-hexagon-outer" />
              
              <div class="h-24 w-24 tech-hexagon-inner">
                <!-- Mechanical Notches -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-4 h-0.5 bg-primary/60 shadow-[0_0_6px_#06b6d4]" />
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-4 h-0.5 bg-primary/60 shadow-[0_0_6px_#06b6d4]" />
                
                <span class="text-3xl font-display font-black text-white italic drop-shadow-[0_0_12px_rgba(6,182,212,0.9)]">
                  {{ user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                </span>
              </div>
              <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-primary text-black font-black text-[8px] tracking-widest clip-path-tech shadow-[0_0_15px_#06b6d4] z-20">
                LVL 24
              </div>
            </div>

            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1.5">
                <div class="h-0.5 w-10 bg-gradient-to-r from-primary to-purple-500 drop-shadow-[0_0_6px_#06b6d4]" />
                <div class="text-[9px] text-primary font-black tracking-[0.3em] uppercase animate-pulse">SYSTEM_ACTIVE // MYTHICAL_EXPLORER</div>
              </div>
              <h1 class="text-4xl lg:text-5xl font-display font-black uppercase italic tracking-tighter mb-6 text-white drop-shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                {{ user.name }}
              </h1>
              
              <div class="max-w-md">
                <div class="flex justify-between text-[8px] font-black tracking-[0.2em] mb-2 uppercase">
                  <span class="text-white/60 flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary animate-ping" /> DATA_STREAMING_01
                  </span>
                  <span class="text-primary drop-shadow-[0_0_10px_#06b6d4]">{{ xp }} / {{ xpMax }} XP</span>
                </div>
                <div class="segmented-progress">
                  <div v-for="i in segments" :key="i" 
                       :class="cn('progress-segment', i <= activeSegments ? 'active' : '')">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- AGGRESSIVE PLAY BUTTON -->
          <div class="w-full lg:w-72">
            <HUDButton :href="route('player.cities')" variant="aggressive">
              <div class="text-3xl font-display font-black tracking-[0.15em] italic flex items-center gap-3 group-hover/play:text-primary group-hover/play:scale-105 transition-all duration-500">
                PLAY <ArrowRight class="h-8 w-8 text-primary group-hover/play:translate-x-4 transition-transform" />
              </div>
              <div class="text-[8px] font-black tracking-[0.5em] text-white/40 uppercase group-hover/play:text-primary/80 transition-colors">INITIATE_TACTICAL_LINK</div>
              <!-- Glitch Effect Overlay -->
              <div class="absolute inset-0 bg-primary/10 mix-blend-overlay opacity-0 group-hover/play:opacity-100 transition-opacity" />
            </HUDButton>
          </div>
        </div>

        <!-- STATS BAR -->
        <div class="grid grid-cols-2 md:grid-cols-4 mt-8 border-t border-white/10 pt-6 gap-3">
          <div v-for="s in stats" :key="s.label" class="hud-stat-box group/stat p-3 border border-white/5 bg-white/[0.02] rounded-xl transition-all hover:bg-white/[0.05]">
            <div class="flex items-center gap-4">
              <div :class="cn('h-10 w-10 rounded-xl glass border-white/10 flex items-center justify-center shadow-xl group-hover/stat:scale-110 transition-all duration-300 relative overflow-hidden', s.color)">
                <div class="absolute inset-0 bg-current opacity-10" />
                <component :is="s.icon" class="h-5 w-5 relative z-10" />
              </div>
              <div>
                <div class="text-2xl font-display font-black leading-none group-hover/stat:text-primary transition-colors">{{ s.value }}</div>
                <div class="text-[8px] font-black tracking-[0.2em] text-white/30 mt-0.5 uppercase">{{ s.label }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-12 gap-8">
        <!-- MISSION OBJECTIVES -->
        <div class="lg:col-span-8 space-y-6">
          <div class="flex items-center gap-4">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-primary/30 to-transparent" />
            <div class="flex items-center gap-2.5 px-3 py-1.5 border border-primary/20 bg-primary/5 rounded-full">
              <MapPin class="h-3.5 w-3.5 text-primary animate-pulse" />
              <h2 class="text-[10px] font-black tracking-[0.4em] uppercase text-primary">SECTOR_OBJECTIVES</h2>
            </div>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-primary/30 to-transparent" />
          </div>
          
          <div class="grid sm:grid-cols-2 gap-6">
            <!-- DYNAMIC CITY CARDS -->
            <div v-for="(city, idx) in cities.slice(0, 3)" :key="city.id" 
                 :class="cn('hud-glass-card group cursor-pointer overflow-hidden rounded-[2rem] relative border-2', idx === 1 ? 'opacity-60 grayscale cursor-not-allowed border-white/10' : 'border-white/5 hover:border-primary/40')">
              
              <!-- Neon Corners for Cards -->
              <div class="absolute top-3 left-3 h-3 w-3 border-t-2 border-l-2 border-primary/40 group-hover:border-primary opacity-0 group-hover:opacity-100 transition-all" />
              
              <Link :href="idx === 1 ? '#' : route('player.game', city.id)" class="block">
                <div class="relative aspect-[16/9] overflow-hidden">
                  <img :src="city.image_path || 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800'" 
                       class="w-full h-full object-cover city-hud-img" />
                  
                  <!-- Color Tint Overlays -->
                  <div :class="cn('absolute inset-0 mix-blend-color opacity-30 transition-opacity', idx === 0 ? 'bg-primary' : (idx === 2 ? 'bg-orange-500' : 'bg-zinc-500'))" />
                  <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-90" />
                  
                  <!-- SCANNER EFFECT ON CARD -->
                  <div class="absolute inset-0 grid-bg opacity-20 group-hover:opacity-40 transition-opacity" />
                  
                  <!-- LOCK OVERLAY -->
                  <div v-if="idx === 1" class="absolute inset-0 flex flex-col items-center justify-center gap-3 z-10">
                     <div class="h-16 w-16 rounded-2xl glass border-white/10 grid place-items-center text-white/20">
                        <Lock class="h-8 w-8" />
                     </div>
                     <span class="text-[10px] font-black tracking-[0.4em] text-white/40 uppercase">DATA_ENCRYPTED</span>
                  </div>
                </div>
                <div class="p-6 relative">
                  <h4 :class="cn('text-2xl font-display font-black uppercase italic tracking-tighter mb-1.5 transition-all group-hover:translate-x-2', idx === 1 ? 'text-white/40' : 'text-white group-hover:text-primary')">
                    {{ city.name }}
                  </h4>
                  <div class="flex items-center justify-between">
                    <div class="text-[9px] font-black tracking-[0.15em] text-white/40 uppercase flex items-center gap-1.5">
                      STATUS: 
                      <span v-if="idx === 0" class="text-green-400 drop-shadow-[0_0_8px_#4ade80]">OPERATIONAL</span>
                      <span v-else-if="idx === 1" class="text-white/20">LOCKED_CORE</span>
                      <span v-else class="text-orange-400 drop-shadow-[0_0_8px_#fb923c]">SIGNAL_DETECTED</span>
                    </div>
                    <div class="h-8 w-8 rounded-full border border-primary/20 flex items-center justify-center group-hover:border-primary group-hover:bg-primary/10 transition-all">
                      <ArrowRight :class="cn('h-4 w-4 transition-all', idx === 1 ? 'hidden' : 'text-primary group-hover:translate-x-1')" />
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- SUCCESS / HALL OF FAME -->
        <div class="lg:col-span-4 space-y-6">
          <div class="flex items-center gap-4">
            <Award class="h-3.5 w-3.5 text-purple-500 animate-bounce" />
            <h2 class="text-[10px] font-black tracking-[0.4em] uppercase text-purple-500">HALL_OF_FAME</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-purple-500/30 to-transparent" />
          </div>

          <div class="neon-border-box magenta p-8 flex flex-col min-h-[550px] justify-between relative">
            <!-- Mechanical Corners -->
            <div class="neon-corner magenta top-0 left-0 border-r-0 border-b-0 scale-75" />
            <div class="neon-corner magenta top-0 right-0 border-l-0 border-b-0 scale-75" />
            <div class="neon-corner magenta bottom-0 left-0 border-r-0 border-t-0 scale-75" />
            <div class="neon-corner magenta bottom-0 right-0 border-l-0 border-t-0 scale-75" />

            <!-- HONEYCOMB BADGES (Symmetric Grid) -->
            <div class="relative h-72 flex items-center justify-center scale-[1.1]">
              <!-- CENTER (Active) -->
              <div class="badge-hex epic shadow-[0_0_35px_rgba(217,70,239,0.5)] z-10 border-magenta-500/60">
                <div class="flex flex-col items-center gap-1">
                  <Moon class="h-8 w-8 text-magenta-500 drop-shadow-[0_0_12px_#d946ef]" />
                  <span class="text-[6px] font-black text-magenta-500 tracking-[0.1em]">NIGHT_HUNTER</span>
                </div>
              </div>
              
              <!-- NESTED GRID (Symmetric Offsets) -->
              <div class="absolute -translate-x-[54px] -translate-y-[41px]">
                <div class="badge-hex border-amber-500/40 bg-amber-500/5 shadow-[0_0_15px_rgba(245,158,11,0.1)]">
                   <Crown class="h-6 w-6 text-amber-500/50" />
                </div>
              </div>

              <div class="absolute translate-x-[54px] -translate-y-[41px]">
                <div class="badge-hex border-primary/20">
                   <Zap class="h-6 w-6 text-primary/20" />
                </div>
              </div>

              <div class="absolute -translate-x-[54px] translate-y-[41px]">
                <div class="badge-hex border-white/5">
                   <ShieldCheck class="h-6 w-6 text-white/5" />
                </div>
              </div>

              <div class="absolute translate-x-[54px] translate-y-[41px]">
                <div class="badge-hex border-white/5">
                   <Trophy class="h-6 w-6 text-white/5" />
                </div>
              </div>

              <div class="absolute translate-x-0 -translate-y-[82px]">
                <div class="badge-hex border-white/5">
                   <Star class="h-6 w-6 text-white/5" />
                </div>
              </div>

              <div class="absolute translate-x-0 translate-y-[82px]">
                <div class="badge-hex border-white/5">
                   <Sparkles class="h-6 w-6 text-white/5" />
                </div>
              </div>
            </div>

            <!-- FLAMME ANIMÉE -->
            <div class="text-center pb-6 relative z-10">
              <div class="relative inline-block">
                <Flame class="h-20 w-20 text-orange-500 real-flame" />
                <div class="absolute inset-0 bg-orange-600/30 blur-[60px] rounded-full scale-[2.5] animate-pulse" />
              </div>
              <div class="mt-8">
                <div class="text-6xl font-display font-black italic tracking-tighter leading-none text-white drop-shadow-[0_0_25px_rgba(249,115,22,0.6)]">32j</div>
                <div class="text-[10px] font-black tracking-[0.6em] text-orange-500/60 mt-3 uppercase">STREAK_RECORD_DATA</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>



    <!-- MOBILE BOTTOM NAVIGATION (HUD STYLE) -->

    <nav class="md:hidden fixed bottom-0 inset-x-0 z-[60] pb-6 px-4">

      <div class="hud-border h-20 bg-zinc-950/90 backdrop-blur-xl flex items-center justify-between px-6 border-primary/30">

        <div v-for="link in navLinks" :key="link.name"

             :class="cn('flex flex-col items-center gap-1 transition-all', link.active ? 'text-primary' : 'text-white/40')">

          <component :is="link.name === 'DASHBOARD' ? LayoutDashboard : (link.name === 'CITIES' ? Map : (link.name === 'PLAY' ? Gamepad2 : (link.name === 'LEADERBOARD' ? Trophy : Gift)))"

                     :class="cn('h-5 w-5', link.active && 'drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]')" />

          <span class="text-[8px] font-black tracking-widest">{{ link.name }}</span>

        </div>

      </div>

    </nav>



    <!-- FLOATING DECORATIONS -->

    <div class="fixed top-1/4 -left-20 h-96 w-96 rounded-full bg-primary/5 blur-[120px] pointer-events-none" />

    <div class="fixed bottom-1/4 -right-20 h-96 w-96 rounded-full bg-purple-500/5 blur-[120px] pointer-events-none" />

    <div class="fixed bottom-10 right-10 opacity-20 pointer-events-none">

       <div class="h-20 w-20 border border-white/10 rounded-full animate-spin [animation-duration:10s]" />

       <div class="absolute inset-0 flex items-center justify-center">

         <Sparkles class="h-6 w-6 text-white/20" />

       </div>

    </div>
</SiteLayout>


</template>



<style scoped>

.hud-container {

  background-color: #020617;

}



@keyframes shimmer {

  0% { transform: translateX(-100%); }

  100% { transform: translateX(100%); }

}



.animate-shimmer {

  animation: shimmer 2s infinite;

}



/* Custom Scrollbar for HUD */

::-webkit-scrollbar {

  width: 4px;

}



::-webkit-scrollbar-track {

  background: #020617;

}



::-webkit-scrollbar-thumb {

  background: rgba(6, 182, 212, 0.2);

  border-radius: 10px;

}



::-webkit-scrollbar-thumb:hover {

  background: rgba(6, 182, 212, 0.4);

}

</style>