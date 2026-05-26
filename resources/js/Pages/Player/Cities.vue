<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { firstStorageUrl, storageUrl } from '@/lib/storageUrl';
import { Head, Link } from '@inertiajs/vue3';
import {
  Search, MapPin, Star, Filter, ArrowRight,
  Calendar, Image as ImageIcon, ChevronLeft,
  ChevronRight, X, CheckCircle2
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    cities: Array,
    gameMode: String,
});

const searchQuery = ref('');
const selectedEvent = ref(null);
const currentImageIndex = ref(0);

// Reset de l'index d'image à chaque changement d'événement (évite les index out-of-bounds)
watch(selectedEvent, () => {
    currentImageIndex.value = 0;
});

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

// Centralisation de la liste des images de l'événement sélectionné (gère images et image_urls)
const selectedEventImages = computed(() => {
  if (!selectedEvent.value) return [];
  return selectedEvent.value.images || selectedEvent.value.image_urls || [];
});

const totalImages = computed(() => selectedEventImages.value.length || 1);

// Résout dynamiquement l'image à afficher dans la modal en fonction de l'index courant
const activeImageUrl = computed(() => {
  if (!selectedEvent.value) return DEFAULT_EVENT_IMAGE;
  if (selectedEventImages.value.length === 0) return DEFAULT_EVENT_IMAGE;

  const currentImage = selectedEventImages.value[currentImageIndex.value];
  return storageUrl(currentImage) || DEFAULT_EVENT_IMAGE;
});

// Modal Actions
const openEventModal = (event) => {
  selectedEvent.value = event;
};

const handleImageNavigation = (direction) => {
  const length = selectedEventImages.value.length;
  if (!length) return;

  if (direction === 'next') {
    currentImageIndex.value = (currentImageIndex.value + 1) % length;
  } else {
    currentImageIndex.value = (currentImageIndex.value - 1 + length) % length;
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

const cityImage = (city) => {
  if (city?.image_url && city.image_url.startsWith('http')) return city.image_url;
  if (city?.image_path && city.image_path.startsWith('http')) return city.image_path;

  const processed = storageUrl(city?.image_url || city?.image_path);
  return processed || DEFAULT_CITY_IMAGE;
};

const eventImage = (event) => firstStorageUrl(event?.image_urls) || firstStorageUrl(event?.images) || DEFAULT_EVENT_IMAGE;

// Markers for the mini map
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

      <div class="mt-6 relative h-44 md:h-56 rounded-2xl glass-strong overflow-hidden border border-electric/10">
        <!-- BENIN FLAG BACKGROUND (Animated) Theodore -->
        <div class="absolute inset-0 flex opacity-[0.08] pointer-events-none animate-flag-wave">
            <div class="w-[40%] h-full bg-[rgba(0,190,100,1)]"></div> <!-- Vert -->
            <div class="w-[60%] h-full flex flex-col">
                <div class="h-1/2 bg-[rgba(255,228,45,1)]"></div> <!-- Jaune -->
                <div class="h-1/2 bg-[rgba(232,28,48,1)]"></div> <!-- Rouge -->
            </div>
        </div>

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
          class="group relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] hover-lift block aspect-[4/5] animate-fade-up transition-all duration-500 hover:border-primary/40"
          :class="{ 'grayscale-[0.8] opacity-80': c.has_completed_adventure }"
          :style="{ animationDelay: `${i * 60}ms` }"
        >
          <AppImage
            :src="cityImage(c)"
            :alt="c.name"
            :fallback="DEFAULT_CITY_IMAGE"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
          >
            <template #placeholder>
              <div class="absolute inset-0 bg-gaming-darker grid place-items-center">
                <ImageIcon class="h-12 w-12 text-white/10" />
              </div>
            </template>
          </AppImage>
          <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/40 to-transparent" />

          <div class="absolute top-4 left-4 right-4 flex justify-between">
            <span class="px-3 py-1 rounded-full glass text-[10px] text-electric font-bold uppercase tracking-widest">Bénin</span>
            <div v-if="c.has_completed_adventure" class="px-2 py-1 rounded-full bg-success/20 border border-success/40 text-success text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
              <CheckCircle2 class="h-3 w-3" /> Explorée
            </div>
            <span v-else class="px-2 py-1 rounded-full glass text-xs flex items-center gap-1">
              <Star class="h-3 w-3 fill-electric text-electric" />4.9
            </span>
          </div>

          <div class="absolute bottom-5 left-5 right-5">
            <h3 class="font-display text-2xl text-white">{{ c.name }}</h3>

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
                <span class="flex items-center gap-1"><MapPin class="h-3 w-3" />{{ c.total_count || 0 }} missions</span>
              </div>
              <div class="h-8 w-8 rounded-lg glass border-white/10 grid place-items-center text-white group-hover:bg-electric transition-all">
                <ArrowRight class="h-4 w-4" />
              </div>
            </div>
          </div>
        </Link>
      </div>

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
            class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl group relative overflow-hidden rounded-[2.5rem] border border-white/20 cursor-pointer transition-all duration-500 hover:border-accent/40 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]"
          >
            <div class="relative aspect-video overflow-hidden bg-black">
              <img
                :src="eventImage(event)"
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
                  <span>{{ event.images?.length || event.image_urls?.length || 0 }} PHOTOS</span>
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

    <Transition name="modal">
      <div v-if="selectedEvent" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 backdrop-blur-xl bg-gaming-darker/90">
        <div class="absolute inset-0" @click="selectedEvent = null" />

        <div class="relative w-full max-w-5xl overflow-hidden rounded-[3rem] border border-white/10 bg-gaming-dark shadow-2xl animate-scale-up">
          <button @click="selectedEvent = null" class="glass absolute top-6 right-6 z-10 flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
            <X class="h-6 w-6" />
          </button>

          <div class="grid h-full max-h-[90vh] lg:grid-cols-2">
            <div class="relative flex h-[40vh] items-center justify-center bg-black lg:h-full">
              <img
                :src="activeImageUrl"
                :alt="selectedEvent.title"
                class="h-full w-full object-cover transition-all duration-500"
              />

              <div v-if="totalImages > 1" class="absolute inset-x-0 bottom-8 flex justify-center gap-4">
                <button @click="handleImageNavigation('prev')" class="glass-strong flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
                  <ChevronLeft class="h-6 w-6" />
                </button>
                <button @click="handleImageNavigation('next')" class="glass-strong flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:bg-accent hover:text-black">
                  <ChevronRight class="h-6 w-6" />
                </button>
              </div>

              <div class="glass absolute top-8 left-8 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white">
                {{ currentImageIndex + 1 }} / {{ totalImages }}
              </div>
            </div>

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
