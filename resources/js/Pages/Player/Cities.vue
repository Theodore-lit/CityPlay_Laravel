<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { firstStorageUrl, storageUrl } from '@/lib/storageUrl';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  Search, Lock, MapPin, Star, Filter, ArrowRight, 
  Calendar, Image as ImageIcon, ChevronLeft, 
  ChevronRight, X, Eye, CheckCircle2 
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    cities: Array,
    gameMode: String,
});

const searchQuery = ref('');
const selectedEvent = ref(null);
const currentImageIndex = ref(0);

const filteredCities = computed(() => {
    if (!searchQuery.value) return props.cities;
    return props.cities.filter(c =>
        c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||       
        (c.description && c.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

// Computed - Centralisation et tri des événements chronologiquement
const allEvents = computed(() => {
  return filteredCities.value
    .flatMap(city => (city.events || []).map(event => ({ ...event, cityName: city.name })))
    .sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
});

// Modal Actions
const openEventModal = (event) => {
  selectedEvent.value = event;
  currentImageIndex.value = 0;
};

const handleImageNavigation = (direction) => {
  const images = selectedEvent.value?.images || [];
  if (!images.length) return;
  
  if (direction === 'next') {
    currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
  } else {
    currentImageIndex.value = (currentImageIndex.value - 1 + images.length) % images.length;
  }
};

// Utilities
const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=800';
const DEFAULT_CITY_IMAGE = 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800';

const cityImage = (city) => city?.image_url || storageUrl(city?.image_path) || DEFAULT_CITY_IMAGE;
const eventImage = (event) => firstStorageUrl(event?.image_urls) || firstStorageUrl(event?.images) || DEFAULT_EVENT_IMAGE;

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
          :class="cn('hud-glass-card group cursor-pointer overflow-hidden rounded-[2.5rem] relative border-2 transition-all duration-500 block animate-fade-up', c.has_completed_adventure ? 'grayscale-[0.8] opacity-80 border-white/5' : 'border-white/5 hover:border-primary/40')"
          :style="{ animationDelay: `${i * 100}ms` }"
        >
          <div class="relative aspect-[16/9] overflow-hidden">
            <img
              :src="cityImage(c)"
              :alt="c.name"
              class="w-full h-full object-cover city-hud-img"
            />
            <div class="absolute inset-0 bg-primary/20 mix-blend-color opacity-40" />
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-90" />
            <div class="absolute inset-0 grid-bg opacity-20 group-hover:opacity-40 transition-opacity" />

            <div v-if="c.has_completed_adventure" class="absolute top-4 right-4 px-3 py-1 rounded-full bg-green-500/20 border border-green-500/40 text-green-400 text-[8px] font-black uppercase tracking-widest flex items-center gap-1.5 backdrop-blur-md">
              <CheckCircle2 class="h-3 w-3" /> EXPLORÉE
            </div>
            <div v-else class="absolute top-4 right-4 px-3 py-1 rounded-full glass border-white/10 text-[8px] text-primary font-black uppercase tracking-widest">
              {{ c.progress_percentage || 0 }}% DATA
            </div>
          </div>

          <div class="p-6">
            <h3 class="font-display text-2xl text-white font-black uppercase italic tracking-tighter group-hover:text-primary transition-colors">
              {{ c.name }}
            </h3>

            <!-- PROGRESS BAR HUD -->
            <div class="mt-4">
              <div class="flex justify-between items-center mb-2 text-[8px] font-black uppercase tracking-[0.2em]">
                <span class="text-white/60">EXPLORATION_STATUS</span>
                <span class="text-primary">{{ c.discovered_count || 0 }} / {{ c.total_count || 0 }} NODES</span>
              </div>
              <div class="segmented-progress">
                <div v-for="seg in 10" :key="seg" 
                     :class="cn('progress-segment', (seg * 10) <= (c.progress_percentage || 0) ? 'active' : '')">
                </div>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
              <div class="flex items-center gap-4 text-[9px] font-black tracking-widest text-white/60 uppercase">
                <span class="flex items-center gap-1.5"><MapPin class="h-3 w-3 text-primary" />{{ c.total_count || 0 }} NODES</span>
              </div>
              <div class="h-9 w-9 rounded-full border border-primary/20 flex items-center justify-center group-hover:border-primary group-hover:bg-primary/10 transition-all">
                <ArrowRight class="h-4 w-4 text-primary group-hover:translate-x-1 transition-transform" />
              </div>
            </div>
          </div>
        </Link>
      </div>

      <!-- ================= EVENTS SECTION ================= -->
      <div v-if="allEvents.length" class="mt-24 animate-fade-up">
        <div class="mb-12 flex items-center justify-between">
          <div>
            <div class="glass mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.3em] text-accent">
              <Calendar class="h-3 w-3" /> Agenda Culturel
            </div>
            <h2 class="font-display text-4xl font-black uppercase italic leading-none tracking-tight md:text-5xl text-white">
              Événements <span class="text-accent">phare</span>
            </h2>
          </div>
        </div>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <div 
            v-for="event in allEvents" 
            :key="event.id"
            @click="openEventModal(event)"
            class="glass-strong group relative overflow-hidden rounded-[2.5rem] border border-white/5 cursor-pointer transition-all duration-500 hover:border-accent/30"
          >
            <div class="relative aspect-video overflow-hidden bg-black">
              <img 
                :src="eventImage(event)" 
                :alt="event.title"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent to-transparent opacity-60" />
            </div>
            
            <div class="p-8">
              <div class="mb-4 flex items-center justify-between text-[10px] uppercase">
                <span class="font-black tracking-widest text-accent">{{ event.cityName }}</span>
                <div class="flex items-center gap-2 font-bold text-white/60">
                  <Calendar class="h-3 w-3" /> {{ formatDate(event.event_date) }}
                </div>
              </div>
              
              <h3 class="font-display text-xl font-black uppercase italic text-white transition-colors group-hover:text-accent mb-3">
                {{ event.title }}
              </h3>
              <p class="line-clamp-2 text-xs leading-relaxed text-white/70 mb-6 uppercase font-bold tracking-widest">
                {{ event.description }}
              </p>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-[10px] font-black tracking-widest text-white/60">
                  <div class="glass flex h-8 w-8 items-center justify-center rounded-lg text-accent">
                    <ImageIcon class="h-4 w-4" />
                  </div>
                  <span>{{ event.images?.length || 0 }} PHOTOS</span>
                </div>
                <div class="glass flex h-10 w-10 items-center justify-center rounded-full text-white transition-all group-hover:bg-accent group-hover:text-black">
                  <ArrowRight class="h-5 w-5" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <MobileTabBar />

    <!-- ================= EVENT MODAL OVERLAY ================= -->
    <Transition name="modal">
      <div v-if="selectedEvent" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 backdrop-blur-xl bg-zinc-950/90">
        <div class="absolute inset-0" @click="selectedEvent = null" />
        
        <div class="relative w-full max-w-5xl overflow-hidden rounded-[3rem] border border-white/10 bg-zinc-900 shadow-2xl animate-scale-up">
          <button @click="selectedEvent = null" class="glass absolute top-6 right-6 z-10 flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
            <X class="h-6 w-6" />
          </button>

          <div class="grid h-full max-h-[90vh] lg:grid-cols-2">
            <!-- Left Side: Image Gallery -->
            <div class="relative flex h-[40vh] items-center justify-center bg-black lg:h-full">
              <img 
                :src="firstStorageUrl(selectedEvent?.image_urls) || storageUrl(selectedEvent?.images?.[currentImageIndex]) || DEFAULT_EVENT_IMAGE" 
                :alt="selectedEvent.title"
                class="h-full w-full object-cover transition-all duration-500"
              />
              
              <div v-if="selectedEvent.images?.length > 1" class="absolute inset-x-0 bottom-8 flex justify-center gap-4">
                <button @click="handleImageNavigation('prev')" class="glass-strong flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
                  <ChevronLeft class="h-6 w-6" />
                </button>
                <button @click="handleImageNavigation('next')" class="glass-strong flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
                  <ChevronRight class="h-6 w-6" />
                </button>
              </div>
              
              <div class="glass absolute top-8 left-8 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white">
                {{ currentImageIndex + 1 }} / {{ selectedEvent.images?.length || 1 }}
              </div>
            </div>

            <!-- Right Side: Metadata Description -->
            <div class="custom-scrollbar overflow-y-auto p-8 md:p-12">
              <div class="glass mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.3em] text-accent">
                {{ selectedEvent.cityName }}
              </div>
              
              <h2 class="font-display text-3xl font-black uppercase italic leading-tight text-white mb-6 md:text-5xl">
                {{ selectedEvent.title }}
              </h2>
              
              <div class="mb-8 grid grid-cols-2 gap-6">
                <div class="glass rounded-2xl border border-white/5 p-4">
                  <div class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">Date de l'événement</div>
                  <div class="flex items-center gap-3 font-bold text-white">
                    <Calendar class="h-5 w-5 text-accent" /> {{ formatDate(selectedEvent.event_date) }}
                  </div>
                </div>
                <div class="glass rounded-2xl border border-white/5 p-4">
                  <div class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">Lieu</div>
                  <div class="flex items-center gap-3 font-bold text-white">
                    <MapPin class="h-5 w-5 text-accent" /> {{ selectedEvent.location_name || 'Non spécifié' }}
                  </div>
                </div>
              </div>

              <div class="space-y-6">
                <div>
                  <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-accent mb-4">À propos</h4>
                  <p class="font-bold text-lg leading-relaxed text-white/70 uppercase tracking-widest">
                    {{ selectedEvent.description }}
                  </p>
                </div>
                
                <div class="pt-8 border-t border-white/10">
                  <NeonButton class="w-full rounded-2xl border-none bg-gradient-to-r from-cyan-400 to-blue-500 py-6 shadow-2xl shadow-primary/20">
                    S'y rendre avec CityPlay <ArrowRight class="ml-3 h-5 w-5" />
                  </NeonButton>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </SiteLayout>
</template>

<style scoped>
.neon-text {
  text-shadow: 0 0 10px rgba(0, 112, 255, 0.5), 0 0 20px rgba(0, 112, 255, 0.3);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.animate-scale-up {
  animation: scale-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes scale-up {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
