<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  Trophy, Zap, MapPin, Target, Award, Flame, ArrowRight, Crown, Sparkles, Heart,
} from 'lucide-vue-next';
import { computed } from 'vue';

// Import images if needed, or use city.image_path
import ouidahImg from '../../../images/city-ouidah.jpg';
import abomeyImg from '../../../images/city-abomey.jpg';

const props = defineProps({
    cities: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const xp = 2840;
const xpMax = 4000;
const pct = (xp / xpMax) * 100;

const stats = [
  { icon: Trophy, label: 'Missions', value: '47', color: 'text-electric' },
  { icon: Zap, label: 'Total XP', value: '12,840', color: 'text-cyan-neon' },
  { icon: MapPin, label: 'Villes', value: '4 / 5', color: 'text-purple-neon' },
  { icon: Flame, label: 'Série', value: '32j', color: 'text-warning' },
];

const achievements = [
  { icon: Crown, name: 'Savant Royal', desc: 'Complété tous les quiz d\'Abomey', rare: true },
  { icon: Sparkles, name: 'Chasseur Nocturne', desc: 'Résolu une énigme après minuit' },
  { icon: Flame, name: 'Série de 30 Jours', desc: 'Joué 30 jours consécutifs' },
];
</script>

<template>
  <Head title="Tableau de bord — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12">
      <!-- PROFILE HERO -->
      <div class="relative overflow-hidden rounded-3xl glass-strong p-6 md:p-10">
        <div class="absolute inset-0 grid-bg opacity-20" />
        <div class="absolute -top-20 -right-10 h-72 w-72 rounded-full bg-electric/30 blur-3xl" />
        <div class="relative flex flex-col md:flex-row md:items-center gap-6">
          <div class="relative">
            <div class="h-24 w-24 md:h-28 md:w-28 rounded-2xl bg-gradient-electric grid place-items-center font-display text-4xl font-black text-electric-foreground shadow-neon animate-pulse-glow">
              {{ user.name.substring(0, 2).toUpperCase() }}
            </div>
            <div class="absolute -bottom-2 -right-2 px-2 py-1 rounded-lg bg-purple-neon text-xs font-display font-black shadow-purple">
              LVL 24
            </div>
          </div>
          <div class="flex-1">
            <div class="text-xs text-electric uppercase tracking-widest font-bold">Explorateur Mythique</div>
            <h1 class="font-display text-3xl md:text-4xl mt-1">{{ user.name }}</h1>
            <p class="text-muted-foreground text-sm mt-1">Bénin • Membre depuis 2024 • 32 jours de série 🔥</p>
            <div class="mt-5">
              <div class="flex justify-between text-xs mb-2">
                <span class="text-muted-foreground">Progression XP</span>
                <span class="text-electric font-bold">{{ xp }} / {{ xpMax }} XP</span>
              </div>
              <div class="h-3 rounded-full bg-gaming-darker overflow-hidden border border-electric/30">
                <div
                  class="h-full bg-gradient-electric relative overflow-hidden"
                  :style="{ width: `${pct}%` }"
                >
                  <div class="absolute inset-0 animate-shimmer" />
                </div>
              </div>
            </div>
          </div>
          <div class="hidden md:block">
            <NeonButton :href="route('player.modes')">
              Continuer <ArrowRight class="h-4 w-4" />
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- STATS -->
      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="s in stats" :key="s.label" class="rounded-2xl p-5 glass hover-lift">
          <component :is="s.icon" :class="`h-6 w-6 ${s.color}`" />
          <div class="mt-3 font-display text-2xl md:text-3xl">{{ s.value }}</div>
          <div class="text-xs uppercase tracking-widest text-muted-foreground mt-1">{{ s.label }}</div>
        </div>
      </div>

      <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <!-- VILLES DISPONIBLES -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-lg flex items-center gap-2">
              <MapPin class="h-5 w-5 text-electric" />Villes à Explorer
            </h2>
          </div>
          
          <div class="grid gap-6 sm:grid-cols-2">
            <Link
              v-for="city in cities"
              :key="city.id"
              :href="route('player.game', city.id)"
              class="block rounded-2xl glass hover-lift overflow-hidden group"
            >
              <div class="relative h-48">
                <img v-if="city.image_path" :src="city.image_path" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div v-else class="absolute inset-0 bg-gradient-to-br from-electric/20 to-purple-neon/20 flex items-center justify-center">
                  <MapPin class="h-12 w-12 text-electric/40" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-transparent to-transparent" />
                <div class="absolute bottom-4 left-4">
                  <h3 class="font-display text-xl text-white">{{ city.name }}</h3>
                  <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
                    <span class="text-electric font-bold">{{ city.locations_count || 0 }} Lieux</span>
                    <span>•</span>
                    <span>Active</span>
                  </div>
                </div>
                <div class="absolute top-4 right-4 h-10 w-10 rounded-full bg-electric/20 backdrop-blur-sm border border-electric/30 grid place-items-center text-electric group-hover:bg-electric group-hover:text-electric-foreground transition-colors">
                  <ArrowRight class="h-5 w-5" />
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- ACHIEVEMENTS -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-lg flex items-center gap-2">
              <Award class="h-5 w-5 text-electric" />Succès
            </h2>
            <Link :href="route('player.rewards')" class="text-xs text-electric hover:underline flex items-center gap-1">Voir tout <ArrowRight class="h-3 w-3" /></Link>
          </div>
          <div class="rounded-2xl glass p-5 space-y-4">
            <div v-for="a in achievements" :key="a.name" class="flex items-center gap-4">
              <div :class="`h-12 w-12 rounded-xl grid place-items-center ${a.rare ? 'bg-purple-neon/20 border border-purple-neon shadow-purple' : 'bg-electric/10 border border-electric/30'}`">
                <component :is="a.icon" :class="`h-5 w-5 ${a.rare ? 'text-purple-neon' : 'text-electric'}`" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-bold text-sm truncate">{{ a.name }}</div>
                <div class="text-xs text-muted-foreground truncate">{{ a.desc }}</div>
              </div>
              <div v-if="a.rare" class="text-[10px] text-purple-neon font-display font-black tracking-widest">RARE</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
