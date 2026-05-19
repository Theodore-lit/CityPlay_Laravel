<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, Menu, X, User, LogOut } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import NeonButton from './NeonButton.vue';
import Modal from './Modal.vue';
import Checkbox from './Checkbox.vue';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentPath = computed(() => page.url);

const open = ref(false);
const showLogoutModal = ref(false);
const deactivateOnLogout = ref(user.value?.deactivate_on_logout || false);

const confirmLogout = () => {
    router.post(route('logout'), {
        deactivate_on_logout: deactivateOnLogout.value
    });
};

const links = computed(() => {
    if (user.value?.role === 'super_admin') {
        return [
            { to: 'admin.dashboard', label: '' },
        ];
    }

    if (user.value?.role === 'mairie') {
        return [
            { to: 'mairie.dashboard', label: '' },
        ];
    }

    const baseLinks = [
        { to: 'welcome', label: 'Accueil' },
    ];

    return [
        { to: 'dashboard', label: 'Tableau de bord' },
        { to: 'player.cities', label: 'Villes' },
        { to: 'player.modes', label: 'Jouer' },
        { to: 'player.leaderboard', label: 'Classement' },
        { to: 'player.rewards', label: 'Récompenses' },
    ];
});

const isActive = (routeName) => route().current(routeName);
</script>

<template>
  <header class="sticky top-0 z-50 glass-strong border-b border-white/20">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
      <Link :href="route('dashboard')" class="flex items-center gap-3 group">
        <div class="relative h-10 w-10 rounded-xl bg-gradient-premium grid place-items-center shadow-neon group-hover:scale-110 transition-transform">
          <Compass class="h-6 w-6 text-white" />
        </div>
        <div class="font-display font-black tracking-[0.2em] text-sm sm:text-lg text-foreground">
          CITY<span class="text-primary neon-text">PLAY</span>
        </div>
      </Link>

      <nav class="hidden md:flex items-center gap-2">
        <template v-for="l in links" :key="l.to">
          <Link
            :href="route(l.to)"
            :class="cn(
              'px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all',
              isActive(l.to)
                ? 'text-primary bg-primary/10 shadow-sm'
                : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'
            )"
          >
            {{ l.label }}
          </Link>
        </template>
      </nav>

      <div class="hidden md:block">
        <div class="flex items-center gap-3">
          <template v-if="user">
            <div v-if="user.role !== 'super_admin' && user.role !== 'mairie'" class="flex items-center gap-4 mr-4 bg-muted px-4 py-1.5 rounded-full border border-border">
              <div class="flex items-center text-accent neon-text-purple">
                  <span class="text-lg mr-1">⚡</span>
                  <span class="font-bold">{{ user.xp || 0 }}</span>
              </div>
              <button 
                @click="router.post(route('player.buy.heart'))"
                class="flex items-center text-destructive hover:scale-110 transition-transform group relative"
                title="Acheter un cœur (500 XP)"
              >
                  <span class="text-lg mr-1">❤️</span>
                  <span class="font-bold">{{ user.hearts || 0 }}</span>
                  <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gaming-dark text-[10px] text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-white/10 pointer-events-none">
                    +1 ❤️ (500 XP)
                  </span>
              </button>
            </div>
            <div class="flex items-center gap-3">
              <Link :href="route('profile.edit')" class="flex items-center gap-2 group p-1 pr-3 rounded-xl bg-white/5 border border-white/10 hover:border-primary/50 transition-all">
                <div class="h-8 w-8 rounded-lg bg-primary/20 grid place-items-center border border-primary/30 group-hover:bg-primary/30">
                  <User class="h-4 w-4 text-primary" />
                </div>
                <div class="flex flex-col">
                  <span class="text-[10px] font-black text-white uppercase leading-none">{{ user.name }}</span>
                  <span class="text-[8px] text-muted-foreground uppercase tracking-tighter">{{ user.role }}</span>
                </div>
              </Link>

              <button 
                @click="showLogoutModal = true"
                class="h-10 w-10 rounded-xl bg-destructive/10 border border-destructive/20 grid place-items-center text-destructive hover:bg-destructive hover:text-white transition-all shadow-sm"
                title="Déconnexion"
              >
                <LogOut class="h-5 w-5" />
              </button>
            </div>
          </template>
          <template v-else>
            <NeonButton :href="route('login')" variant="ghost" size="sm">Connexion</NeonButton>
            <NeonButton :href="route('register')" size="sm">Rejoindre</NeonButton>
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

    <!-- MODAL DE DÉCONNEXION -->
    <Modal :show="showLogoutModal" @close="showLogoutModal = false">
        <div class="p-6 bg-gaming-darker border border-electric/20 rounded-3xl overflow-hidden relative">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="relative z-10">
                <h2 class="font-display text-2xl text-white mb-4">Fin de Session Tactique</h2>
                <p class="text-muted-foreground text-sm mb-6 leading-relaxed">
                    Voulez-vous archiver votre compte ?
                    Celui-ci sera désactivé et vous devrez contacter un administrateur pour le réactiver.
                </p>

                <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/5 border border-white/10 mb-8">
                    <Checkbox v-model:checked="deactivateOnLogout" id="modal_deactivate" />
                    <label for="modal_deactivate" class="text-sm text-white font-medium cursor-pointer">
                        Confirmer la désactivation directe du compte
                    </label>
                </div>

                <div class="flex gap-4">
                    <NeonButton variant="outline" class="flex-1" @click="showLogoutModal = false">
                        Annuler
                    </NeonButton>
                    <NeonButton class="flex-1" @click="confirmLogout">
                        Se Déconnecter
                    </NeonButton>
                </div>
            </div>
        </div>
    </Modal>

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
        <button
          @click="showLogoutModal = true; open = false"
          class="block w-full text-left px-4 py-2 rounded-lg text-electric font-bold"
        >
          Déconnexion
        </button>
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
