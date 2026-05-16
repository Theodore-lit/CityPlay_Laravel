<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, Menu, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import NeonButton from './NeonButton.vue';
import { cn } from '@/lib/utils';

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentPath = computed(() => page.url);

const open = ref(false);

const links = [
  { to: 'dashboard', label: 'Tableau de bord' },
  { to: 'player.cities', label: 'Villes' },
  { to: 'player.modes', label: 'Jouer' },
  { to: 'player.leaderboard', label: 'Classement' },
  { to: 'player.rewards', label: 'Récompenses' },
];

const isActive = (routeName) => route().current(routeName);
</script>

<template>
  <header class="sticky top-0 z-50 glass-strong border-b border-electric/20">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
      <Link :href="route('dashboard')" class="flex items-center gap-2 group">
        <div class="relative h-9 w-9 rounded-lg bg-gradient-electric grid place-items-center shadow-neon">
          <Compass class="h-5 w-5 text-electric-foreground" />
        </div>
        <div class="font-display font-black tracking-widest text-sm sm:text-base text-white">
          CITY<span class="text-electric neon-text">PLAY</span>
        </div>
      </Link>

      <nav class="hidden md:flex items-center gap-1">
        <template v-for="l in links" :key="l.to">
          <Link
            :href="route(l.to)"
            :class="cn(
              'px-4 py-2 rounded-lg text-sm font-medium tracking-wide transition-colors',
              isActive(l.to)
                ? 'text-electric bg-electric/10 neon-border'
                : 'text-muted-foreground hover:text-foreground hover:bg-electric/5'
            )"
          >
            {{ l.label }}
          </Link>
        </template>
      </nav>

      <div class="hidden md:block">
        <div class="flex items-center gap-2">
          <template v-if="user">
            <div class="flex items-center gap-4 mr-4 bg-gaming-dark/50 px-4 py-1.5 rounded-full border border-electric/20">
              <div class="flex items-center text-yellow-400">
                  <span class="text-lg mr-1">🪙</span>
                  <span class="font-bold">{{ user.coins || 0 }}</span>
              </div>
              <div class="flex items-center text-red-500">
                  <span class="text-lg mr-1">❤️</span>
                  <span class="font-bold">{{ user.hearts || 0 }}</span>
              </div>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="text-muted-foreground hover:text-electric text-sm font-medium">
              Déconnexion
            </Link>
          </template>
          <template v-else>
            <NeonButton :href="route('login')" variant="outline" size="sm">
              Connexion
            </NeonButton>
          </template>
        </div>
      </div>

      <div class="md:hidden flex items-center gap-1">
        <button
          class="p-2 text-electric"
          @click="open = !open"
          aria-label="Menu"
        >
          <X v-if="open" />
          <Menu v-else />
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="open" class="md:hidden glass-strong border-t border-electric/20 px-4 py-3 space-y-1 animate-fade-up">
      <template v-for="l in links" :key="l.to">
        <Link
          :href="route(l.to)"
          @click="open = false"
          :class="cn(
            'block px-4 py-2 rounded-lg text-sm',
            isActive(l.to)
              ? 'text-electric bg-electric/10'
              : 'text-muted-foreground'
          )"
        >
          {{ l.label }}
        </Link>
      </template>
      <template v-if="user">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          @click="open = false"
          class="block w-full text-left px-4 py-2 rounded-lg text-electric font-bold"
        >
          Déconnexion
        </Link>
      </template>
      <template v-else>
        <Link
          :href="route('login')"
          @click="open = false"
          class="block px-4 py-2 rounded-lg text-electric font-bold"
        >
          Connexion →
        </Link>
      </template>
    </div>
  </header>
</template>
