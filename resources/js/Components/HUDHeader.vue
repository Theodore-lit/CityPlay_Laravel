<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { MapPin, Zap, Heart, Gamepad2, Bell, X, ArrowRight } from 'lucide-vue-next';
import HUDButton from '@/Components/HUDButton.vue';
import { cn } from '@/lib/utils';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const notifications = computed(() => page.props.auth.notifications || []);

const showNotifications = ref(false);

const markAsRead = (id) => {
  router.post(route('notifications.mark-as-read', id), {}, {
    preserveScroll: true,
  });
};

const navLinks = computed(() => [
  { name: 'DASHBOARD', to: 'dashboard', active: page.component === 'Player/Dashboard' },
  { name: 'DESTINATIONS', to: 'player.cities', active: page.component === 'Player/Cities' },
  { name: 'CLASSEMENT', to: 'player.leaderboard', active: page.component === 'Player/Leaderboard' },
  { name: 'RÉCOMPENSES', to: 'player.rewards', active: page.component === 'Player/Rewards' },
]);
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
            <span class="text-[7px] font-black text-primary/80 tracking-[0.4em] uppercase mt-0.5">SECTOR_V_04</span>
        </div>
      </Link>

      <nav class="hidden lg:flex items-center gap-2">
        <Link v-for="link in navLinks" :key="link.name" :href="route(link.to)" 
              :class="cn('hud-nav-item text-[10px] px-4 py-1.5 font-black', link.active && 'active')">
          {{ link.name }}
        </Link>
        <HUDButton :href="route('player.modes')" variant="primary" class="h-8 px-4 ml-4 scale-90">
            <div class="flex items-center gap-2">
                <Gamepad2 class="h-3 w-3" />
                <span>JOUER</span>
            </div>
        </HUDButton>
      </nav>
    </div>

    <div class="flex items-center gap-6">
      <!-- NOTIFICATIONS BELL -->
      <div class="relative">
        <button 
          @click="showNotifications = !showNotifications"
          class="glass h-10 w-10 rounded-xl border-white/10 flex items-center justify-center bg-white/5 hover:bg-white/10 transition-all relative group"
        >
          <Bell :class="cn('h-5 w-5 transition-colors', notifications.length > 0 ? 'text-primary animate-pulse' : 'text-white/40 group-hover:text-white')" />
          <div v-if="notifications.length > 0" class="absolute -top-1 -right-1 h-4 w-4 bg-primary rounded-full border-2 border-zinc-950 flex items-center justify-center">
            <span class="text-[8px] font-black text-black">{{ notifications.length }}</span>
          </div>
        </button>

        <!-- NOTIFICATIONS DROPDOWN -->
        <div v-if="showNotifications" class="absolute top-14 right-0 w-80 neon-border-box p-4 z-[100] animate-fade-in">
          <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/10">
            <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em]">FLUX_NOTIFICATIONS</span>
            <button @click="showNotifications = false"><X class="h-3 w-3 text-white/40 hover:text-white" /></button>
          </div>

          <div v-if="notifications.length === 0" class="py-8 text-center">
            <div class="text-[10px] font-black text-white/20 uppercase tracking-widest">AUCUN_SIGNAL_ACTIF</div>
          </div>

          <div v-else class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar pr-2">
            <div v-for="n in notifications" :key="n.id" class="hud-glass-card p-3 rounded-xl border-white/5 group/notif">
              <div class="flex flex-col gap-2">
                <p class="text-[10px] font-bold text-white/80 leading-relaxed uppercase tracking-wide">
                  {{ n.data.message }}
                </p>
                <div class="flex items-center justify-between mt-1">
                  <span class="text-[7px] text-white/30 font-black uppercase">{{ new Date(n.created_at).toLocaleTimeString() }}</span>
                  <div class="flex gap-2">
                    <button @click="markAsRead(n.id)" class="text-[7px] font-black text-white/20 hover:text-white uppercase tracking-widest">IGNORER</button>
                    <Link 
                      :href="n.data.action_url" 
                      @click="showNotifications = false; markAsRead(n.id)"
                      class="text-[7px] font-black text-primary hover:underline uppercase tracking-widest flex items-center gap-1"
                    >
                      REJOINDRE <ArrowRight class="h-2 w-2" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <div class="glass px-3 py-1.5 rounded-xl border-white/10 flex items-center gap-2 bg-white/5">
          <Zap class="h-3.5 w-3.5 text-primary drop-shadow-[0_0_8px_#06b6d4]" />
          <span class="text-xs font-black text-white italic tracking-tighter">{{ user?.xp || 0 }}</span>
        </div>
        <div class="glass px-3 py-1.5 rounded-xl border-white/10 flex items-center gap-2 bg-white/5">
          <Heart class="h-3.5 w-3.5 text-red-500 drop-shadow-[0_0_8px_#ef4444]" />
          <span class="text-xs font-black text-white italic tracking-tighter">{{ user?.hearts || 0 }}</span>
        </div>
      </div>

      <div class="flex items-center gap-4 border-l border-white/10 pl-6">
        <div class="text-right hidden sm:block">
          <div class="text-[10px] font-black uppercase tracking-widest leading-none text-white">{{ user?.name || 'CITIZEN' }}</div>
          <div class="text-[7px] text-primary font-black uppercase tracking-[0.3em] mt-1.5">AUTH_LEVEL_{{ user?.auth_level || '01' }}</div>
        </div>
        <div class="h-10 w-10 rounded-xl border-2 border-primary/40 p-0.5 shadow-[0_0_15px_rgba(6,182,212,0.2)] group cursor-pointer">
          <div class="h-full w-full rounded-lg bg-zinc-900 grid place-items-center font-black text-xs text-white italic border border-white/10 group-hover:border-primary/60 transition-all">
            {{ (user?.name || 'CP').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
          </div>
        </div>
      </div>
    </div>
  </header>
</template>