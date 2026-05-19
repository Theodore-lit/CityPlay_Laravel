<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  Trophy, Zap, MapPin, Award, Flame, ArrowRight, Crown, Sparkles,
  Lock, Network, ChevronRight, Cat, Medal,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  cities: Array,
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const level = computed(() => user.value?.level || 1);
const xp = computed(() => props.stats.xp_in_level ?? 0);
const xpMax = computed(() => props.stats.xp_per_level ?? 4000);
const pct = computed(() => Math.min(100, (xp.value / xpMax.value) * 100));

const initials = computed(() =>
  (user.value?.name || 'CP').substring(0, 2).toUpperCase()
);

const heroStats = computed(() => [
  { icon: Trophy, value: String(props.stats.missions ?? 0), label: 'Missions' },
  { icon: Zap, value: (props.stats.total_xp ?? 0).toLocaleString('fr-FR'), label: 'Total XP' },
  {
    icon: Network,
    value: `${props.stats.cities_unlocked ?? 0} / ${props.stats.cities_total ?? 0}`,
    label: 'Villes',
  },
  { icon: Flame, value: `${props.stats.streak_days ?? 0}j`, label: 'Série' },
]);

const achievements = [
  { icon: Crown, name: 'Savant Royal', epic: true, unlocked: true, tone: 'gold' },
  { icon: Sparkles, name: 'Chasseur Nocturne', epic: true, unlocked: true, tone: 'cyan' },
  { icon: Cat, name: 'Panthera', epic: false, unlocked: false },
  { icon: Flame, name: 'Série 30j', epic: false, unlocked: false },
  { icon: Medal, name: 'Champion', epic: false, unlocked: false },
];

const statusLabel = (status) => {
  if (status === 'lock') return { text: 'Objectifs indisponibles (VERROUILLÉ)', class: 'text-muted-foreground' };
  if (status === 'new') return { text: 'Objectifs disponibles (NOUVEAU)', class: 'text-orange-400' };
  return { text: 'Objectifs disponibles (OK)', class: 'text-emerald-400' };
};

const cityCardClass = (locked) =>
  cn(
    'group relative flex overflow-hidden rounded-xl border bg-card/30 backdrop-blur-sm transition-all duration-300',
    locked
      ? 'border-white/10 cursor-not-allowed opacity-75'
      : 'border-primary/20 hover:border-primary/50 hover:shadow-[0_0_24px_oklch(0.70_0.18_250/0.12)]'
  );
</script>

<template>
  <Head title="Tableau de bord — CityPlay" />

  <SiteLayout :hide-footer="true">
    <div class="relative min-h-[calc(100vh-4rem)]">
      <div class="pointer-events-none absolute inset-0 grid-bg opacity-[0.07]" />

      <div class="relative mx-auto max-w-[1400px] px-4 sm:px-8 lg:px-12 py-6 pb-28 md:pb-10 space-y-8">

        <section
          class="relative overflow-hidden rounded-2xl border border-primary/40 bg-card/40 backdrop-blur-xl shadow-[0_0_40px_oklch(0.70_0.18_250/0.15)]"
        >
          <div class="absolute inset-0 grid-bg opacity-[0.06] pointer-events-none" />
          <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/80 to-transparent" />

          <div class="relative p-6 md:p-8 lg:p-10">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
              <div class="flex shrink-0 justify-center lg:justify-start">
                <div
                  class="hex-badge h-28 w-28 md:h-32 md:w-32 flex flex-col items-center justify-center border-2 border-primary/50 bg-primary/10 shadow-[0_0_30px_oklch(0.70_0.18_250/0.35)]"
                >
                  <span class="font-display text-3xl md:text-4xl font-black text-white tracking-tight">
                    {{ initials }}
                  </span>
                  <span class="mt-1 text-[10px] font-black uppercase tracking-[0.25em] text-primary">
                    Niv. {{ level }}
                  </span>
                </div>
              </div>

              <div class="flex-1 space-y-4 text-center lg:text-left">
                <div>
                  <p class="text-[10px] font-black uppercase tracking-[0.35em] text-primary/80 mb-1">
                    Explorateur mythique
                  </p>
                  <h1 class="font-display text-2xl sm:text-3xl md:text-4xl font-black text-foreground uppercase tracking-tight">
                    {{ user?.name }}
                  </h1>
                </div>

                <div class="max-w-xl mx-auto lg:mx-0">
                  <div class="flex justify-end text-[10px] font-bold uppercase tracking-wider text-primary mb-1.5">
                    {{ xp }} / {{ xpMax }} XP
                  </div>
                  <div class="xp-segments h-3.5 w-full overflow-hidden rounded-sm border border-primary/25 bg-muted/30 p-[2px] flex gap-[2px]">
                    <div
                      class="h-full bg-gradient-to-r from-primary via-cyan-400 to-primary shadow-[0_0_12px_oklch(0.70_0.18_250/0.6)] transition-all duration-700"
                      :style="{ width: `${pct}%` }"
                    />
                    <div class="flex-1 flex gap-[2px] opacity-30">
                      <div v-for="i in 12" :key="i" class="flex-1 h-full bg-primary/20 skew-x-[-12deg]" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-center lg:justify-end shrink-0">
                <Link
                  :href="route('player.modes')"
                  class="group relative flex flex-col items-center"
                >
                  <div class="absolute -inset-2 rounded-2xl bg-primary/20 blur-xl opacity-60 group-hover:opacity-100 transition-opacity" />
                  <div
                    class="relative flex items-center gap-3 rounded-xl border border-primary/50 bg-primary px-8 py-4 font-display text-lg font-black uppercase tracking-wider text-primary-foreground shadow-neon hover:scale-[1.02] active:scale-[0.98] transition-transform"
                  >
                    Jouer
                    <ChevronRight class="h-5 w-5 stroke-[3]" />
                  </div>
                  <span class="mt-2 text-[9px] font-bold uppercase tracking-[0.2em] text-muted-foreground group-hover:text-primary transition-colors">
                    Lancer la mission
                  </span>
                </Link>
              </div>
            </div>

            <div class="mt-8 pt-6 border-t border-primary/15 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-0 md:divide-x md:divide-primary/15">
              <div
                v-for="s in heroStats"
                :key="s.label"
                class="flex items-center justify-center md:justify-start gap-3 px-2 md:px-6"
              >
                <component :is="s.icon" class="h-5 w-5 text-primary shrink-0" />
                <div class="text-center md:text-left">
                  <div class="font-display text-lg md:text-xl font-black text-foreground leading-none">
                    {{ s.value }}
                  </div>
                  <div class="text-[9px] uppercase tracking-[0.2em] text-muted-foreground font-bold mt-0.5">
                    {{ s.label }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[1fr_380px] xl:grid-cols-[1fr_420px]">
          <section>
            <h2 class="font-display text-sm font-black uppercase tracking-[0.25em] text-foreground mb-5 flex items-center gap-2">
              <MapPin class="h-4 w-4 text-primary" />
              Objectifs de mission
            </h2>

            <div class="space-y-4">
              <template v-for="city in cities" :key="city.id">
                <Link
                  v-if="city.mission_status !== 'lock'"
                  :href="route('player.game', city.id)"
                  :class="cityCardClass(false)"
                >
                  <div class="relative w-36 sm:w-44 shrink-0 overflow-hidden">
                    <img
                      v-if="city.image_path"
                      :src="city.image_path"
                      :alt="city.name"
                      class="h-full w-full object-cover min-h-[100px] transition-transform duration-500 group-hover:scale-105"
                    />
                    <div v-else class="h-full min-h-[100px] w-full bg-muted/40 flex items-center justify-center">
                      <MapPin class="h-8 w-8 text-muted-foreground/40" />
                    </div>
                  </div>
                  <div class="flex flex-1 flex-col justify-center p-4 sm:p-5">
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground mb-1">Ville</span>
                    <h3 class="font-display text-base sm:text-lg font-black uppercase text-foreground tracking-tight">{{ city.name }}</h3>
                    <p :class="cn('mt-2 text-[10px] font-bold uppercase tracking-wide', statusLabel(city.mission_status).class)">
                      {{ statusLabel(city.mission_status).text }}
                    </p>
                  </div>
                  <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-primary/0 group-hover:text-primary transition-all" />
                </Link>

                <div v-else :class="cityCardClass(true)">
                  <div class="relative w-36 sm:w-44 shrink-0 overflow-hidden">
                    <img
                      v-if="city.image_path"
                      :src="city.image_path"
                      :alt="city.name"
                      class="h-full w-full object-cover min-h-[100px] grayscale opacity-50"
                    />
                    <div v-else class="h-full min-h-[100px] w-full bg-muted/40 flex items-center justify-center">
                      <MapPin class="h-8 w-8 text-muted-foreground/40" />
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-[1px]">
                      <div class="flex flex-col items-center gap-1">
                        <Lock class="h-8 w-8 text-white/70" />
                        <span class="text-[9px] font-black uppercase tracking-widest text-white/60">Verrouillé</span>
                      </div>
                    </div>
                  </div>
                  <div class="flex flex-1 flex-col justify-center p-4 sm:p-5">
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground mb-1">Ville</span>
                    <h3 class="font-display text-base sm:text-lg font-black uppercase text-foreground tracking-tight">{{ city.name }}</h3>
                    <p :class="cn('mt-2 text-[10px] font-bold uppercase tracking-wide', statusLabel(city.mission_status).class)">
                      {{ statusLabel(city.mission_status).text }}
                    </p>
                  </div>
                </div>
              </template>

              <p v-if="!cities?.length" class="text-sm text-muted-foreground text-center py-8">
                Aucune ville active pour le moment.
              </p>
            </div>
          </section>

          <section>
            <h2 class="font-display text-sm font-black uppercase tracking-[0.25em] text-foreground mb-5 flex items-center gap-2">
              <Award class="h-4 w-4 text-primary" />
              Succès
            </h2>

            <div class="rounded-2xl border border-primary/20 bg-card/30 backdrop-blur-sm p-5 sm:p-6">
              <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground mb-5 text-center">
                Temple des agents
              </p>

              <div class="hex-grid mx-auto max-w-[280px]">
                <div
                  v-for="a in achievements"
                  :key="a.name"
                  :class="cn(
                    'hex-cell flex flex-col items-center justify-center gap-1 p-2 transition-transform',
                    a.unlocked ? 'hover:scale-105' : 'opacity-40 grayscale'
                  )"
                >
                  <div
                    :class="cn(
                      'hex-badge h-16 w-16 sm:h-[4.5rem] sm:w-[4.5rem] flex items-center justify-center border-2',
                      a.unlocked && a.tone === 'gold' && 'border-amber-400/60 bg-amber-500/20 shadow-[0_0_20px_rgba(251,191,36,0.4)]',
                      a.unlocked && a.tone === 'cyan' && 'border-primary/60 bg-primary/15 shadow-[0_0_20px_oklch(0.70_0.18_250/0.35)]',
                      !a.unlocked && 'border-white/15 bg-muted/20'
                    )"
                  >
                    <component
                      :is="a.icon"
                      :class="cn(
                        'h-6 w-6',
                        a.unlocked && a.tone === 'gold' && 'text-amber-300',
                        a.unlocked && a.tone === 'cyan' && 'text-primary',
                        !a.unlocked && 'text-muted-foreground'
                      )"
                    />
                  </div>
                  <span v-if="a.epic && a.unlocked" class="text-[7px] font-black uppercase text-amber-400 tracking-wider">
                    Épique
                  </span>
                </div>
              </div>

              <Link
                :href="route('player.rewards')"
                class="mt-6 flex items-center justify-center gap-2 w-full py-3 rounded-xl border border-dashed border-primary/30 text-[10px] font-black uppercase tracking-widest text-primary hover:bg-primary/5 transition-colors"
              >
                Voir les archives
                <ArrowRight class="h-3.5 w-3.5" />
              </Link>

              <div class="mt-5 relative overflow-hidden rounded-xl bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 p-5 flex items-center justify-between">
                <div class="absolute -right-6 -bottom-6 h-28 w-28 rounded-full bg-white/10 blur-2xl" />
                <div class="relative z-10">
                  <p class="text-[9px] font-black uppercase tracking-widest text-white/80">Série active</p>
                  <p class="font-display text-3xl font-black text-white italic tracking-tight">
                    {{ stats.streak_days ?? 0 }}<span class="text-lg not-italic"> j</span>
                  </p>
                </div>
                <Flame class="relative z-10 h-14 w-14 text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)] animate-pulse" />
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>

    <MobileTabBar />
  </SiteLayout>
</template>

<style scoped>
.hex-badge {
  clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
}

.hex-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem 0.5rem;
  justify-items: center;
}

.hex-cell:nth-child(1) { grid-column: 1; grid-row: 1; }
.hex-cell:nth-child(2) { grid-column: 3; grid-row: 1; }
.hex-cell:nth-child(3) { grid-column: 1; grid-row: 2; }
.hex-cell:nth-child(4) { grid-column: 2; grid-row: 2; }
.hex-cell:nth-child(5) { grid-column: 3; grid-row: 2; }

.xp-segments > div:first-child {
  clip-path: polygon(0 0, calc(100% - 4px) 0, 100% 100%, 4px 100%);
}
</style>
