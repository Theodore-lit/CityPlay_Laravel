<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  Trophy, Zap, MapPin, Target, Award, Flame, ArrowRight, Crown, Sparkles, Heart,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

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
  { icon: Trophy, label: 'Missions', value: '47', color: 'text-primary' },
  { icon: Zap, label: 'Total XP', value: '12,840', color: 'text-secondary' },
  { icon: MapPin, label: 'Villes', value: '4 / 5', color: 'text-accent' },
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
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 pb-28 md:pb-12">
      <!-- PROFILE HERO -->
      <div class="relative overflow-hidden rounded-[2rem] glass-strong p-5 md:p-8 hover-game border-white/20">
        <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
        <div class="absolute -top-20 -right-10 h-64 w-64 rounded-full bg-primary/15 blur-3xl animate-pulse-soft" />
        <div class="relative flex flex-col md:flex-row md:items-center gap-5">
          <div class="relative group shrink-0">
            <div class="h-20 w-20 md:h-24 md:w-24 rounded-2xl bg-gradient-premium grid place-items-center font-display text-3xl font-black text-white shadow-lg animate-float-game">
              {{ user.name.substring(0, 2).toUpperCase() }}
            </div>
            <div class="absolute -bottom-2 -right-1 px-2 py-1 rounded-lg bg-accent text-white text-[8px] font-display font-black shadow-purple uppercase tracking-widest border border-white/20">
              LVL 24
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[9px] text-primary uppercase tracking-[0.3em] font-black animate-pulse">Explorateur Mythique</div>
            <h1 class="font-display text-2xl md:text-4xl mt-0.5 neon-text truncate">{{ user.name }}</h1>
            <div class="mt-4">
              <div class="flex justify-between text-[9px] uppercase tracking-widest font-black mb-1.5">
                <span class="text-muted-foreground">Progression</span>
                <span class="text-primary">{{ xp }} / {{ xpMax }} XP</span>
              </div>
              <div class="h-2.5 rounded-full bg-muted/40 overflow-hidden border border-white/10">
                <div
                  class="h-full bg-gradient-premium rounded-full transition-all duration-1000 relative"
                  :style="{ width: `${pct}%` }"
                >
                    <div class="absolute inset-0 bg-white/20 animate-shimmer" />
                </div>
              </div>
            </div>
          </div>
          <div class="hidden md:block">
            <NeonButton :href="route('player.modes')" size="md" class="rounded-xl px-6">
              JOUER <ArrowRight class="h-4 w-4" />
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- STATS -->
      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="s in stats" :key="s.label" class="rounded-2xl p-4 glass hover-game group border-white/10">
          <div class="flex items-center gap-3">
              <div :class="cn('h-10 w-10 rounded-xl bg-white/40 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform shrink-0', s.color)">
                <component :is="s.icon" class="h-5 w-5" />
              </div>
              <div>
                  <div class="font-display text-xl font-black">{{ s.value }}</div>
                  <div class="text-[8px] uppercase tracking-widest text-muted-foreground font-black">{{ s.label }}</div>
              </div>
          </div>
        </div>
      </div>

      <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <!-- VILLES DISPONIBLES -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-base flex items-center gap-2 uppercase tracking-tight">
              <MapPin class="h-4 w-4 text-primary" />Objectifs de mission
            </h2>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <Link
              v-for="city in cities"
              :key="city.id"
              :href="route('player.game', city.id)"
              class="block rounded-[1.5rem] glass hover-game overflow-hidden group border-white/10"
            >
              <div class="relative h-44">
                <img v-if="city.image_path" :src="city.image_path" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
                <div v-else class="absolute inset-0 bg-gradient-premium opacity-10 flex items-center justify-center">
                  <MapPin class="h-10 w-10 text-primary/30" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-background via-background/10 to-transparent" />
                <div class="absolute bottom-4 left-5 right-4">
                  <h3 class="font-display text-lg text-foreground neon-text uppercase tracking-tight">{{ city.name }}</h3>
                  <div class="flex items-center gap-2 text-[9px] text-muted-foreground font-bold mt-1 uppercase tracking-widest">
                    <span class="text-primary">{{ city.locations_count || 0 }} Objectifs</span>
                    <span>•</span>
                    <span>Disponible</span>
                  </div>
                </div>
                <div class="absolute top-4 right-4 h-9 w-9 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 grid place-items-center text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                  <ArrowRight class="h-4 w-4" />
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- ACHIEVEMENTS -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-base flex items-center gap-2 uppercase tracking-tight">
              <Award class="h-4 w-4 text-primary" />Succès
            </h2>
            <Link :href="route('player.rewards')" class="text-[10px] text-primary font-black uppercase tracking-widest hover:underline flex items-center gap-1">Détails <ArrowRight class="h-2.5 w-2.5" /></Link>
          </div>
          <div class="rounded-[1.5rem] glass-strong p-5 space-y-4 border-white/10">
            <div v-for="a in achievements" :key="a.name" class="flex items-center gap-3 group">
              <div :class="cn(
                'h-11 w-11 rounded-xl grid place-items-center border transition-all duration-300 group-hover:scale-110 shrink-0',
                a.rare ? 'bg-accent/10 border-accent shadow-purple' : 'bg-primary/10 border-primary/20 shadow-sm'
              )">
                <component :is="a.icon" :class="cn('h-5 w-5', a.rare ? 'text-accent' : 'text-primary')" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-display font-black text-xs truncate uppercase tracking-tight">{{ a.name }}</div>
                <div class="text-[8px] text-muted-foreground truncate uppercase tracking-[0.1em] font-bold">{{ a.desc }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
