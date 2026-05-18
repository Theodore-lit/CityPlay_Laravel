<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, Lock, MapPin, Star, Filter, ArrowRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 pb-28 md:pb-12">
      <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
          <div class="text-xs text-electric uppercase tracking-widest font-bold">Carte du Monde</div>
          <h1 class="font-display text-3xl md:text-5xl mt-1">Choisissez votre <span class="text-electric neon-text">Destination</span></h1>
        </div>
        <div class="flex gap-2">
          <div class="relative flex-1 md:w-72 group">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground group-focus-within:text-electric transition-colors" />
            <input 
              v-model="searchQuery"
              placeholder="Rechercher une ville ou un secret..." 
              class="w-full h-11 pl-10 pr-3 rounded-xl bg-gaming-darker/80 border border-electric/30 text-sm text-white placeholder:text-muted-foreground/40 focus:border-electric focus:shadow-neon outline-none transition-all" 
            />
          </div>
          <button class="h-11 px-4 rounded-xl glass border-electric/40 text-sm flex items-center gap-2 hover:text-electric transition-colors">
            <Filter class="h-4 w-4" />Filtrer
          </button>
        </div>
      </div>

      <!-- MINI MAP -->
      <div class="mt-6 relative h-44 md:h-56 rounded-2xl glass-strong overflow-hidden border border-electric/10">
        <div class="absolute inset-0 grid-bg opacity-30" />
        <div class="absolute inset-0">
          <div v-for="p in mapPoints" :key="p.label" class="absolute" :style="{ top: p.top, left: p.left }">
            <div class="relative">
              <div class="absolute inset-0 h-4 w-4 rounded-full bg-electric/40 animate-ping" />
              <div class="h-4 w-4 rounded-full bg-electric shadow-neon" />
              <div class="absolute top-5 left-1/2 -translate-x-1/2 text-[10px] font-display text-electric whitespace-nowrap">{{ p.label }}</div>
            </div>
          </div>
        </div>
        <div class="absolute top-3 left-3 text-xs text-muted-foreground uppercase tracking-widest">RÉPUBLIQUE DU BÉNIN</div>
      </div>

      <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(c, i) in filteredCities"
          :key="c.id"
          :href="gameMode === 'aventure' ? route('player.adventure.setup', c.id) : route('player.game', c.id)"
          class="group relative overflow-hidden rounded-2xl glass hover-lift block aspect-[4/5] animate-fade-up"
          :style="{ animationDelay: `${i * 60}ms` }"
        >
          <img 
            :src="c.image_path || 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800'" 
            :alt="c.name" 
            loading="lazy"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
          />
          <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/40 to-transparent" />
          
          <div class="absolute top-4 left-4 right-4 flex justify-between">
            <span class="px-3 py-1 rounded-full glass text-[10px] text-electric font-bold uppercase tracking-widest">Bénin</span>
            <span class="px-2 py-1 rounded-full glass text-xs flex items-center gap-1">
              <Star class="h-3 w-3 fill-electric text-electric" />4.9
            </span>
          </div>
          
          <div class="absolute bottom-5 left-5 right-5">
            <h3 class="font-display text-2xl text-white">{{ c.name }}</h3>
            
            <!-- PROGRESS BAR -->
            <div class="mt-4">
              <div class="flex justify-between items-center mb-1 text-[10px] font-bold uppercase tracking-widest">
                <span class="text-electric">{{ c.progress_percentage }}% DÉCOUVERT</span>
                <span class="text-muted-foreground">{{ c.discovered_count }}/{{ c.total_count }} LIEUX</span>
              </div>
              <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden border border-white/5">
                <div 
                  class="h-full bg-gradient-electric transition-all duration-1000" 
                  :style="{ width: `${c.progress_percentage}%` }"
                />
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span class="flex items-center gap-1"><MapPin class="h-3 w-3" />{{ c.locations_count || 0 }} missions</span>
              </div>
              <div class="h-8 w-8 rounded-lg glass border-white/10 grid place-items-center text-white group-hover:bg-electric transition-all">
                <ArrowRight class="h-4 w-4" />
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
