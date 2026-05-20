<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, Lock, MapPin, Star, Filter, ArrowRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    cities: Array,
    gameMode: String,
});

const searchQuery = ref('');

const filteredCities = computed(() => {
    if (!searchQuery.value) return props.cities;
    return props.cities.filter(c =>
        c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (c.description && c.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

// Markers for the mini map - simplified version
const mapPoints = [
  { top: "60%", left: "40%", label: "Cotonou" },
  { top: "65%", left: "28%", label: "Ouidah" },
  { top: "55%", left: "48%", label: "Porto-Novo" },
  { top: "40%", left: "38%", label: "Abomey" },
  { top: "18%", left: "55%", label: "Parakou" },
];
</script>

<template>
  <Head title="Villes — CityPlay" />

  <SiteLayout isHUD>
    <HUDHeader />

    <div class="mx-auto max-w-7xl px-6 py-6 pb-24 relative z-10">
      <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-8">
        <div>
          <div class="flex items-center gap-2 mb-1.5">
            <div class="h-0.5 w-8 bg-primary" />
            <div class="text-[9px] text-primary font-black tracking-[0.3em] uppercase">GEOLOCALISATION_SYSTEM</div>
          </div>
          <h1 class="font-display text-4xl md:text-5xl font-black uppercase italic tracking-tighter text-white">
            SECTEUR <span class="text-primary drop-shadow-[0_0_10px_#06b6d4]">EXPLORATION</span>
          </h1>
        </div>
        <div class="flex gap-3">
          <div class="relative flex-1 md:w-80 group">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-white/20 group-focus-within:text-primary transition-colors" />
            <input
              v-model="searchQuery"
              placeholder="RECHERCHE_DONNEES..."
              class="w-full h-11 pl-10 pr-4 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black tracking-widest text-white placeholder:text-white/20 focus:border-primary focus:bg-white/10 outline-none transition-all"
            />
          </div>
        </div>
      </div>

      <!-- MINI MAP HUD -->
      <div class="mb-10 relative h-48 md:h-64 rounded-[2rem] neon-border-box overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-30" />
        <div class="absolute inset-0">
          <div v-for="p in mapPoints" :key="p.label" class="absolute" :style="{ top: p.top, left: p.left }">
            <div class="relative group/point">
              <div class="absolute inset-0 h-4 w-4 rounded-full bg-primary/40 animate-ping" />
              <div class="h-4 w-4 rounded-full bg-primary shadow-[0_0_15px_#06b6d4]" />
              <div class="absolute top-6 left-1/2 -translate-x-1/2 text-[8px] font-black text-primary tracking-widest whitespace-nowrap bg-black/80 px-2 py-0.5 rounded opacity-0 group-hover/point:opacity-100 transition-opacity">
                {{ p.label.toUpperCase() }}
              </div>
            </div>
          </div>
        </div>
        <div class="absolute top-4 left-6 flex items-center gap-3">
          <div class="h-1.5 w-1.5 rounded-full bg-primary animate-pulse" />
          <span class="text-[9px] text-white/60 font-black tracking-[0.4em] uppercase">RÉPUBLIQUE_DU_BÉNIN // LIVE_FEED</span>
        </div>
      </div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(c, i) in filteredCities"
          :key="c.id"
          :href="gameMode === 'aventure' ? route('player.adventure.setup', c.id) : route('player.game', c.id)"
          class="hud-glass-card group cursor-pointer overflow-hidden rounded-[2.5rem] relative border-2 border-white/5 hover:border-primary/40 transition-all duration-500 block animate-fade-up"
          :style="{ animationDelay: `${i * 100}ms` }"
        >
          <div class="relative aspect-[16/9] overflow-hidden">
            <img
              :src="c.image_path || 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800'"
              :alt="c.name"
              class="w-full h-full object-cover city-hud-img"
            />
            <div class="absolute inset-0 bg-primary/20 mix-blend-color opacity-40" />
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-90" />
            <div class="absolute inset-0 grid-bg opacity-20 group-hover:opacity-40 transition-opacity" />

            <div class="absolute top-4 right-4 px-3 py-1 rounded-full glass border-white/10 text-[8px] text-primary font-black uppercase tracking-widest">
              {{ c.progress_percentage }}% DATA
            </div>
          </div>

          <div class="p-6">
            <h3 class="font-display text-2xl text-white font-black uppercase italic tracking-tighter group-hover:text-primary transition-colors">
              {{ c.name }}
            </h3>

            <!-- PROGRESS BAR HUD -->
            <div class="mt-4">
              <div class="flex justify-between items-center mb-2 text-[8px] font-black uppercase tracking-[0.2em]">
                <span class="text-white/40">EXPLORATION_STATUS</span>
                <span class="text-primary">{{ c.discovered_count }} / {{ c.total_count }} LOCS</span>
              </div>
              <div class="segmented-progress">
                <div v-for="seg in 10" :key="seg" 
                     :class="cn('progress-segment', (seg * 10) <= c.progress_percentage ? 'active' : '')">
                </div>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
              <div class="flex items-center gap-4 text-[9px] font-black tracking-widest text-white/40 uppercase">
                <span class="flex items-center gap-1.5"><MapPin class="h-3 w-3 text-primary" />{{ c.total_count || 0 }} NODES</span>
              </div>
              <div class="h-9 w-9 rounded-full border border-primary/20 flex items-center justify-center group-hover:border-primary group-hover:bg-primary/10 transition-all">
                <ArrowRight class="h-4 w-4 text-primary group-hover:translate-x-1 transition-transform" />
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
