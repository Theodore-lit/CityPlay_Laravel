<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  Search, Filter, ArrowRight, Compass, Shield, 
  Zap, Calendar, ImageIcon, ChevronLeft, 
  ChevronRight, X, Star, MapPin, User, Users 
} from 'lucide-vue-next';

import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  cities: { type: Array, default: () => [] },
  gameMode: { type: String, default: 'aventure' },
});

// States
const searchQuery = ref('');
const selectedEvent = ref(null);
const currentImageIndex = ref(0);
const showModeModal = ref(false);
const selectedCity = ref(null);

// Default Fallbacks
const DEFAULT_CITY_IMAGE = 'https://images.unsplash.com/photo-1590603783930-9d93dcf99723?auto=format&fit=crop&q=80&w=800';
const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=800';

// Modal Actions
const handleCityClick = (city) => {
  if (props.gameMode === 'aventure') {
    selectedCity.value = city;
    showModeModal.value = true;
  } else {
    router.get(route('player.game', city.id));
  }
};

const startSolo = () => {
  showModeModal.value = false;
  router.post(route('player.adventure.solo', selectedCity.value.id));
};

const startTeam = () => {
  showModeModal.value = false;
  router.get(route('teams.index', { city_id: selectedCity.value.id }));
};

// Computed - Filtrage des villes
const filteredCities = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return props.cities;
  
  return props.cities.filter(city => 
    city.name?.toLowerCase().includes(query) ||
    city.description?.toLowerCase().includes(query)
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

// Map configuration
const mapPoints = [
  { top: "60%", left: "40%", label: "Cotonou", color: "text-primary" },
  { top: "65%", left: "28%", label: "Ouidah", color: "text-accent" },
  { top: "55%", left: "48%", label: "Porto-Novo", color: "text-primary" },
  { top: "40%", left: "38%", label: "Abomey", color: "text-accent" },
  { top: "18%", left: "55%", label: "Parakou", color: "text-primary" },
];
</script>

<template>
  <Head title="Destinations — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 py-12 pb-28 sm:px-6 md:pb-12">
      
      <!-- ================= HEADER SECTION ================= -->
      <div class="mb-12 flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
        <div class="max-w-2xl">
          <div class="glass mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.3em] text-primary">
            <Compass class="h-3 w-3" /> Atlas de l'Explorateur
          </div>
          <h1 class="font-display text-4xl font-black uppercase italic leading-none tracking-tight md:text-6xl">
            Choisissez votre <span class="bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">prochaine quête</span>
          </h1>
        </div>
        
        <div class="flex w-full gap-3 md:w-auto">
          <div class="group relative flex-1 md:w-80">
            <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary" />
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher un secret..." 
              class="h-14 w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 font-medium text-white placeholder:text-muted-foreground/40 outline-none transition-all focus:border-primary/50 focus:bg-primary/5" 
            />
          </div>
          <button class="glass-strong flex h-14 items-center gap-3 rounded-2xl border border-white/10 px-6 text-sm font-black uppercase tracking-widest transition-all hover:border-primary/40">
            <Filter class="h-5 w-5 text-primary" />
          </button>
        </div>
      </div>

      <!-- ================= HOLOGRAPHIC MINI MAP ================= -->
      <div class="group relative mb-16 h-64 overflow-hidden rounded-[2.5rem] border border-white/10 bg-gaming-darker md:h-80">
        <div class="grid-bg absolute inset-0 opacity-20 transition-opacity group-hover:opacity-30" />
        
        <!-- Radar Scanning Animation Line -->
        <div class="animate-radar absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent pointer-events-none" />

        <!-- Map Nodes -->
        <div class="absolute inset-0 p-8">
          <div 
            v-for="point in mapPoints" 
            :key="point.label" 
            class="absolute transition-all duration-500 hover:scale-125" 
            :style="{ top: point.top, left: point.left }"
          >
            <div class="group/point relative cursor-pointer">
              <div class="absolute inset-0 h-6 w-6 -translate-x-1/2 -translate-y-1/2 animate-ping rounded-full bg-primary/20" />
              <div class="h-6 w-6 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-gaming-darker bg-primary shadow-2xl shadow-primary/50" />
              <div :class="['absolute top-4 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-black uppercase tracking-widest opacity-50 transition-opacity group-hover/point:opacity-100', point.color]">
                {{ point.label }}
              </div>
            </div>
          </div>
        </div>
        
        <!-- System Metadata footer -->
        <div class="absolute bottom-6 left-8 flex items-center gap-4 text-[10px] font-black uppercase tracking-[0.2em] text-white/40">
          <div class="flex items-center gap-2">
            <div class="h-2 w-2 animate-pulse rounded-full bg-primary" />
            <span>Région Dahomey Active</span>
          </div>
          <div class="h-4 w-[1px] bg-white/10" />
          <div>Atlas v4.0.2</div>
        </div>
      </div>

      <!-- ================= CITIES GRID ================= -->
      <div class="mb-24 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="(city, i) in filteredCities"
          :key="city.id"
          @click="handleCityClick(city)"
          class="glass-strong group relative block aspect-[3/4] animate-fade-up overflow-hidden rounded-[2.5rem] border border-white/5 transition-all duration-700 hover:border-primary/20 hover:shadow-2xl hover:shadow-primary/20 cursor-pointer"
          :style="{ animationDelay: `${i * 100}ms` }"
        >
          <!-- Thumbnail & Overlay -->
          <div class="absolute inset-0">
            <img 
              :src="city.image_path || DEFAULT_CITY_IMAGE" 
              :alt="city.name" 
              loading="lazy"
              class="h-full w-full object-cover grayscale-[0.3] transition-transform duration-1000 group-hover:scale-110 group-hover:grayscale-0" 
            />
            <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/20 to-transparent opacity-90" />
          </div>
          
          <!-- Floating Header Badges -->
          <div class="absolute top-6 left-6 right-6 flex items-start justify-between">
            <div class="glass rounded-full border border-primary/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-primary">
              {{ city.progress_percentage === 100 ? 'Conquis' : 'Explorable' }}
            </div>
            <div class="glass-strong flex h-12 w-12 flex-col items-center justify-center rounded-2xl border border-white/10 transition-all group-hover:bg-primary group-hover:text-black">
              <Star class="h-4 w-4 fill-current" />
              <span class="mt-1 text-[10px] font-black">4.9</span>
            </div>
          </div>
          
          <!-- Card Info Wrapper -->
          <div class="absolute bottom-8 left-8 right-8">
            <h3 class="font-display text-3xl font-black uppercase italic text-white transition-transform group-hover:translate-x-2 md:text-4xl">
              {{ city.name }}
            </h3>
            <p class="mt-2 line-clamp-2 font-medium text-xs leading-relaxed text-white/60 transition-colors group-hover:text-white/80">
              {{ city.description }}
            </p>
            
            <!-- Gamified Progress Bar -->
            <div class="mt-6 space-y-3">
              <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-[0.2em]">
                <span class="text-primary">{{ city.progress_percentage || 0 }}% Découvert</span>
                <span class="text-white/40">{{ city.discovered_count || 0 }}/{{ city.total_count || 0 }} Quêtes</span>
              </div>
              <div class="h-2 w-full rounded-full border border-white/10 bg-white/5 p-[1px]">
                <div 
                  class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-blue-500 shadow-[0_0_10px_rgba(34,211,238,0.5)] transition-all duration-1000" 
                  :style="{ width: `${city.progress_percentage || 0}%` }"
                />
              </div>
            </div>

            <!-- Perks / Action Footer -->
            <div class="mt-8 flex items-center justify-between">
              <div class="flex items-center gap-4 text-[10px] font-black uppercase text-white/40">
                <div class="flex items-center gap-2"><Shield class="h-4 w-4" /> <span>Tier 1</span></div>
                <div class="flex items-center gap-2"><Zap class="h-4 w-4" /> <span>500 XP</span></div>
              </div>
              <div class="glass flex h-12 w-12 items-center justify-center rounded-full border border-white/10 text-white shadow-2xl transition-all group-hover:scale-110 group-hover:bg-primary group-hover:text-black">
                <ArrowRight class="h-6 w-6" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= EVENTS SECTION ================= -->
      <div v-if="allEvents.length" class="mt-24 animate-fade-up">
        <div class="mb-12 flex items-center justify-between">
          <div>
            <div class="glass mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.3em] text-accent">
              <Calendar class="h-3 w-3" /> Agenda Culturel
            </div>
            <h2 class="font-display text-4xl font-black uppercase italic leading-none tracking-tight md:text-5xl">
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
                :src="event.images?.[0] || DEFAULT_EVENT_IMAGE" 
                :alt="event.title"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-transparent to-transparent opacity-60" />
            </div>
            
            <div class="p-8">
              <div class="mb-4 flex items-center justify-between text-[10px] uppercase">
                <span class="font-black tracking-widest text-accent">{{ event.cityName }}</span>
                <div class="flex items-center gap-2 font-bold text-white/40">
                  <Calendar class="h-3 w-3" /> {{ formatDate(event.event_date) }}
                </div>
              </div>
              
              <h3 class="font-display text-xl font-black uppercase italic text-white transition-colors group-hover:text-accent mb-3">
                {{ event.title }}
              </h3>
              <p class="line-clamp-2 text-xs leading-relaxed text-white/60 mb-6">
                {{ event.description }}
              </p>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-[10px] font-black tracking-widest text-white/40">
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

    <!-- ================= MODE SELECTION MODAL ================= -->
    <Modal :show="showModeModal" @close="showModeModal = false">
      <div class="p-8 bg-gaming-darker border border-primary/20 rounded-[2.5rem] overflow-hidden relative max-w-lg mx-auto shadow-2xl">
        <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
        
        <div class="relative z-10 text-center">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-black text-primary uppercase tracking-[0.3em] mb-6">
            <Compass class="h-3 w-3" /> Configuration Tactique
          </div>
          
          <h2 class="font-display text-3xl text-white uppercase italic font-black mb-8 leading-none">
            COMMENT VOULEZ-VOUS <br/> <span class="text-primary">EXPLORER {{ selectedCity?.name }} ?</span>
          </h2>

          <div class="grid gap-4">
            <button 
              @click="startSolo"
              class="group relative overflow-hidden rounded-2xl p-6 bg-white/5 border border-white/10 hover:border-primary transition-all text-left"
            >
              <div class="flex items-center gap-5">
                <div class="h-12 w-12 rounded-xl glass flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-black transition-all">
                  <User class="h-6 w-6" />
                </div>
                <div>
                  <div class="font-display text-lg text-white uppercase italic font-black">SOLO</div>
                  <div class="text-[9px] text-white/40 uppercase font-black tracking-widest mt-1">INFILTRATION INDIVIDUELLE</div>
                </div>
              </div>
            </button>

            <button 
              @click="startTeam"
              class="group relative overflow-hidden rounded-2xl p-6 bg-white/5 border border-white/10 hover:border-accent transition-all text-left"
            >
              <div class="flex items-center gap-5">
                <div class="h-12 w-12 rounded-xl glass flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-black transition-all">
                  <Users class="h-6 w-6" />
                </div>
                <div>
                  <div class="font-display text-lg text-white uppercase italic font-black">EN ÉQUIPE</div>
                  <div class="text-[9px] text-white/40 uppercase font-black tracking-widest mt-1">DÉPLOIEMENT D'ESCOUADE</div>
                </div>
              </div>
            </button>
          </div>

          <button @click="showModeModal = false" class="mt-8 text-[10px] font-black text-white/20 uppercase tracking-widest hover:text-white transition-colors">
            RETOUR À L'ATLAS
          </button>
        </div>
      </div>
    </Modal>

    <!-- ================= EVENT MODAL OVERLAY ================= -->
    <Transition name="modal">
      <div v-if="selectedEvent" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 backdrop-blur-xl bg-gaming-darker/90">
        <div class="absolute inset-0" @click="selectedEvent = null" />
        
        <div class="relative w-full max-w-5xl overflow-hidden rounded-[3rem] border border-white/10 bg-gaming-dark shadow-2xl animate-scale-up">
          <button @click="selectedEvent = null" class="glass absolute top-6 right-6 z-10 flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
            <X class="h-6 w-6" />
          </button>

          <div class="grid h-full max-h-[90vh] lg:grid-cols-2">
            <!-- Left Side: Image Gallery -->
            <div class="relative flex h-[40vh] items-center justify-center bg-black lg:h-full">
              <img 
                :src="selectedEvent.images?.[currentImageIndex] || DEFAULT_EVENT_IMAGE" 
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
                  <div class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2">Date de l'événement</div>
                  <div class="flex items-center gap-3 font-bold text-white">
                    <Calendar class="h-5 w-5 text-accent" /> {{ formatDate(selectedEvent.event_date) }}
                  </div>
                </div>
                <div class="glass rounded-2xl border border-white/5 p-4">
                  <div class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2">Lieu</div>
                  <div class="flex items-center gap-3 font-bold text-white">
                    <MapPin class="h-5 w-5 text-accent" /> {{ selectedEvent.location_name || 'Non spécifié' }}
                  </div>
                </div>
              </div>

              <div class="space-y-6">
                <div>
                  <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-accent mb-4">À propos</h4>
                  <p class="font-medium text-lg leading-relaxed text-white/70">
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
/* Custom keyframe animation handling the horizontal scanning animation via Tailwind */
.animate-radar {
  animation: radar-scan 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes radar-scan {
  0% { transform: translateX(-100%) skewX(-15deg); }
  50% { transform: translateX(100%) skewX(-15deg); }
  100% { transform: translateX(-100%) skewX(-15deg); }
}
</style>