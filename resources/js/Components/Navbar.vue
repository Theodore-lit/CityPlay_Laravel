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
            <div class="flex items-center gap-5 mr-4 glass px-5 py-2 rounded-2xl border-white/40">
              <div class="flex items-center text-accent group cursor-help">
                  <span class="text-lg mr-2 group-hover:scale-125 transition-transform">🪙</span>
                  <span class="font-display font-black">{{ user.coins || 0 }}</span>
              </div>
              <div class="flex items-center text-red-500 group cursor-help">
                  <span class="text-lg mr-2 group-hover:scale-125 transition-transform">❤️</span>
                  <span class="font-display font-black">{{ user.hearts || 0 }}</span>
              </div>
            </div>
            <Link :href="route('profile.edit')" class="h-10 w-10 rounded-xl bg-muted border border-border grid place-items-center hover:border-primary transition-colors">
              <User class="h-5 w-5 text-muted-foreground" />
            </Link>
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
