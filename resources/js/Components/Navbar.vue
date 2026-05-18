<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, Menu, X } from 'lucide-vue-next';
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
    const baseLinks = [
        { to: 'welcome', label: 'Accueil' },
    ];

    if (user.value?.role === 'super_admin') {
        return [
            ...baseLinks,
            { to: 'admin.dashboard', label: 'Admin HQ' },
            { to: 'mairie.dashboard', label: 'Console Maire' },
        ];
    }

    if (user.value?.role === 'mairie') {
        return [
            ...baseLinks,
            { to: 'mairie.dashboard', label: 'Console Maire' },
        ];
    }

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
  <header class="sticky top-0 z-50 bg-card/80 backdrop-blur-md border-b border-border">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
      <Link :href="route('dashboard')" class="flex items-center gap-2 group">
        <div class="relative h-9 w-9 rounded-lg bg-primary/10 border border-primary/20 grid place-items-center">
          <Compass class="h-5 w-5 text-primary neon-text" />
        </div>
        <div class="font-display font-black tracking-widest text-sm sm:text-base text-foreground">
          CITY<span class="text-primary neon-text">PLAY</span>
        </div>
      </Link>

      <nav class="hidden md:flex items-center gap-1">
        <template v-for="l in links" :key="l.to">
          <Link
            :href="route(l.to)"
            :class="cn(
              'px-4 py-2 rounded-lg text-sm font-medium tracking-wide transition-colors',
              isActive(l.to)
                ? 'text-primary bg-primary/10'
                : 'text-muted-foreground hover:text-foreground hover:bg-muted'
            )"
          >
            {{ l.label }}
          </Link>
        </template>
      </nav>

      <div class="hidden md:block">
        <div class="flex items-center gap-2">
          <template v-if="user">
            <div class="flex items-center gap-4 mr-4 bg-muted px-4 py-1.5 rounded-full border border-border">
              <div class="flex items-center text-accent neon-text-purple">
                  <span class="text-lg mr-1">🪙</span>
                  <span class="font-bold">{{ user.coins || 0 }}</span>
              </div>
              <div class="flex items-center text-destructive">
                  <span class="text-lg mr-1">❤️</span>
                  <span class="font-bold">{{ user.hearts || 0 }}</span>
              </div>
            </div>
            <button @click="showLogoutModal = true" class="text-muted-foreground hover:text-electric text-sm font-medium">
              Déconnexion
            </button>
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
